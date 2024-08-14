<?php

namespace wcf\system\discord\interaction\callback;

interface IDiscordInteractionCallback
{
    /**
     * the type of response
     */
    public function getType(): int;

    /**
     * an optional response message
     */
    public function getData(): ?array;

    /**
     * interaction response
     */
    public function getInteractionResponse(): array;
}
