<?php

namespace wcf\system\discord\interaction\callback;

use wcf\system\discord\DiscordApi;

final class ApplicationCommandAutocompleteResultInteractionCallback extends AbstractInteractionCallback
{
    /**
     * @inheritDoc
     */
    protected int $type = DiscordApi::DISCORD_APPLICATION_COMMAND_AUTOCOMPLETE_RESULT;
}
