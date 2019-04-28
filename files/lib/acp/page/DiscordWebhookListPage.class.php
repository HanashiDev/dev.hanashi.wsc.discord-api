<?php
namespace wcf\acp\page;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\page\SortablePage;

class DiscordWebhookListPage extends SortablePage {
    public $neededPermissions = ['admin.discord.canManageWebhooks'];

    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordWebhookList';

    public $objectListClassName = DiscordWebhookList::class;

    public $defaultSortField = 'webhookID';

    public $defaultSortOrder = 'ASC';

    public $validSortFields = ['channelID', 'botID', 'webhookID', 'webhookName', 'webhookTitle', 'webhookTime'];
}
