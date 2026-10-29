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
     * SQLSTATE 23000 covers three very different problems, so the MySQL error
     * number decides the wording. 1451 is the one an admin meets most: the row
     * is still referenced elsewhere - typically a product on an order or
     * invoice - and the honest answer is to deactivate it, not delete it.
     */
    private const INTEGRITY_HINTS = [
        1451 => 'This record is still used by other records - most often an order or invoice - so it cannot be deleted. Switch it to Inactive instead.',
        1452 => 'A record this one links to no longer exists. Pick an existing one.',
        1062 => 'That value is already used by another record. Enter a different one.',
    ];

    /**
     * The whole sentence shown to the admin, module name included. `$action` is
     * the past participle of what was attempted, so a refused delete does not
     * read "could not be saved".
     *
     * @param  array<string, mixed>  $module
     */
    public function forModule(array $module, QueryException $exception, string $action = 'saved'): string
    {
        return ($module['singular'] ?? 'Record').' could not be '.$action.'. '.$this->describe($exception);
    }

    public function describe(QueryException $exception): string
    {
        $driverMessage = trim((string) ($exception->errorInfo[2] ?? ''));
        $errorNumber = (int) ($exception->errorInfo[1] ?? 0);
        $hint = self::INTEGRITY_HINTS[$errorNumber] ?? self::HINTS[(string) $exception->getCode()] ?? '';

        // A blocked delete needs the reason, not the raw constraint name.
        if (isset(self::INTEGRITY_HINTS[$errorNumber])) {
            return $hint;
        }

        if ($driverMessage === '') {
            return $hint !== '' ? $hint : 'The database rejected the change. Check the log for details.';
        }

        return $hint === '' ? $driverMessage : $driverMessage.' '.$hint;
    }
}
