<?php

namespace wcf\data\discord\webhook;

use wcf\data\DatabaseObjectList;

/**
 * Discord-Webhook-Objekt-Liste
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Webhook
 *
 * @method  DiscordWebook     current()
 * @method  DiscordWebook[]       getObjects()
 * @method  DiscordWebook|null    getSingleObject()
 * @method  DiscordWebook|null    search($objectID)
 * @property    DiscordWebook[] $objects
 */
class DiscordWebhookList extends DatabaseObjectList
{
}
