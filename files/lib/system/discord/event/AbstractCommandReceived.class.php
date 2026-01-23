<?php

namespace wcf\system\discord\event;

use wcf\system\discord\interaction\callback\IDiscordInteractionCallback;

abstract class AbstractCommandReceived implements ICommandReceived
{
    protected IDiscordInteractionCallback $response;

    /**
     * @param array<mixed> $data
     */
    public function __construct(private array $data)
    {
    }

    #[\Override]
    public function getData(): array
    {
        return $this->data;
    }

    #[\Override]
    public function setCallback(IDiscordInteractionCallback $response): void
    {
        $this->response = $response;
    }

    #[\Override]
    public function getCallback(): ?IDiscordInteractionCallback
    {
        return $this->response ?? null;
    }
}
