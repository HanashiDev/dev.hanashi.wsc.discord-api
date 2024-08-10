<?php

namespace wcf\acp\page;

use Override;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\page\SortablePage;
use wcf\system\cache\builder\DiscordGuildChannelsCacheBuilder;
use wcf\system\WCF;

/**
 * Übersicht der erstellten Discord-Webhooks
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Acp\Page
 */
class DiscordWebhookListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.discord.canManageWebhooks'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordWebhookList';

    /**
     * @inheritDoc
     */
    public $objectListClassName = DiscordWebhookList::class;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'webhookID';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'ASC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['channelID', 'botID', 'webhookID', 'webhookName', 'webhookTitle', 'webhookTime'];

    protected array $channels = [];

    #[Override]
    public function readData()
    {
        parent::readData();

        $this->channels = DiscordGuildChannelsCacheBuilder::getInstance()->getData();
    }

    #[Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'channels' => $this->channels,
        ]);
    }
}
