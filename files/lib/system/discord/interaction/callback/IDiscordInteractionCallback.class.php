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
     *
     * @return array<mixed>
     */
    public function getData(): ?array;

    /**
     * interaction response
     *
     * @return array<mixed>
     */
    public function getInteractionResponse(): array;
}
