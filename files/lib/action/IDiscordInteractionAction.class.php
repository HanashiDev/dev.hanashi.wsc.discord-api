<?php

namespace wcf\action;

use Psr\Http\Message\ResponseInterface;

interface IDiscordInteractionAction
{
    /**
     * public key vom Discord Bot zurückgeben
     *
     * @return array
     */
    public function getPublicKeys(): array;

    /**
     * verarbeitet die von Discord gesendeten Daten für Application Commands
     */
    public function handleApplicationCommand(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Components
     */
    public function handleMessageCommand(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Application Command Autocomplete
     */
    public function handleApplicationCommandAutocomplete(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Modal
     */
    public function handleModalCommand(array $data): ResponseInterface;
}
