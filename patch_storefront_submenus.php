<?php

$stamp = date('Ymd-His');

function backup_file(string $file, string $stamp): void {
    if (file_exists($file)) {
        copy($file, $file.'.bak-storefront-submenu-'.$stamp);
        echo "Backup: {$file}\n";
    }
}

function replace_required(string &$content, string $search, string $replace, string $label): void {
    if (! str_contains($content, $search)) {
        echo "SKIP: {$label} not found\n";
        return;
    }

    $content = str_replace($search, $replace, $content);
    echo "OK: {$label}\n";
}

$files = [
    'config/admin.php',
    'app/Http/Controllers/Admin/Concerns/AdminModuleController.php',
    'resources/views/admin/shared/form.blade.php',
    'resources/views/admin/shared/index.blade.php',
    'resources/views/admin/shared/show.blade.php',
];

foreach ($files as $file) {
    backup_file($file, $stamp);
}

/*
|--------------------------------------------------------------------------
| 1) Storefront submenu: keep only four required special storefront pages
|--------------------------------------------------------------------------
*/
$configFile = 'config/admin.php';
$config = file_get_contents($configFile);

$config = preg_replace(
    "/\\['label'=>'Storefront','id'=>'storefrontMenu','icon'=>'iconoir-www','items'=>\\[.*?\\]\\],\\s*\\['label'=>'Finance'/s",
    "['label'=>'Storefront','id'=>'storefrontMenu','icon'=>'iconoir-www','items'=>[
            ['key'=>'sf-row-5-bank-offers','label'=>'Row 5 - Bank & Wallet Offers','route'=>'admin.storefront-banners.index','params'=>['placement'=>'bank_offer','row_title'=>'Row 5 - Bank & Wallet Offers'],'icon'=>'iconoir-media-image'],
            ['key'=>'sf-row-7-strip-banner','label'=>'Row 7 - Small Strip Banner','route'=>'admin.storefront-banners.index','params'=>['placement'=>'strip_banner','row_title'=>'Row 7 - Small Strip Banner'],'icon'=>'iconoir-media-image'],
            ['key'=>'sf-row-13-personal-care-banner','label'=>'Row 13 - Banner Above Personal Care','route'=>'admin.storefront-banners.index','params'=>['placement'=>'footer_promo','row_title'=>'Row 13 - Banner Above Personal Care'],'icon'=>'iconoir-media-image'],
            ['key'=>'sf-row-16-blog','label'=>'Row 16 - Bottom Blog','route'=>'admin.storefront-sections.index','params'=>['section_key'=>'row_16_blog','row_title'=>'Row 16 - Bottom Blog'],'icon'=>'iconoir-page'],
        ]],

        ['label'=>'Finance'",
    $config,
    1,
    $count
);
echo $count ? "OK: Storefront four submenu updated\n" : "SKIP: Storefront menu block not found\n";

/*
|--------------------------------------------------------------------------
| 2) Banner module: add strip banner placement + button fields
|--------------------------------------------------------------------------
*/
$config = preg_replace(
    "/'storefront-banners'=>\\[\\s*'label'=>'Home Banners'.*?\\n\\s*\\],\\s*\\n\\s*'storefront-sections'=>/s",
    "'storefront-banners'=>[
            'label'=>'Home Banners','group'=>'Storefront','singular'=>'Home Banner','model'=>StorefrontBanner::class,'search'=>['placement','title','subtitle','button_text'],'status_column'=>'is_active','status_options'=>\$active,
            'columns'=>[
                ['key'=>'image_path','label'=>'Image','type'=>'image'],
                ['key'=>'placement','label'=>'Placement'],
                ['key'=>'title','label'=>'Title'],
                ['key'=>'subtitle','label'=>'Subtitle'],
                ['key'=>'button_text','label'=>'Button'],
                ['key'=>'button_url','label'=>'Button URL'],
                ['key'=>'sort_order','label'=>'Sort Order'],
                ['key'=>'is_active','label'=>'Status','type'=>'boolean']
            ],
            'fields'=>[
                ['name'=>'placement','label'=>'Placement','type'=>'select','options'=>[
                    'hero_main'=>'Hero Main - 1920 x 637',
                    'promo_small'=>'Small Promo Banner - 375 x 243',
                    'bank_offer'=>'Row 5 - Bank / Wallet Offer',
                    'strip_banner'=>'Row 7 - Small Strip Banner',
                    'middle_promo'=>'Row 10 - Two Promo Banners',
                    'footer_promo'=>'Row 13 - Banner Above Personal Care'
                ],'rules'=>['required','string','max:80']],
                ['name'=>'title','label'=>'Title','rules'=>['nullable','string','max:255']],
                ['name'=>'subtitle','label'=>'Subtitle','rules'=>['nullable','string','max:255']],
                ['name'=>'description','label'=>'Description','type'=>'textarea','col'=>'col-12','rules'=>['nullable','string']],
                ['name'=>'button_text','label'=>'Button Text','rules'=>['nullable','string','max:255']],
                ['name'=>'button_url','label'=>'Button URL','rules'=>['nullable','string','max:255']],
                ['name'=>'image_path','label'=>'Banner Image','type'=>'image','upload_dir'=>'uploads/storefront/banners','rules'=>['nullable','image','max:5120']],
                ['name'=>'sort_order','label'=>'Sort Order','type'=>'number','rules'=>['nullable','integer','min:0']],
                ['name'=>'is_active','label'=>'Active','type'=>'checkbox','rules'=>['boolean']]
            ],
        ],
        'storefront-sections'=>",
    $config,
    1,
    $count
);
echo $count ? "OK: Storefront banner fields updated\n" : "SKIP: Storefront banner module block not found\n";

file_put_contents($configFile, $config);

/*
|--------------------------------------------------------------------------
| 3) Controller: preserve submenu query after save/delete + defaults for Row 16
|--------------------------------------------------------------------------
*/
$controllerFile = 'app/Http/Controllers/Admin/Concerns/AdminModuleController.php';
$controller = file_get_contents($controllerFile);

replace_required(
    $controller,
    "return redirect()->route(\$module['route'].'.edit', \$record->getKey())->with('success', \$module['singular'].' created successfully.');",
    "return redirect()->route(\$module['route'].'.edit', array_merge([\$record->getKey()], \$request->only(['type', 'placement', 'section_key', 'row_title'])))->with('success', \$module['singular'].' created successfully.');",
    'store redirect keeps submenu params'
);

replace_required(
    $controller,
    "return redirect()->route(\$module['route'].'.index')->with('success', \$module['singular'].' deleted successfully.');",
    "return redirect()->route(\$module['route'].'.index', request()->only(['type', 'placement', 'section_key', 'row_title']))->with('success', \$module['singular'].' deleted successfully.');",
    'delete redirect keeps submenu params'
);

replace_required(
    $controller,
    "if (! in_array(\$this->moduleKey, ['products', 'pricing', 'categories', 'brands', 'units', 'languages', 'translations'], true)) {",
    "if (! in_array(\$this->moduleKey, ['products', 'pricing', 'categories', 'brands', 'units', 'languages', 'translations', 'storefront-banners', 'storefront-sections', 'storefront-section-products', 'storefront-service-blocks', 'storefront-footer-links'], true)) {",
    'storefront cache version included'
);

$needle = "        return \$data;\n    }\n\n    protected function formData";
$insert = "        if ((\$module['key'] ?? '') === 'storefront-sections' && \$request->query('section_key') === 'row_16_blog') {
            \$data['title'] = \$data['title'] ?? (string) \$request->query('row_title', 'Bottom Blog');
            \$data['section_type'] = \$data['section_type'] ?? 'offer';
            \$data['source_type'] = \$data['source_type'] ?? 'manual';
            \$data['product_limit'] = \$data['product_limit'] ?? 3;
            \$data['sort_order'] = \$data['sort_order'] ?? 16;
            \$data['is_active'] = \$data['is_active'] ?? true;
        }

        return \$data;
    }

    protected function formData";

replace_required($controller, $needle, $insert, 'Row 16 default values');

file_put_contents($controllerFile, $controller);

/*
|--------------------------------------------------------------------------
| 4) Form: hide Placement / Section Key when opened from submenu
|--------------------------------------------------------------------------
*/
$formFile = 'resources/views/admin/shared/form.blade.php';
$form = file_get_contents($formFile);

replace_required(
    $form,
    "    \$hasUpload = collect(\$module['fields'] ?? [])->contains(fn (\$field) => in_array(\$field['type'] ?? '', ['file', 'image', 'image_multiple'], true));\n@endphp",
    "    \$hasUpload = collect(\$module['fields'] ?? [])->contains(fn (\$field) => in_array(\$field['type'] ?? '', ['file', 'image', 'image_multiple'], true));
    \$submenuQueryKeys = ['type', 'placement', 'section_key', 'row_title'];
    \$fieldNames = collect(\$module['fields'] ?? [])->pluck('name')->filter()->values()->all();
@endphp",
    'form query variable added'
);

replace_required(
    $form,
    "                    @if(\$record) @method('PUT') @endif\n\n                    <div class=\"row g-3\">",
    "                    @if(\$record) @method('PUT') @endif

                    @foreach(request()->only(\$submenuQueryKeys) as \$queryKey => \$queryValue)
                        @if(is_scalar(\$queryValue) && ! in_array(\$queryKey, \$fieldNames, true))
                            <input type=\"hidden\" name=\"{{ \$queryKey }}\" value=\"{{ \$queryValue }}\">
                        @endif
                    @endforeach

                    <div class=\"row g-3\">",
    'hidden row_title/type query added'
);

replace_required(
    $form,
    "                            @php(\$value = old(\$name, \$formData[\$name] ?? (\$field['default'] ?? null)))\n\n                            <div class=\"{{ \$field['col'] ?? 'col-md-6' }}\">",
    "                            @php(\$value = old(\$name, \$formData[\$name] ?? (\$field['default'] ?? null)))
                            @php(\$lockedBySubmenu = in_array(\$module['key'] ?? '', ['storefront-banners', 'storefront-sections'], true) && in_array(\$name, ['placement', 'section_key'], true) && request()->filled(\$name))

                            @if(\$lockedBySubmenu)
                                <input type=\"hidden\" name=\"{{ \$name }}\" value=\"{{ \$value }}\">
                                @continue
                            @endif

                            <div class=\"{{ \$field['col'] ?? 'col-md-6' }}\">",
    'placement/section key hidden on submenu add/edit'
);

file_put_contents($formFile, $form);

/*
|--------------------------------------------------------------------------
| 5) Index/show buttons: preserve submenu query for Add/Edit/View/Delete/Back
|--------------------------------------------------------------------------
*/
$indexFile = 'resources/views/admin/shared/index.blade.php';
$index = file_get_contents($indexFile);

replace_required(
    $index,
    "route(\$module['route'].'.show', \$record->getKey())",
    "route(\$module['route'].'.show', array_merge([\$record->getKey()], request()->only(['type','placement','section_key','row_title'])))",
    'index view link keeps submenu params'
);

replace_required(
    $index,
    "route(\$module['route'].'.edit', \$record->getKey())",
    "route(\$module['route'].'.edit', array_merge([\$record->getKey()], request()->only(['type','placement','section_key','row_title'])))",
    'index edit link keeps submenu params'
);

replace_required(
    $index,
    "route(\$module['route'].'.destroy', \$record->getKey())",
    "route(\$module['route'].'.destroy', array_merge([\$record->getKey()], request()->only(['type','placement','section_key','row_title'])))",
    'index delete link keeps submenu params'
);

file_put_contents($indexFile, $index);

$showFile = 'resources/views/admin/shared/show.blade.php';
$show = file_get_contents($showFile);

replace_required(
    $show,
    "route(\$module['route'].'.index')",
    "route(\$module['route'].'.index', request()->only(['type','placement','section_key','row_title']))",
    'show back link keeps submenu params'
);

replace_required(
    $show,
    "route(\$module['route'].'.edit', \$record->getKey())",
    "route(\$module['route'].'.edit', array_merge([\$record->getKey()], request()->only(['type','placement','section_key','row_title'])))",
    'show edit link keeps submenu params'
);

file_put_contents($showFile, $show);

echo "\nDONE storefront submenu patch.\n";
