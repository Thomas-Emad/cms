<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ThemeController extends Controller
{
    public function edit(Request $request, CurrentHotel $currentHotel): Response
    {
        $hotel = $currentHotel->get();
        $this->authorize('view', $hotel);

        $branches = $hotel->branches()->ordered()->get(['id', 'name', 'slug', 'city']);
        $branchId = $request->integer('branch_id') ?: null;

        $selectedBranch = null;
        if ($branchId) {
            $selectedBranch = $branches->firstWhere('id', $branchId);
            if (! $selectedBranch) {
                $branchId = null;
            }
        }

        // Master theme for the hotel
        $masterTheme = $hotel->themes()
            ->whereNull('hotel_branch_id')
            ->where('is_active', true)
            ->first()
            ?? $hotel->activeTheme
            ?? $hotel->themes()->firstOrCreate(
                ['is_active' => true, 'hotel_branch_id' => null],
                [
                    'name' => 'Guest Layout Theme',
                    'primary_color' => '#059669',
                    'secondary_color' => '#B99A62',
                    'font_family' => 'Instrument Sans',
                    'border_radius' => 'medium',
                    'button_style' => 'rounded',
                    'card_style' => 'elevated',
                ]
            );

        $hasCustomBranchTheme = false;
        if ($selectedBranch) {
            $branchTheme = $hotel->themes()
                ->where('hotel_branch_id', $selectedBranch->id)
                ->first();

            if ($branchTheme) {
                $theme = $branchTheme;
                $hasCustomBranchTheme = true;
            } else {
                // Default to master theme styling for a branch that doesn't yet have custom styling
                $theme = new Theme([
                    'hotel_id' => $hotel->id,
                    'hotel_branch_id' => $selectedBranch->id,
                    'name' => $selectedBranch->name.' Theme',
                    'primary_color' => $masterTheme->primary_color,
                    'secondary_color' => $masterTheme->secondary_color,
                    'font_family' => $masterTheme->font_family,
                    'border_radius' => $masterTheme->border_radius,
                    'button_style' => $masterTheme->button_style,
                    'card_style' => $masterTheme->card_style,
                    'config' => $masterTheme->config ?? [],
                    'is_active' => true,
                ]);
            }
        } else {
            $theme = $masterTheme;
        }

        return Inertia::render('Admin/Theme/Edit', [
            'theme' => array_merge($theme->toArray(), [
                'header_bg' => $theme->header_bg,
                'footer_bg' => $theme->footer_bg,
            ]),
            'branches' => $branches,
            'selected_branch_id' => $selectedBranch?->id,
            'selected_branch' => $selectedBranch,
            'has_custom_branch_theme' => $hasCustomBranchTheme,
            'presets' => [
                [
                    'id' => 'emerald',
                    'name' => 'Emerald Green (Hospitality Oasis)',
                    'name_ar' => 'أخضر زمردي فاخر',
                    'description' => 'Vibrant, fresh hospitality green with deep emerald header & dock.',
                    'primary_color' => '#059669',
                    'secondary_color' => '#10B981',
                    'header_bg' => '#064e3b',
                    'footer_bg' => '#022c22',
                ],
                [
                    'id' => 'forest',
                    'name' => 'Forest Luxury (Deep Pine & Gold)',
                    'name_ar' => 'أخضر غابي ملكي وذهبي',
                    'description' => 'Grand Horizon signature dark pine header with champagne gold accents.',
                    'primary_color' => '#183C2D',
                    'secondary_color' => '#B99A62',
                    'header_bg' => '#122e22',
                    'footer_bg' => '#0a1d15',
                ],
                [
                    'id' => 'sage',
                    'name' => 'Sage & Olive (Modern Organic Green)',
                    'name_ar' => 'أخضر ميرمية وزيتوني هادئ',
                    'description' => 'Subtle earthy green tones with warm olive header and dock.',
                    'primary_color' => '#365347',
                    'secondary_color' => '#84A98C',
                    'header_bg' => '#22352b',
                    'footer_bg' => '#15221b',
                ],
                [
                    'id' => 'hilton-blue',
                    'name' => 'Hilton Blue & Champagne',
                    'name_ar' => 'أزرق كلاسيكي وشمبانيا',
                    'description' => 'Corporate navy blue header with warm champagne accents.',
                    'primary_color' => '#002F61',
                    'secondary_color' => '#B99A62',
                    'header_bg' => '#001e3d',
                    'footer_bg' => '#001326',
                ],
                [
                    'id' => 'ocean-teal',
                    'name' => 'Ocean Coral & Teal',
                    'name_ar' => 'أزرق فيروزي ومحيطي',
                    'description' => 'Tropical coastal turquoise header for beachfront properties.',
                    'primary_color' => '#0F766E',
                    'secondary_color' => '#2DD4BF',
                    'header_bg' => '#115e59',
                    'footer_bg' => '#042f2e',
                ],
                [
                    'id' => 'royal-wine',
                    'name' => 'Royal Burgundy & Rose',
                    'name_ar' => 'عنابي ملكي ووردي',
                    'description' => 'Opulent deep wine header with subtle blush rose tones.',
                    'primary_color' => '#831843',
                    'secondary_color' => '#F472B6',
                    'header_bg' => '#500724',
                    'footer_bg' => '#370417',
                ],
                [
                    'id' => 'warm-amber',
                    'name' => 'Desert Gold & Amber',
                    'name_ar' => 'ذهبي صحراوي وكهرمان دافئ',
                    'description' => 'Warm sunset amber header for desert retreats and boutique riads.',
                    'primary_color' => '#D97706',
                    'secondary_color' => '#F59E0B',
                    'header_bg' => '#451a03',
                    'footer_bg' => '#270e01',
                ],
                [
                    'id' => 'classic-dark',
                    'name' => 'Classic Charcoal & Gold',
                    'name_ar' => 'فحمي كلاسيكي وذهبي',
                    'description' => 'Timeless deep charcoal header and dock with champagne gold.',
                    'primary_color' => '#B99A62',
                    'secondary_color' => '#E5C483',
                    'header_bg' => '#0a0c10',
                    'footer_bg' => '#0b0e13',
                ],
            ],
            'flash_ok' => session('success'),
        ]);
    }

    public function update(Request $request, CurrentHotel $currentHotel): RedirectResponse
    {
        $hotel = $currentHotel->get();
        $this->authorize('update', $hotel);

        $validated = $request->validate([
            'hotel_branch_id' => [
                'nullable',
                'integer',
                Rule::exists('hotel_branches', 'id')->where('hotel_id', $hotel->id),
            ],
            'name' => ['nullable', 'string', 'max:100'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'header_bg' => ['nullable', 'string', 'max:50'],
            'footer_bg' => ['nullable', 'string', 'max:50'],
            'font_family' => ['nullable', 'string', Rule::in(['Instrument Sans', 'Cairo', 'Tajawal', 'Fraunces', 'Inter'])],
            'border_radius' => ['nullable', 'string', Rule::in(['none', 'small', 'medium', 'large', 'full'])],
            'button_style' => ['nullable', 'string', Rule::in(['square', 'rounded', 'pill'])],
            'card_style' => ['nullable', 'string', Rule::in(['flat', 'outlined', 'elevated'])],
        ]);

        $branchId = $validated['hotel_branch_id'] ?? null;

        $theme = $branchId
            ? $hotel->themes()->where('hotel_branch_id', $branchId)->first()
            : ($hotel->themes()->whereNull('hotel_branch_id')->first() ?? $hotel->activeTheme);

        $config = $theme?->config ?? [];
        if (! empty($validated['header_bg'])) {
            $config['header_bg'] = $validated['header_bg'];
        }
        if (! empty($validated['footer_bg'])) {
            $config['footer_bg'] = $validated['footer_bg'];
        }

        $saveData = array_merge(
            collect($validated)->except(['header_bg', 'footer_bg', 'hotel_branch_id'])->all(),
            [
                'config' => $config,
                'is_active' => true,
                'hotel_branch_id' => $branchId,
            ]
        );

        if ($theme) {
            $theme->update($saveData);
        } else {
            $hotel->themes()->create(array_merge($saveData, [
                'name' => $validated['name'] ?? ($branchId ? 'Branch Custom Theme' : 'Guest Layout Theme'),
            ]));
        }

        $redirectParams = $branchId ? ['branch_id' => $branchId] : [];

        return redirect()->route('admin.theme.edit', $redirectParams)
            ->with('success', __('Theme updated successfully! Changes are live on the guest site.'));
    }

    public function resetBranchTheme(Request $request, CurrentHotel $currentHotel): RedirectResponse
    {
        $hotel = $currentHotel->get();
        $this->authorize('update', $hotel);

        $validated = $request->validate([
            'hotel_branch_id' => [
                'required',
                'integer',
                Rule::exists('hotel_branches', 'id')->where('hotel_id', $hotel->id),
            ],
        ]);

        $hotel->themes()
            ->where('hotel_branch_id', $validated['hotel_branch_id'])
            ->delete();

        return redirect()->route('admin.theme.edit', ['branch_id' => $validated['hotel_branch_id']])
            ->with('success', __('Branch theme reset to hotel master theme.'));
    }
}
