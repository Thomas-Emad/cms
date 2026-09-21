<?php

namespace App\Services\Map;

use App\Models\Facility;
use App\Models\Restaurant;
use App\Models\Room;

/**
 * Fills map locations that carry a `ref` (facility / restaurant / room slug) with the real
 * hotel content: photo, description and a "View Details" link. Only PUBLISHED content of the
 * CURRENT hotel is used (the models are tenant-scoped). Values written on the location itself
 * always win, so a map can override any of them.
 */
class MapContentLinker
{
    private const URL = ['facility' => '/facilities/', 'restaurant' => '/restaurants/', 'room' => '/rooms/'];

    public function link(array $data): array
    {
        $slugs = ['facility' => [], 'restaurant' => [], 'room' => []];
        foreach ($data['locations'] ?? [] as $l) {
            $type = $l['ref']['type'] ?? null;
            $slug = $l['ref']['slug'] ?? null;
            if (isset($slugs[$type]) && is_string($slug)) {
                $slugs[$type][] = $slug;
            }
        }
        if (! array_filter($slugs)) {
            return $data;
        }

        $found = [
            'facility' => $slugs['facility'] ? Facility::published()->whereIn('slug', array_unique($slugs['facility']))->with('cover')->get()->keyBy('slug') : collect(),
            'restaurant' => $slugs['restaurant'] ? Restaurant::published()->whereIn('slug', array_unique($slugs['restaurant']))->with('cover')->get()->keyBy('slug') : collect(),
            'room' => $slugs['room'] ? Room::published()->whereIn('slug', array_unique($slugs['room']))->with('cover')->get()->keyBy('slug') : collect(),
        ];

        foreach ($data['locations'] as &$l) {
            $type = $l['ref']['type'] ?? null;
            $slug = $l['ref']['slug'] ?? null;
            $model = isset($found[$type]) ? $found[$type]->get($slug) : null;
            if (! $model) {
                continue; // unpublished or missing: the location still works, just without a link
            }
            $l['image'] = ! empty($l['image']) ? $l['image'] : $model->cover_image_url;
            $l['description'] = ! empty($l['description']) ? $l['description'] : ($model->getAttribute('short_description') ?: $model->getAttribute('description'));
            $l['details_url'] = self::URL[$type] . $model->slug;
        }
        unset($l);

        return $data;
    }
}
