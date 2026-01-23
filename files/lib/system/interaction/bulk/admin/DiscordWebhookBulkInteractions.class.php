<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\event\interaction\bulk\admin\DiscordWebhookBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;

final class DiscordWebhookBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new BulkDeleteInteraction("hanashi/discord/webhook/%s"),
        ]);

        EventHandler::getInstance()->fire(
            new DiscordWebhookBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return DiscordWebhookList::class;
    }
}
