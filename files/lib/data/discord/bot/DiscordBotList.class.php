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
 * @method  DiscordBot     current()
 * @method  DiscordBot[]       getObjects()
 * @method  DiscordBot|null    getSingleObject()
 * @method  DiscordBot|null    search($objectID)
 * @property    DiscordBot[] $objects
 */
class DiscordBotList extends DatabaseObjectList
{
}
