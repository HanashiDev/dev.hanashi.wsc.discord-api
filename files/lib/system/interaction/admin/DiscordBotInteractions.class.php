<?php

namespace wcf\system\interaction\admin;

use Override;
use wcf\data\discord\bot\DiscordBot;
use wcf\event\interaction\admin\DiscordBotInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;

final class DiscordBotInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction('hanashi/discord/bot/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new DiscordBotInteractionCollecting($this)
        );
    }

    #[Override]
    public function getObjectClassName(): string
    {
        return DiscordBot::class;
    }
}
