<?php

namespace App\Support\Admin\Forms;

/**
 * SRP: turns the flat `fields` array of a module config into render
 * instructions for the shared admin form.
 *
 * Two things live here so that neither the Blade template nor the module
 * config has to know about the other:
 *
 * - `tree()` nests any field carrying `render_inside` under the field it names,
 *   which is how the product "Bottom Details" block gets its subsections.
 * - `customView()` maps a custom field type to the partial that draws it, so a
 *   new widget is added by registering a view instead of by editing the shared
 *   form (open for extension, closed for modification).
 */
final class AdminFormFields
{
    /**
     * Custom field types and the partial that renders each one.
     *
     * `wrap` is false when the partial already emits its own grid column.
     *
     * @var array<string, array{view: string, wrap?: bool}>
     */
    private const CUSTOM_VIEWS = [
        'product_translation_tools' => [
            'view' => 'admin.products.partials.translation-tools',
            'wrap' => false,
        ],
        'product_variants_repeater' => [
            'view' => 'admin.products.partials.variants.repeater',
        ],
        'product_media_repeater' => [
            'view' => 'admin.products.partials.media.repeater',
        ],
        'product_additional_information_repeater' => [
            'view' => 'admin.products.partials.additional-information.repeater',
        ],
    ];

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, array{field: array<string, mixed>, children: array<int, array<string, mixed>>}>
     */
    public static function tree(array $fields): array
    {
        $nodes = [];
        $positions = [];

        foreach ($fields as $field) {
            $parent = $field['render_inside'] ?? null;

            if (filled($parent) && isset($positions[$parent])) {
                $nodes[$positions[$parent]]['children'][] = $field;

                continue;
            }

            $nodes[] = ['field' => $field, 'children' => []];

            // A container is addressed by its name, or by its type when it has
            // no name of its own (section wrappers such as Bottom Details).
            $key = $field['name'] ?? ($field['type'] ?? null);
            if (filled($key)) {
                $positions[$key] = array_key_last($nodes);
            }
        }

        return $nodes;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    public static function shouldRender(array $field, bool $editing): bool
    {
        if ($field['display_only'] ?? false) {
            return false;
        }

        if (($field['create_only'] ?? false) && $editing) {
            return false;
        }

        return ! (($field['edit_only'] ?? false) && ! $editing);
    }

    /**
     * @return array{view: string, wrap: bool}|null
     */
    public static function customView(string $type): ?array
    {
        $custom = self::CUSTOM_VIEWS[$type] ?? null;

        return $custom === null
            ? null
            : ['view' => $custom['view'], 'wrap' => $custom['wrap'] ?? true];
    }

    /**
     * Subsections declared by a container field, keyed by their `render_group`.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, string>
     */
    public static function groups(array $field): array
    {
        return array_filter((array) ($field['groups'] ?? []));
    }
}
