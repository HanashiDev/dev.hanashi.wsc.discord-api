<?php

namespace wcf\command\discord\bot;

use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotAction;
use wcf\event\discord\bot\DiscordBotDeleted;
use wcf\system\event\EventHandler;

final class DeleteDiscordBot
{
    public function __construct(
        private readonly DiscordBot $bot
    ) {
    }

    public function __invoke(): void
    {
        $action = new DiscordBotAction([$this->bot], 'delete');
        $action->executeAction();

        EventHandler::getInstance()->fire(new DiscordBotDeleted($this->bot));
    }
}
