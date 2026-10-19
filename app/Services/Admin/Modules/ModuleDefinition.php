<?php

namespace App\Services\Admin\Modules;

use App\Contracts\Admin\Modules\ModuleDefinitionContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ModuleDefinition implements ModuleDefinitionContract
{
    /**
     * The only two sales channels; a page is scoped to one of them or to
     * neither, never to anything else.
     */
    private const CHANNELS = ['customer', 'dealer'];

    public function forKey(string $moduleKey): array
    {
        $module = config('admin.modules.'.$moduleKey);
        abort_if(! $module, 404);

        return array_merge([
            'key' => $moduleKey,
            'singular' => Str::singular($module['label']),
            'route' => 'admin.'.$moduleKey,
        ], $module);
    }

    /**
     * A module may ship its own view; otherwise the shared one is used.
     */
    public function viewName(string $moduleKey, string $view): string
    {
        $moduleView = 'admin.'.$moduleKey.'.'.$view;

        return view()->exists($moduleView)
            ? $moduleView
            : 'admin.shared.'.($view === 'edit' || $view === 'create' ? 'form' : $view);
    }

    public function actionTitle(array $module, Request $request, string $action): string
    {
        $label = (string) ($module['singular'] ?? $module['label'] ?? 'Record');

        if ($request->filled('row_title')) {
            $rowTitle = (string) $request->query('row_title');
            $label = preg_replace('/^Row\s+\d+\s*-\s*/i', '', $rowTitle) ?: $rowTitle;
        }

        return trim($action.' '.$label);
    }

    public function pageTitle(array $module, Request $request): string
    {
        if ($request->filled('row_title')) {
            return (string) $request->query('row_title');
        }

        $channel = $request->query('type');

        if (($module['group'] ?? null) === 'Sales' && in_array($channel, self::CHANNELS, true)) {
            return Str::title((string) $channel).' '.$module['label'];
        }

        return $module['label'];
    }

    public function channel(array $module, ?Request $request = null, ?Model $record = null): ?string
    {
        $column = $module['channel']['column'] ?? null;

        if (blank($column)) {
            return null;
        }

        // An existing record answers for itself, so editing or viewing a dealer
        // order shows dealer fields whatever the URL says.
        if ($record) {
            $relation = $module['channel']['relation'] ?? null;
            $value = data_get($record, $relation ? $relation.'.'.$column : $column);

            return in_array($value, self::CHANNELS, true) ? (string) $value : null;
        }

        $type = $request?->query('type');

        return in_array($type, self::CHANNELS, true) ? (string) $type : null;
    }
}
