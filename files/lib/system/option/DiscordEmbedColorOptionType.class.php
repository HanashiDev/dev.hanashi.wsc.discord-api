<?php

namespace wcf\system\option;

use Override;
use wcf\data\option\Option;
use wcf\system\discord\type\EmbedColorType;

class DiscordEmbedColorOptionType extends AbstractOptionType
{
    protected $embedColorType = [];

    #[Override]
    public function getFormElement(Option $option, $value)
    {
        if (!isset($this->embedColorType[$option->optionName])) {
            $this->embedColorType[$option->optionName] = new EmbedColorType($option->optionName);
        }

        return $this->embedColorType[$option->optionName]->getFormElement($value);
    }

    #[Override]
    public function validate(Option $option, $newValue)
    {
        if (!isset($this->embedColorType[$option->optionName])) {
            $this->embedColorType[$option->optionName] = new EmbedColorType($option->optionName);
        }
        $this->embedColorType[$option->optionName]->validate($newValue);
    }

    #[Override]
    public function getData(Option $option, $newValue)
    {
        if (!isset($this->embedColorType[$option->optionName])) {
            $this->embedColorType[$option->optionName] = new EmbedColorType($option->optionName);
        }

        return $this->embedColorType[$option->optionName]->getData($newValue);
    }
}
