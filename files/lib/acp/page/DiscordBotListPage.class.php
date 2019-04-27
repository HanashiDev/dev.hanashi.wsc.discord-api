<?php
namespace wcf\acp\page;
use wcf\data\discord\bot\DiscordBotList;
use wcf\page\SortablePage;

class DiscordBotListPage extends SortablePage {
    public $neededPermissions = ['admin.discord.canManageConnection'];

    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordBotList';

    public $objectListClassName = DiscordBotList::class;

    public $defaultSortField = 'botID';

    public $defaultSortOrder = 'ASC';

    public $validSortFields = ['botID', 'botName', 'guildID', 'guildName', 'botTime'];
}
