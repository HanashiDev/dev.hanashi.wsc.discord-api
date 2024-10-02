<?php

use wcf\acp\form\DiscordBotAddManagerForm;
use wcf\acp\page\DiscordBotListPage;
use wcf\acp\page\DiscordWebhookListPage;
use wcf\event\acp\dashboard\box\PHPExtensionCollecting;
use wcf\event\acp\menu\item\ItemCollecting;
use wcf\event\worker\RebuildWorkerCollecting;
use wcf\system\event\EventHandler;
use wcf\system\menu\acp\AcpMenuItem;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\system\worker\DiscordWebhookAvatarRebuildDataWorker;

return static function (): void {
    EventHandler::getInstance()->register(ItemCollecting::class, static function (ItemCollecting $event) {
        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.configuration.discord',
                '',
                'wcf.acp.menu.link.configuration'
            )
        );

        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.management.discord',
                '',
                'wcf.acp.menu.link.management'
            )
        );

        if (WCF::getSession()->getPermission('admin.discord.canManageConnection')) {
            $event->register(
                new AcpMenuItem(
                    'wcf.acp.menu.link.configuration.discord.discordBotList',
                    '',
                    'wcf.acp.menu.link.configuration.discord',
                    LinkHandler::getInstance()->getControllerLink(DiscordBotListPage::class)
                )
            );

            $event->register(
                new AcpMenuItem(
                    'wcf.acp.menu.link.configuration.discord.discordBotList.add',
                    '',
                    'wcf.acp.menu.link.configuration.discord.discordBotList',
                    LinkHandler::getInstance()->getControllerLink(DiscordBotAddManagerForm::class),
                    FontAwesomeIcon::fromString('plus;false')
                )
            );
        }

        if (WCF::getSession()->getPermission('admin.discord.canManageWebhooks')) {
            $event->register(
                new AcpMenuItem(
                    'wcf.acp.menu.link.configuration.discord.discordWebhookList',
                    '',
                    'wcf.acp.menu.link.configuration.discord',
                    LinkHandler::getInstance()->getControllerLink(DiscordWebhookListPage::class)
                )
            );
        }
    });

    EventHandler::getInstance()->register(
        PHPExtensionCollecting::class,
        static function (PHPExtensionCollecting $event) {
            $event->register('sodium');
        }
    );

    EventHandler::getInstance()->register(
        RebuildWorkerCollecting::class,
        static function (RebuildWorkerCollecting $event) {
            $event->register(DiscordWebhookAvatarRebuildDataWorker::class, 0);
        }
    );
};
