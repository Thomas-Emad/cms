<?php

namespace App\Services\Map;

use App\Models\Facility;
use App\Models\Page;
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
    private const URL = ['facility' => '/facilities/', 'restaurant' => '/restaurants/', 'room' => '/rooms/', 'page' => '/pages/'];

    public function link(array $data): array
    {
        $slugs = ['facility' => [], 'restaurant' => [], 'room' => [], 'page' => []];
        foreach ($data['locations'] ?? [] as $l) {
            $type = $l['ref']['type'] ?? null;
            $slug = $l['ref']['slug'] ?? null;
            if (isset($slugs[$type]) && is_string($slug)) {
                $slugs[$type][] = $slug;
            }
        }
        $hasLink = false;
        foreach ($data['locations'] ?? [] as $l) {
            $hasLink = $hasLink || ! empty($l['link']);
        }
        if (! array_filter($slugs) && ! $hasLink) {
            return $data;
        }

        $found = [
            'facility' => $slugs['facility'] ? Facility::published()->whereIn('slug', array_unique($slugs['facility']))->with('cover')->get()->keyBy('slug') : collect(),
            'restaurant' => $slugs['restaurant'] ? Restaurant::published()->whereIn('slug', array_unique($slugs['restaurant']))->with('cover')->get()->keyBy('slug') : collect(),
            'room' => $slugs['room'] ? Room::published()->whereIn('slug', array_unique($slugs['room']))->with('cover')->get()->keyBy('slug') : collect(),
            // Page Builder pages: guests can only ever reach the PUBLISHED version.
            'page' => $slugs['page'] ? Page::published()->whereIn('slug', array_unique($slugs['page']))->get()->keyBy('slug') : collect(),
        ];

        foreach ($data['locations'] as &$l) {
            $type = $l['ref']['type'] ?? null;
            $slug = $l['ref']['slug'] ?? null;
            $model = isset($found[$type]) ? $found[$type]->get($slug) : null;
            if ($model) { // unpublished or missing content is simply not linked
                $l['image'] = ! empty($l['image']) ? $l['image'] : ($type === 'page' ? null : $model->cover_image_url);
                $l['description'] = ! empty($l['description']) ? $l['description'] : ($type === 'page' ? null : ($model->getAttribute('short_description') ?: $model->getAttribute('description')));
                $l['details_url'] = self::URL[$type].$model->slug;
            }
            // An address typed on the place itself always wins.
            if (! empty($l['link'])) {
                $l['details_url'] = $l['link'];
            }
        }
        unset($l);

        return $data;
    }
}
