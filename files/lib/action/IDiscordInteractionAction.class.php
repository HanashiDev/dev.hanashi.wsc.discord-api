<?php

namespace wcf\action;

use Psr\Http\Message\ResponseInterface;

interface IDiscordInteractionAction
{
    /**
     * public key vom Discord Bot zurückgeben
     *
     * @return array<mixed>
     */
    public function getPublicKeys(): array;

    /**
     * verarbeitet die von Discord gesendeten Daten für Application Commands
     *
     * @param array<mixed> $data
     */
    public function handleApplicationCommand(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Components
     *
     * @param array<mixed> $data
     */
    public function handleMessageCommand(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Application Command Autocomplete
     *
     * @param array<mixed> $data
     */
    public function handleApplicationCommandAutocomplete(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Modal
     *
     * @param array<mixed> $data
     */
    public function handleModalCommand(array $data): ResponseInterface;
}
