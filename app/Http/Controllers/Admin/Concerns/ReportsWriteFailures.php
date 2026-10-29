<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

/**
 * SRP: a database write an admin module could not complete becomes a red alert
 * on the page they were on, never a blank 500.
 *
 * Validation passing does not mean the write will succeed: a column may be
 * missing on this server, or the row may still be referenced by an order or
 * invoice. Those surface only as a QueryException at save or delete time, so
 * store, update, destroy and bulkDestroy all funnel through here. The wording
 * comes from SaveFailureMessage; the full exception still reaches the log.
 *
 * @property-read AdminModuleServices $modules
 */
trait ReportsWriteFailures
{
    /**
     * @param  array<string, mixed>  $module
     * @param  string  $action  past participle of what was attempted, so a
     *                          refused delete does not read "could not be saved"
     */
    protected function saveFailed(QueryException $exception, array $module, string $action = 'saved'): RedirectResponse
    {
        report($exception);

        return back()->withInput()->with('error', $this->modules->saveFailures->forModule($module, $exception, $action));
    }
}
