<?php

namespace wcf\system\discord\interaction\callback;

use wcf\system\discord\DiscordApi;

final class ModalInteractionCallback extends AbstractInteractionCallback
{
    /**
     * @inheritDoc
     */
    protected int $type = DiscordApi::DISCORD_MODAL;
}
