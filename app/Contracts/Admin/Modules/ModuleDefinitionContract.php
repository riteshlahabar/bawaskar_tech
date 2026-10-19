<?php

namespace App\Contracts\Admin\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * SRP: reading a module's definition out of config and naming its pages.
 */
interface ModuleDefinitionContract
{
    /**
     * @return array<string, mixed>
     */
    public function forKey(string $moduleKey): array;

    public function viewName(string $moduleKey, string $view): string;

    /**
     * @param  array<string, mixed>  $module
     */
    public function actionTitle(array $module, Request $request, string $action): string;

    /**
     * @param  array<string, mixed>  $module
     */
    public function pageTitle(array $module, Request $request): string;

    /**
     * Which sales channel ("customer" or "dealer") the page being rendered
     * belongs to, or null for a module that has no channel or a page opened
     * outside either submenu. A record answers for itself; otherwise the
     * submenu's ?type= decides.
     *
     * @param  array<string, mixed>  $module
     */
    public function channel(array $module, ?Request $request = null, ?Model $record = null): ?string;
}
