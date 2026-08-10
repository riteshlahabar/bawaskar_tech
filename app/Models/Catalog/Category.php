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
        'homepage_title',
        'homepage_layout',
        'homepage_product_limit',
        'homepage_sort_order',
        'show_on_homepage',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'homepage_product_limit' => 'integer',
            'homepage_sort_order' => 'integer',
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

        $translations = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->where('locale', $locale)->get();

        $translation = $translations->firstWhere('locale', $locale);

        return filled($translation?->name) ? $translation->name : $this->name;
    }
    public function getStorefrontImageUrlAttribute(): string
    {
        if (filled($this->image_path)) {
            return Str::startsWith($this->image_path, ['http://', 'https://'])
                ? $this->image_path
                : asset($this->image_path);
        }

        $fallbacks = [
            'fastkart-store/images/grocery/category/1.png',
            'fastkart-store/images/grocery/category/2.png',
            'fastkart-store/images/grocery/category/3.png',
            'fastkart-store/images/grocery/category/4.png',
            'fastkart-store/images/grocery/category/5.png',
            'fastkart-store/images/grocery/category/6.png',
            'fastkart-store/images/grocery/category/7.png',
            'fastkart-store/images/grocery/category/8.png',
        ];

        $fallbackIndex = max(0, ((int) ($this->id ?: 1)) - 1) % count($fallbacks);

        return asset($fallbacks[$fallbackIndex]);
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

