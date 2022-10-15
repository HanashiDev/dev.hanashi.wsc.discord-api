<?php

namespace wcf\action;

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
    public function handleApplicationCommand(array $data);

    /**
     * verarbeitet die von Discord gesendeten Daten für Components
     *
     * @param  array $data
     * @return void
     */
    public function handleMessageCommand(array $data);

    /**
     * verarbeitet die von Discord gesendeten Daten für Application Command Autocomplete
     *
     * @param  array $data
     * @return void
     */
    public function handleApplicationCommandAutocomplete(array $data);

    /**
     * verarbeitet die von Discord gesendeten Daten für Modal
     *
     * @param  array $data
     * @return void
     */
    public function handleModalCommand(array $data);
}
