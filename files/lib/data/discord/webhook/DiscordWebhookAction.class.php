<?php

namespace wcf\data\discord\webhook;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Discord-Webhook-Objekt-Action
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Webhook
 *
 * @method  DiscordWebhookEditor[] getObjects()
 * @method  DiscordWebhookEditor   getSingleObject()
 */
class DiscordWebhookAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.discord.canManageWebhooks'];

    /**
     * @inheritDoc
     */
    public function delete()
    {
        foreach ($this->objects as $object) {
            $discordWebhook = $object->getDecoratedObject();
            $discordApi = $discordWebhook->getDiscordApi();
            $discordApi->deleteWebhookWithToken($discordWebhook->webhookID, $discordWebhook->webhookToken);
        }

        return parent::delete();
    }
}
