<?php

namespace wcf\system\discord\event;

use wcf\event\IPsr14Event;
use wcf\system\discord\interaction\callback\IDiscordInteractionCallback;

interface ICommandReceived extends IPsr14Event
{
    /**
     * get input data
     */
    public function getData(): array;

    /**
     * set callback informations
     */
    public function setCallback(IDiscordInteractionCallback $response): void;

    /**
     * get callback informations
     */
    public function getCallback(): ?IDiscordInteractionCallback;
}
