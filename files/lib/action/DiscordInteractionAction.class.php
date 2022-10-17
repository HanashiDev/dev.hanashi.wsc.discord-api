<?php

namespace wcf\action;

use wcf\system\discord\event\ApplicationCommandAutocompleteReceived;
use wcf\system\discord\event\ApplicationCommandReceived;
use wcf\system\discord\event\MessageCommandReceived;
use wcf\system\discord\event\ModalCommandReceived;
use wcf\system\event\EventHandler;

class DiscordInteractionAction extends AbstractDiscordInteractionAction
{
    public function handleApplicationCommand(array $data)
    {
        EventHandler::getInstance()->fire(new ApplicationCommandReceived($data));

        /**
         * @deprecated
         */
        EventHandler::getInstance()->fireAction($this, 'applicationCommand', $data);
    }

    public function handleMessageCommand(array $data)
    {
        EventHandler::getInstance()->fire(new MessageCommandReceived($data));

        /**
         * @deprecated
         */
        EventHandler::getInstance()->fireAction($this, 'messageCommand', $data);
    }

    public function handleApplicationCommandAutocomplete(array $data)
    {
        EventHandler::getInstance()->fire(new ApplicationCommandAutocompleteReceived($data));

        /**
         * @deprecated
         */
        EventHandler::getInstance()->fireAction($this, 'applicationCommandAutocomplete', $data);
    }

    public function handleModalCommand(array $data)
    {
        EventHandler::getInstance()->fire(new ModalCommandReceived($data));

        /**
         * @deprecated
         */
        EventHandler::getInstance()->fireAction($this, 'modalCommand', $data);
    }
}
