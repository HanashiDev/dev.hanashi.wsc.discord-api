<?php

namespace wcf\data\discord\bot;

use wcf\data\DatabaseObjectList;

/**
 * Discord-Bot-Objekt-Liste
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Bot
 *
 * @extends DatabaseObjectList<DiscordBot>
 */
final class DiscordBotList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = DiscordBot::class;
}
