<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\HotelBranch;
use App\Models\HotelSettings;
use App\Models\Page;
use App\Models\Restaurant;
use App\Models\Room;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Hotel::query()
            ->withCount('users')
            ->with(['primaryAdmin', 'settings']);

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->value()) {
            if (in_array($status, ['active', 'suspended', 'draft'], true)) {
                $query->where('status', $status);
            }
        }

        $customers = $query->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Hotel $hotel) => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'slug' => $hotel->slug,
                'domain' => $hotel->domain,
                'status' => $hotel->status,
                'users_count' => $hotel->users_count,
                'created_at' => $hotel->created_at?->format('Y-m-d H:i') ?? $hotel->created_at,
                'primary_admin' => $hotel->primaryAdmin ? [
                    'id' => $hotel->primaryAdmin->id,
                    'name' => $hotel->primaryAdmin->name,
                    'email' => $hotel->primaryAdmin->email,
                ] : null,
            ]);

        $stats = [
            'total' => Hotel::count(),
            'active' => Hotel::where('status', 'active')->count(),
            'suspended' => Hotel::where('status', 'suspended')->count(),
            'draft' => Hotel::where('status', 'draft')->count(),
        ];

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'q' => $request->string('q')->value(),
                'status' => $request->string('status')->value(),
            ],
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Customers/Create', [
            'timezones' => [
                'UTC', 'Africa/Cairo', 'Asia/Dubai', 'Asia/Riyadh',
                'Europe/London', 'Europe/Paris', 'America/New_York', 'America/Los_Angeles',
            ],
            'currencies' => ['USD', 'EUR', 'GBP', 'EGP', 'SAR', 'AED'],
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $hotel = DB::transaction(function () use ($request) {
            $hotelData = [
                'name' => $request->validated('name'),
                'slug' => strtolower($request->validated('slug')),
                'domain' => $request->validated('domain') ? strtolower(trim($request->validated('domain'))) : null,
                'status' => $request->validated('status'),
                'contact_email' => $request->validated('contact_email'),
                'contact_phone' => $request->validated('contact_phone'),
                'address' => $request->validated('address'),
                'timezone' => $request->validated('timezone') ?: 'UTC',
                'currency' => $request->validated('currency') ?: 'USD',
            ];

            $hotel = Hotel::create($hotelData);

            HotelSettings::create([
                'hotel_id' => $hotel->id,
                'checkin_time' => $request->validated('checkin_time') ?: '14:00:00',
                'checkout_time' => $request->validated('checkout_time') ?: '12:00:00',
                'default_locale' => $request->validated('default_locale') ?: 'en',
                'guest_view' => 'classic',
            ]);

            Theme::create([
                'hotel_id' => $hotel->id,
                'name' => "{$hotel->name} Default Theme",
                'is_active' => true,
                'primary_color' => '#059669',
                'secondary_color' => '#10B981',
                'font_family' => 'Instrument Sans',
                'border_radius' => 'medium',
                'button_style' => 'rounded',
                'card_style' => 'elevated',
                'config' => [
                    'header_bg' => '#064e3b',
                    'footer_bg' => '#022c22',
                ],
            ]);

            User::create([
                'hotel_id' => $hotel->id,
                'name' => $request->validated('admin_name'),
                'email' => $request->validated('admin_email'),
                'password' => Hash::make($request->validated('admin_password')),
                'role' => 'hotel_admin',
                'status' => 'active',
            ]);

            return $hotel;
        });

        return redirect()->route('admin.customers.show', $hotel)
            ->with('success', __('admin.messages.customer_created', [], 'Customer hotel created successfully.'));
    }

    public function show(Hotel $hotel): Response
    {
        $hotel->load(['settings', 'themes', 'primaryAdmin', 'branches', 'users']);

        $contentCounts = [
            'facilities' => Facility::withoutGlobalScope('hotel')->where('hotel_id', $hotel->id)->count(),
            'rooms' => Room::withoutGlobalScope('hotel')->where('hotel_id', $hotel->id)->count(),
            'restaurants' => Restaurant::withoutGlobalScope('hotel')->where('hotel_id', $hotel->id)->count(),
            'pages' => Page::withoutGlobalScope('hotel')->where('hotel_id', $hotel->id)->count(),
        ];

        return Inertia::render('Admin/Customers/Show', [
            'customer' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'slug' => $hotel->slug,
                'domain' => $hotel->domain,
                'status' => $hotel->status,
                'contact_email' => $hotel->contact_email,
                'contact_phone' => $hotel->contact_phone,
                'address' => $hotel->address,
                'timezone' => $hotel->timezone,
                'currency' => $hotel->currency,
                'created_at' => $hotel->created_at?->format('Y-m-d H:i'),
                'updated_at' => $hotel->updated_at?->format('Y-m-d H:i'),
                'settings' => $hotel->settings ? [
                    'checkin_time' => $hotel->settings->checkin_time,
                    'checkout_time' => $hotel->settings->checkout_time,
                    'default_locale' => $hotel->settings->default_locale,
                    'guest_view' => $hotel->settings->guest_view,
                ] : null,
                'primary_admin' => $hotel->primaryAdmin ? [
                    'id' => $hotel->primaryAdmin->id,
                    'name' => $hotel->primaryAdmin->name,
                    'email' => $hotel->primaryAdmin->email,
                    'status' => $hotel->primaryAdmin->status,
                    'created_at' => $hotel->primaryAdmin->created_at?->format('Y-m-d'),
                ] : null,
                'users' => $hotel->users->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'status' => $u->status,
                    'created_at' => $u->created_at?->format('Y-m-d'),
                ]),
                'branches' => $hotel->branches->map(fn (HotelBranch $b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'domain' => $b->domain,
                    'city' => $b->city,
                    'phone' => $b->phone,
                    'status' => $b->status,
                    'is_main' => (bool) $b->is_main,
                ]),
                'content_counts' => $contentCounts,
            ],
        ]);
    }

    public function edit(Hotel $hotel): Response
    {
        $hotel->load('settings');

        return Inertia::render('Admin/Customers/Edit', [
            'customer' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'slug' => $hotel->slug,
                'domain' => $hotel->domain,
                'status' => $hotel->status,
                'contact_email' => $hotel->contact_email,
                'contact_phone' => $hotel->contact_phone,
                'address' => $hotel->address,
                'timezone' => $hotel->timezone,
                'currency' => $hotel->currency,
                'settings' => $hotel->settings ? [
                    'checkin_time' => $hotel->settings->checkin_time,
                    'checkout_time' => $hotel->settings->checkout_time,
                    'default_locale' => $hotel->settings->default_locale,
                ] : null,
            ],
            'timezones' => [
                'UTC', 'Africa/Cairo', 'Asia/Dubai', 'Asia/Riyadh',
                'Europe/London', 'Europe/Paris', 'America/New_York', 'America/Los_Angeles',
            ],
            'currencies' => ['USD', 'EUR', 'GBP', 'EGP', 'SAR', 'AED'],
        ]);
    }

    public function update(UpdateCustomerRequest $request, Hotel $hotel): RedirectResponse
    {
        DB::transaction(function () use ($request, $hotel) {
            $hotel->update([
                'name' => $request->validated('name'),
                'slug' => strtolower($request->validated('slug')),
                'domain' => $request->validated('domain') ? strtolower(trim($request->validated('domain'))) : null,
                'status' => $request->validated('status'),
                'contact_email' => $request->validated('contact_email'),
                'contact_phone' => $request->validated('contact_phone'),
                'address' => $request->validated('address'),
                'timezone' => $request->validated('timezone') ?: $hotel->timezone,
                'currency' => $request->validated('currency') ?: $hotel->currency,
            ]);

            if ($hotel->settings) {
                $hotel->settings->update([
                    'checkin_time' => $request->validated('checkin_time') ?: $hotel->settings->checkin_time,
                    'checkout_time' => $request->validated('checkout_time') ?: $hotel->settings->checkout_time,
                    'default_locale' => $request->validated('default_locale') ?: $hotel->settings->default_locale,
                ]);
            }
        });

        return redirect()->route('admin.customers.show', $hotel)
            ->with('success', __('admin.messages.customer_updated', [], 'Customer hotel updated successfully.'));
    }

    public function updateStatus(Request $request, Hotel $hotel): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended', 'draft'])],
        ]);

        $hotel->update(['status' => $data['status']]);

        $message = $data['status'] === 'suspended'
            ? 'Hotel has been suspended. Tenant access is now restricted.'
            : 'Hotel status updated successfully.';

        return back()->with('success', $message);
    }

    public function updateDomain(Request $request, Hotel $hotel): RedirectResponse
    {
        $data = $request->validate([
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('hotels', 'domain')->ignore($hotel->id),
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            ],
        ]);

        $hotel->update([
            'domain' => ! empty($data['domain']) ? strtolower(trim($data['domain'])) : null,
        ]);

        return back()->with('success', 'Domain configuration updated successfully.');
    }

    public function storeUser(Request $request, Hotel $hotel): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['hotel_admin', 'hotel_staff'])],
        ]);

        User::create([
            'hotel_id' => $hotel->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => 'active',
        ]);

        return back()->with('success', 'Administrator added successfully.');
    }
}
