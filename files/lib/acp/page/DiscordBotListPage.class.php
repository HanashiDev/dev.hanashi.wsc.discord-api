<?php

namespace wcf\acp\page;

use Override;
use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\DiscordBotGridView;

/**
 * Übersicht aller Discord-Bots
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Acp\Page
 *
 * @extends AbstractGridViewPage<DiscordBotGridView>
 */
final class DiscordBotListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.discord.canManageConnection'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordBotList';

    #[Override]
    protected function createGridView(): DiscordBotGridView
    {
        return new DiscordBotGridView();
    }
}
