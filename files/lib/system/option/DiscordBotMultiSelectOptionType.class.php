<?php

namespace wcf\system\option;

use Override;
use wcf\data\option\Option;
use wcf\system\discord\type\BotMultiSelectType;

/**
 * Option-Type für die Auswahl mehrere Discord-Bots
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Option
 */
class DiscordBotMultiSelectOptionType extends AbstractOptionType
{
    protected $botMultiSelectType = [];

    #[Override]
    public function getFormElement(Option $option, $value)
    {
        if (!isset($this->botMultiSelectType[$option->optionName])) {
            $this->botMultiSelectType[$option->optionName] = new BotMultiSelectType($option->optionName);
        }

        return $this->botMultiSelectType[$option->optionName]->getFormElement($value);
    }

    #[Override]
    public function validate(Option $option, $newValue)
    {
        if (!isset($this->botMultiSelectType[$option->optionName])) {
            $this->botMultiSelectType[$option->optionName] = new BotMultiSelectType($option->optionName);
        }
        $this->botMultiSelectType[$option->optionName]->validate($newValue);
    }

    #[Override]
    public function getData(Option $option, $newValue)
    {
        if (!isset($this->botMultiSelectType[$option->optionName])) {
            $this->botMultiSelectType[$option->optionName] = new BotMultiSelectType($option->optionName);
        }

        return $this->botMultiSelectType[$option->optionName]->getData($newValue);
    }
}
