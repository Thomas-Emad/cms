<?php

namespace App\Models\Concerns;

use App\Models\Hotel;
use App\Models\HotelBranch;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to every model that belongs to a single hotel (Facility, Restaurant,
 * Service, Event, Offer, Experience, Page, Theme, ...).
 *
 * - Auto-scopes all queries to the current tenant (global scope).
 * - Auto-fills hotel_id on creation.
 * - Superadmin/cross-tenant queries can opt out via ::withoutGlobalScope('hotel').
 */
trait BelongsToHotel
{
    use FormatsDates;

    public static function bootBelongsToHotel(): void
    {
        static::addGlobalScope('hotel', function (Builder $builder) {
            /** @var CurrentHotel $currentHotel */
            $currentHotel = app(CurrentHotel::class);

            if ($currentHotel->has()) {
                $builder->where($builder->getModel()->getTable().'.hotel_id', $currentHotel->id());
            }
        });

        static::creating(function ($model) {
            if (empty($model->hotel_id) && app(CurrentHotel::class)->has()) {
                $model->hotel_id = app(CurrentHotel::class)->id();
            }
        });
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(HotelBranch::class, 'hotel_branch_id');
    }

    /**
     * Scope query to items available for a specific branch (items with hotel_branch_id = $branchId OR hotel_branch_id is null).
     */
    public function scopeForBranch(Builder $query, ?int $branchId): Builder
    {
        if (! $branchId) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($branchId) {
            $table = $this->getTable();
            $q->whereNull("{$table}.hotel_branch_id")
                ->orWhere("{$table}.hotel_branch_id", $branchId);
        });
    }

    /**
     * Scope query to items belonging strictly to a specific branch.
     */
    public function scopeStrictlyBranch(Builder $query, int $branchId): Builder
    {
        return $query->where($this->getTable().'.hotel_branch_id', $branchId);
    }

    /**
     * Scope query to hotel-wide items only.
     */
    public function scopeHotelWide(Builder $query): Builder
    {
        return $query->whereNull($this->getTable().'.hotel_branch_id');
    }
}
