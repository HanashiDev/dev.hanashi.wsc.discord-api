<?php

namespace wcf\data\discord\webhook;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\event\discord\webhook\DiscordWebhookCreated;
use wcf\event\discord\webhook\DiscordWebhookUpdated;
use wcf\system\cache\builder\DiscordGuildChannelsCacheBuilder;
use wcf\system\event\EventHandler;

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

    #[\Override]
    public function create()
    {
        $webhook = parent::create();

        EventHandler::getInstance()->fire(new DiscordWebhookCreated($webhook));

        return $webhook;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $webhook) {
            $updatedWebhook = new DiscordWebhook($webhook->webhookID);
            EventHandler::getInstance()->fire(new DiscordWebhookUpdated($updatedWebhook));
        }
    }

    #[\Override]
    protected function resetCache()
    {
        DiscordGuildChannelsCacheBuilder::getInstance()->reset();
    }
}
