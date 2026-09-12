<?php

namespace App\Models\Concerns;

use App\Models\Hotel;
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
    public static function bootBelongsToHotel(): void
    {
        static::addGlobalScope('hotel', function (Builder $builder) {
            /** @var CurrentHotel $currentHotel */
            $currentHotel = app(CurrentHotel::class);

            if ($currentHotel->has()) {
                $builder->where($builder->getModel()->getTable() . '.hotel_id', $currentHotel->id());
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
}
