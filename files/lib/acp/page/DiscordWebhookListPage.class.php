<?php

namespace wcf\acp\page;

use Override;
use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\DiscordWebhookGridView;

/**
 * Übersicht der erstellten Discord-Webhooks
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Acp\Page
 *
 * @extends AbstractGridViewPage<DiscordWebhookGridView>
 */
final class DiscordWebhookListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.discord.canManageWebhooks'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordWebhookList';

    #[Override]
    protected function createGridView(): DiscordWebhookGridView
    {
        return new DiscordWebhookGridView();
    }
}
