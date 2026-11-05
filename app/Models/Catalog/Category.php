<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'is_active',
        // No longer on the Add Category form (there is nothing to limit: a
        // category holds unlimited products). The column is kept because the
        // seeder and the product import still write it.
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function getStorefrontNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === '' || $locale === 'en') {
            return $this->name;
        }

        $translation = $this->translationForLocale($locale);

        if (filled($translation?->name)) {
            return $translation->name;
        }

        return $this->storefrontTranslatedNameFallback($locale);
    }

    private function translationForLocale(string $locale): ?CategoryTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()->where('locale', $locale)->first();
    }

    private function storefrontTranslatedNameFallback(string $locale): string
    {
        $translated = function_exists('storefront_public_auto_translate')
            ? storefront_public_auto_translate($this->name, $locale)
            : $this->name;

        if ($translated === '' || $translated === $this->name) {
            return $this->name;
        }

        try {
            $translation = CategoryTranslation::query()->updateOrCreate(
                ['category_id' => $this->getKey(), 'locale' => $locale],
                ['name' => $translated]
            );

            if ($this->relationLoaded('translations')) {
                $this->setRelation(
                    'translations',
                    $this->translations
                        ->reject(fn (CategoryTranslation $item): bool => $item->locale === $locale)
                        ->push($translation)
                );
            }
        } catch (\Throwable) {
            return $translated;
        }

        return $translated;
    }

    public function getStorefrontImageUrlAttribute(): ?string
    {
        if (filled($this->image_path)) {
            return Str::startsWith($this->image_path, ['http://', 'https://'])
                ? $this->image_path
                : asset($this->image_path);
        }

        return null;
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (blank($category->slug) && filled($category->name)) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $count = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$count;
            $count++;
        }

        return $slug;
    }
}
