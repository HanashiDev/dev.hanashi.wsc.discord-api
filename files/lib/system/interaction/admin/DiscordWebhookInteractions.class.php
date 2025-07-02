<?php

namespace wcf\system\interaction\admin;

use Override;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\webhook\DiscordWebhook;
use wcf\event\interaction\admin\DiscordWebhookInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;

final class DiscordWebhookInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction('hanashi/discord/webhook/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new DiscordWebhookInteractionCollecting($this)
        );
    }

    #[Override]
    public function getObjectClassName(): string
    {
        return DiscordWebhook::class;
    }
}
