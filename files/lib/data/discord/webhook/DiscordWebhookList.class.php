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
 * @method  DiscordWebhook     current()
 * @method  DiscordWebhook[]       getObjects()
 * @method  DiscordWebhook|null    getSingleObject()
 * @method  DiscordWebhook|null    search($objectID)
 * @property    DiscordWebhook[] $objects
 */
final class DiscordWebhookList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = DiscordWebhook::class;
}
