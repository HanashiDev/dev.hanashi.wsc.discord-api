<?php
namespace wcf\acp\page;
use wcf\data\discord\bot\DiscordBotList;
use wcf\page\SortablePage;

/**
 * Übersicht aller Discord-Bots
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\Acp\Page
 */
class DiscordBotListPage extends SortablePage {
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.discord.canManageConnection'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordBotList';

    /**
     * @inheritDoc
     */
    public $objectListClassName = DiscordBotList::class;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'botID';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'ASC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['botID', 'botName', 'guildID', 'guildName', 'botTime'];
}
