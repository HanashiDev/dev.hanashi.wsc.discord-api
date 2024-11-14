<?php

namespace wcf\acp\page;

use Override;
use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\DiscordBotListGridView;

/**
 * @property DiscordBotListGridView $gridView
 */
class DiscordBotListPage extends AbstractGridViewPage
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
    protected function createGridViewController(): AbstractGridView
    {
        return new DiscordBotListGridView();
    }
}
