<?php

namespace App\Models\Concerns;

use App\Models\EntityTranslation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    /** @var array<string, array<string, mixed>> */
    protected array $translationDataCache = [];

    public function initializeHasTranslations(): void
    {
        $this->append('translations_data');
    }

    public static function bootHasTranslations(): void
    {
        static::saved(function ($model) {
            if (request()->has('translations') && is_array(request()->input('translations'))) {
                foreach (request()->input('translations') as $locale => $values) {
                    if (is_array($values)) {
                        $model->setTranslations($locale, $values);
                    }
                }
            }
        });
    }

    public function getTranslationsDataAttribute(): array
    {
        return $this->getTranslationsGrouped();
    }

    public function saveTranslations(array $translations): static
    {
        foreach ($translations as $locale => $values) {
            if (is_array($values)) {
                $this->setTranslations($locale, $values);
            }
        }

        return $this;
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(EntityTranslation::class, 'translatable');
    }

    /** @return string[] */
    public function getTranslatableAttributes(): array
    {
        return property_exists($this, 'translatable') && is_array($this->translatable)
            ? $this->translatable
            : [];
    }

    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes(), true);
    }

    /** @return array<string, mixed> */
    public function getTranslationData(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();

        if (isset($this->translationDataCache[$locale])) {
            return $this->translationDataCache[$locale];
        }

        if ($this->relationLoaded('translations')) {
            $record = $this->translations->firstWhere('locale', $locale);

            return $this->translationDataCache[$locale] = ($record?->data ?? []);
        }

        if (! $this->exists) {
            return $this->translationDataCache[$locale] = [];
        }

        $record = $this->translations()->where('locale', $locale)->first();

        return $this->translationDataCache[$locale] = ($record?->data ?? []);
    }

    public function getTranslatedAttribute(string $key, ?string $locale = null): mixed
    {
        $locale = $locale ?: app()->getLocale();

        if ($locale === 'en' || $locale === config('app.fallback_locale', 'en')) {
            $data = $this->getTranslationData($locale);
            if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null) {
                return $data[$key];
            }

            return parent::getAttribute($key);
        }

        $data = $this->getTranslationData($locale);
        if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null) {
            return $data[$key];
        }

        return parent::getAttribute($key);
    }

    public function getAttribute($key)
    {
        if ($this->isTranslatableAttribute($key)) {
            return $this->getTranslatedAttribute($key);
        }

        return parent::getAttribute($key);
    }

    public function setTranslation(string $locale, string $field, mixed $value): static
    {
        $data = $this->getTranslationData($locale);
        $data[$field] = $value;

        if ($this->exists) {
            EntityTranslation::updateOrCreate(
                [
                    'translatable_type' => $this->getMorphClass(),
                    'translatable_id' => $this->getKey(),
                    'locale' => $locale,
                ],
                ['data' => $data]
            );
        }

        $this->translationDataCache[$locale] = $data;

        return $this;
    }

    /** @param array<string, mixed> $values */
    public function setTranslations(string $locale, array $values): static
    {
        $data = array_merge($this->getTranslationData($locale), $values);

        if ($this->exists) {
            EntityTranslation::updateOrCreate(
                [
                    'translatable_type' => $this->getMorphClass(),
                    'translatable_id' => $this->getKey(),
                    'locale' => $locale,
                ],
                ['data' => $data]
            );
        }

        $this->translationDataCache[$locale] = $data;

        return $this;
    }

    /** @return array<string, array<string, mixed>> */
    public function getTranslationsGrouped(): array
    {
        $result = [];
        if (! $this->exists) {
            return $result;
        }

        $records = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        foreach ($records as $record) {
            $result[$record->locale] = $record->data;
        }

        return $result;
    }
}
