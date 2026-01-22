<?php

namespace wcf\data\discord\interaction\log;

use wcf\data\DatabaseObjectList;

/**
 * Discord-Webhook-Objekt-Liste
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Webhook
 *
 * @extends DatabaseObjectList<DiscordInteractionLog>
 */
final class DiscordInteractionLogList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = DiscordInteractionLog::class;
}
