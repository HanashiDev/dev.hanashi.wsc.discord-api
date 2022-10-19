<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\discord\type\ChannelSelectDiscordType;

/**
 * Option-Type für die Auswahl eines Discord-Channels
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Option
 */
class DiscordChannelSelectOptionType extends AbstractOptionType
{
    protected $channelSelectType = [];

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        if (!isset($this->channelSelectType[$option->optionName])) {
            $this->channelSelectType[$option->optionName] = new ChannelSelectDiscordType($option->optionName);
        }
        $channelTypes = $this->getChannelTypes($option);

        return $this->channelSelectType[$option->optionName]->getFormElement($value, $channelTypes);
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        if (!isset($this->channelSelectType[$option->optionName])) {
            $this->channelSelectType[$option->optionName] = new ChannelSelectDiscordType($option->optionName);
        }
        $this->channelSelectType[$option->optionName]->validate($newValue, $option->maxChannels);
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue)
    {
        if (!isset($this->channelSelectType[$option->optionName])) {
            $this->channelSelectType[$option->optionName] = new ChannelSelectDiscordType($option->optionName);
        }

        return $this->channelSelectType[$option->optionName]->getData($newValue);
    }

    private function getChannelTypes(Option $option): array
    {
        $channelTypes = $option->channeltypes;
        if (empty($channelTypes)) {
            return [];
        }

        return \explode(',', $channelTypes);
    }
}
