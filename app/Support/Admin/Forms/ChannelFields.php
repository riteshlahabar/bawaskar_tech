<?php

namespace App\Support\Admin\Forms;

/**
 * SRP: whether one configured field or column belongs on a sales channel page.
 *
 * The Sales group is split into a Customer and a Dealer submenu, so a field
 * such as Salesman or a Dealer column only makes sense on one of them. Two
 * config keys say so, and both the form and the listing read them through
 * here so the rule is written once:
 *
 *   'channels' => ['dealer']   the field/column appears on dealer pages only
 *   'hide_on_channel' => true  the submenu already says it (the Channel column)
 *   'channel_locked' => true   the submenu decides it: the field is not shown
 *                              but is still posted and still validated
 *
 * A null channel means the page is not scoped to either submenu, and then
 * everything is shown exactly as before.
 */
final class ChannelFields
{
    /**
     * Whether this field or column has any place on the given channel's page.
     *
     * @param  array<string, mixed>  $field
     */
    public function belongsTo(array $field, ?string $channel): bool
    {
        if ($channel === null) {
            return true;
        }

        if ($field['hide_on_channel'] ?? false) {
            return false;
        }

        $channels = array_filter((array) ($field['channels'] ?? []));

        return $channels === [] || in_array($channel, $channels, true);
    }

    /**
     * Whether the channel decides this field's value, so the form posts it as
     * a hidden input instead of asking. Its rules are kept: the value is still
     * submitted, it just cannot be changed from inside the submenu.
     *
     * @param  array<string, mixed>  $field
     */
    public function isLocked(array $field, ?string $channel): bool
    {
        return $channel !== null && ($field['channel_locked'] ?? false);
    }

    /**
     * The hidden inputs a channel-scoped form has to post, as name => value.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, string>
     */
    public function lockedValues(array $fields, ?string $channel): array
    {
        if ($channel === null) {
            return [];
        }

        $locked = [];

        foreach ($fields as $field) {
            if (filled($field['name'] ?? null) && $this->isLocked($field, $channel)) {
                $locked[(string) $field['name']] = $channel;
            }
        }

        return $locked;
    }
}
