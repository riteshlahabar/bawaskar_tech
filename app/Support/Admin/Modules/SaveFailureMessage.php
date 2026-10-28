<?php

namespace App\Support\Admin\Modules;

use Illuminate\Database\QueryException;

/**
 * SRP: turns a database write failure into one short sentence an admin can act
 * on, instead of the blank 500 page a QueryException otherwise produces.
 *
 * A module form validates fine and then still fails when the database does not
 * match the code - a column the form writes is missing, a foreign key points at
 * a row that is gone, a unique index is already taken. The driver already says
 * which of those happened in `errorInfo[2]`; this only adds the one line that
 * says what to do about it. The full exception still goes to the log.
 */
final class SaveFailureMessage
{
    /**
     * SQLSTATE codes worth explaining, mapped to the action that fixes them.
     */
    private const HINTS = [
        '42S22' => 'The database is missing a column this form writes - run the pending migrations on this server.',
        '42S02' => 'The database is missing a table this form writes - run the pending migrations on this server.',
        '23000' => 'A linked record is missing or this value is already used.',
    ];

    /**
     * The whole sentence shown to the admin, module name included.
     *
     * @param  array<string, mixed>  $module
     */
    public function forModule(array $module, QueryException $exception): string
    {
        return ($module['singular'] ?? 'Record').' could not be saved. '.$this->describe($exception);
    }

    public function describe(QueryException $exception): string
    {
        $driverMessage = trim((string) ($exception->errorInfo[2] ?? ''));
        $hint = self::HINTS[(string) $exception->getCode()] ?? '';

        if ($driverMessage === '') {
            return $hint !== '' ? $hint : 'The database rejected the change. Check the log for details.';
        }

        return $hint === '' ? $driverMessage : $driverMessage.' '.$hint;
    }
}
