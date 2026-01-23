<?php

namespace wcf\data\discord\webhook;

use Override;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\cache\builder\DiscordGuildChannelsCacheBuilder;

/**
 * Discord-Webhook-Objekt-Action
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Webhook
 *
 * @extends AbstractDatabaseObjectAction<DiscordWebhook, DiscordWebhookEditor>
 */
final class DiscordWebhookAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.discord.canManageWebhooks'];

    /**
     * @inheritDoc
     */
    public $className = DiscordWebhookEditor::class;

    #[Override]
    public function delete()
    {
        foreach ($this->objects as $object) {
            $discordWebhook = $object->getDecoratedObject();
            $discordApi = $discordWebhook->getDiscordApi();
            $discordApi->deleteWebhookWithToken($discordWebhook->webhookID, $discordWebhook->webhookToken);
        }

        return parent::delete();
    }

    #[Override]
    protected function resetCache()
    {
        DiscordGuildChannelsCacheBuilder::getInstance()->reset();
    }
}
