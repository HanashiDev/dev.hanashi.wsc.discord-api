<?php

namespace wcf\action;

use Override;
use wcf\system\discord\event\ApplicationCommandAutocompleteReceived;
use wcf\system\discord\event\ApplicationCommandReceived;
use wcf\system\discord\event\MessageCommandReceived;
use wcf\system\discord\event\ModalCommandReceived;
use wcf\system\event\EventHandler;

final class DiscordInteractionAction extends AbstractDiscordInteractionAction
{
    #[Override]
    public function handleApplicationCommand(array $data)
    {
        EventHandler::getInstance()->fire(new ApplicationCommandReceived($data));

        /**
         * @deprecated
         */
        EventHandler::getInstance()->fireAction($this, 'applicationCommand', $data);
    }

    #[Override]
    public function handleMessageCommand(array $data)
    {
        EventHandler::getInstance()->fire(new MessageCommandReceived($data));

        /**
         * @deprecated
         */
        EventHandler::getInstance()->fireAction($this, 'messageCommand', $data);
    }

    #[Override]
    public function handleApplicationCommandAutocomplete(array $data)
    {
        EventHandler::getInstance()->fire(new ApplicationCommandAutocompleteReceived($data));

        /**
         * @deprecated
         */
        EventHandler::getInstance()->fireAction($this, 'applicationCommandAutocomplete', $data);
    }

    #[Override]
    public function handleModalCommand(array $data)
    {
        EventHandler::getInstance()->fire(new ModalCommandReceived($data));

        /**
         * @deprecated
         */
        EventHandler::getInstance()->fireAction($this, 'modalCommand', $data);
    }
}
