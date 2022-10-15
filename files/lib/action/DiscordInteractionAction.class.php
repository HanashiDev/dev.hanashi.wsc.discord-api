<?php

namespace wcf\action;

use wcf\system\event\EventHandler;

class DiscordInteractionAction extends AbstractDiscordInteractionAction
{
    public function handleApplicationCommand(array $data)
    {
        EventHandler::getInstance()->fireAction($this, 'applicationCommand', $data);
    }

    public function handleMessageCommand(array $data)
    {
        EventHandler::getInstance()->fireAction($this, 'messageCommand', $data);
    }

    public function handleApplicationCommandAutocomplete(array $data)
    {
        EventHandler::getInstance()->fireAction($this, 'applicationCommandAutocomplete', $data);
    }

    public function handleModalCommand(array $data)
    {
        EventHandler::getInstance()->fireAction($this, 'modalCommand', $data);
    }
}
