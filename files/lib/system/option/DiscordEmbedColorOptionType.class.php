<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\discord\type\EmbedColorType;
use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\WCF;

class DiscordEmbedColorOptionType extends AbstractOptionType
{
    protected $embedColorType = [];

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        if (!isset($this->embedColorType[$option->optionName])) {
            $this->embedColorType[$option->optionName] = new EmbedColorType($option->optionName);
        }

        return $this->embedColorType[$option->optionName]->getFormElement($value);
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        if (!isset($this->embedColorType[$option->optionName])) {
            $this->embedColorType[$option->optionName] = new EmbedColorType($option->optionName);
        }
        $this->embedColorType[$option->optionName]->validate($newValue);
    }

    public function getData(Option $option, $newValue)
    {
        if (!isset($this->embedColorType[$option->optionName])) {
            $this->embedColorType[$option->optionName] = new EmbedColorType($option->optionName);
        }

        return $this->embedColorType[$option->optionName]->getData($newValue);
    }
}
