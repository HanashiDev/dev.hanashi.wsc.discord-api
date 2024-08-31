<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Override;
use Psr\Http\Message\ResponseInterface;
use wcf\http\attribute\DisableXsrfCheck;
use wcf\system\discord\event\ApplicationCommandAutocompleteReceived;
use wcf\system\discord\event\ApplicationCommandReceived;
use wcf\system\discord\event\MessageCommandReceived;
use wcf\system\discord\event\ModalCommandReceived;
use wcf\system\event\EventHandler;

#[DisableXsrfCheck]
final class DiscordInteractionAction extends AbstractDiscordInteractionAction
{
    #[Override]
    public function handleApplicationCommand(array $data): ResponseInterface
    {
        $event = new ApplicationCommandReceived($data);
        EventHandler::getInstance()->fire($event);

        $callback = $event->getCallback();
        if ($callback !== null) {
            return new JsonResponse($callback->getInteractionResponse());
        }

        return new EmptyResponse();
    }

    #[Override]
    public function handleMessageCommand(array $data): ResponseInterface
    {
        $event = new MessageCommandReceived($data);
        EventHandler::getInstance()->fire($event);

        $callback = $event->getCallback();
        if ($callback !== null) {
            return new JsonResponse($callback->getInteractionResponse());
        }

        return new EmptyResponse();
    }

    #[Override]
    public function handleApplicationCommandAutocomplete(array $data): ResponseInterface
    {
        $event = new ApplicationCommandAutocompleteReceived($data);
        EventHandler::getInstance()->fire($event);

        $callback = $event->getCallback();
        if ($callback !== null) {
            return new JsonResponse($callback->getInteractionResponse());
        }

        return new EmptyResponse();
    }

    #[Override]
    public function handleModalCommand(array $data): ResponseInterface
    {
        $event = new ModalCommandReceived($data);
        EventHandler::getInstance()->fire($event);

        $callback = $event->getCallback();
        if ($callback !== null) {
            return new JsonResponse($callback->getInteractionResponse());
        }

        return new EmptyResponse();
    }
}
