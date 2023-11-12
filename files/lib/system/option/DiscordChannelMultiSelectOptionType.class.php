<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\discord\type\ChannelMultiSelectDiscordType;

/**
 * Option-Type für die Auswahl mehrerer Discord-Channel
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Option
 */
class DiscordChannelMultiSelectOptionType extends AbstractOptionType
{
    protected $channelMultiSelectType = [];

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        if (!isset($this->channelMultiSelectType[$option->optionName])) {
            $this->channelMultiSelectType[$option->optionName] = new ChannelMultiSelectDiscordType($option->optionName);
        }
        $channelTypes = $this->getChannelTypes($option);

        return $this->channelMultiSelectType[$option->optionName]->getFormElement($value, $channelTypes);
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        if (!isset($this->channelMultiSelectType[$option->optionName])) {
            $this->channelMultiSelectType[$option->optionName] = new ChannelMultiSelectDiscordType($option->optionName);
        }
        $this->channelMultiSelectType[$option->optionName]->validate($newValue);
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue)
    {
        if (!isset($this->channelMultiSelectType[$option->optionName])) {
            $this->channelMultiSelectType[$option->optionName] = new ChannelMultiSelectDiscordType($option->optionName);
        }

        return $this->channelMultiSelectType[$option->optionName]->getData($newValue);
    }

    private function getChannelTypes(Option $option): array
    {
        $channelTypes = $option->channeltypes;
        if ($channelTypes === null || $channelTypes === '') {
            return [];
        }

        return \explode(',', $channelTypes);
    }
}
