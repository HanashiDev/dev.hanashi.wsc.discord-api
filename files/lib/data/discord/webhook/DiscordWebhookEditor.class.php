<?php

namespace wcf\data\discord\webhook;

use wcf\data\DatabaseObjectEditor;

/**
 * Discord-Webhook-Objekt-Editor
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Webhook
 *
 * @method static DiscordWebhook     create(array $parameters = [])
 * @method      DiscordWebhook     getDecoratedObject()
 * @mixin       DiscordWebhook
 */
class DiscordWebhookEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = DiscordWebhook::class;
}
