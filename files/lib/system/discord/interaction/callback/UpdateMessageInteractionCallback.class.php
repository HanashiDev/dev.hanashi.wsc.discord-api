<?php

namespace wcf\system\discord\interaction\callback;

use wcf\system\discord\DiscordApi;

final class UpdateMessageInteractionCallback extends AbstractInteractionCallback
{
    /**
     * @inheritDoc
     */
    protected int $type = DiscordApi::DISCORD_UPDATE_MESSAGE;
}
