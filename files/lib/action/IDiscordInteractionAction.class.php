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
     *
     * @param  array $data
     * @return void
     */
    public function handleApplicationCommand(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Components
     *
     * @param  array $data
     * @return void
     */
    public function handleMessageCommand(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Application Command Autocomplete
     *
     * @param  array $data
     * @return void
     */
    public function handleApplicationCommandAutocomplete(array $data): ResponseInterface;

    /**
     * verarbeitet die von Discord gesendeten Daten für Modal
     *
     * @param  array $data
     * @return void
     */
    public function handleModalCommand(array $data): ResponseInterface;
}
