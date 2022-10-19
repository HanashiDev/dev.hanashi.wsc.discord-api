<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\discord\type\WebhookChannelSelectDiscordType;

/**
 * Option-Type für die Auswahl eines Discord-Channels mit Webhook-Bezug
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Option
 */
class DiscordWebhookChannelSelectOptionType extends DiscordChannelSelectOptionType
{
    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        $channelMultiSelectType = new WebhookChannelSelectDiscordType($option->optionName);
        $channelMultiSelectType->validate($newValue, $option->maxChannels);
    }
}
