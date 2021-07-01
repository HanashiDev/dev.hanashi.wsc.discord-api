<?php

namespace wcf\action;

interface IDiscordInteractionAction
{
    /**
     * public key vom Discord Bot zurückgeben
     *
     * @return string
     */
    public function getPublicKey();

    /**
     * verarbeitet die von Discord gesendeten Daten für Slash Commands
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
}
