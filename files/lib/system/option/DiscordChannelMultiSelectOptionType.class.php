<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\discord\type\ChannelMultiSelectDiscordType;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option-Type für die Auswahl mehrerer Discord-Channel
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\System\Option
 */
class DiscordChannelMultiSelectOptionType extends AbstractOptionType {
    protected $channelMultiSelectType;

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value) {
        if ($this->channelMultiSelectType === null) {
            $this->channelMultiSelectType = new ChannelMultiSelectDiscordType($option->optionName);
        }
        return $this->channelMultiSelectType->getFormElement($value);
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue) {
        if ($this->channelMultiSelectType === null) {
            $this->channelMultiSelectType = new ChannelMultiSelectDiscordType($option->optionName);
        }
        $this->channelMultiSelectType->validate($newValue);
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue) {
		if ($this->channelMultiSelectType === null) {
            $this->channelMultiSelectType = new ChannelMultiSelectDiscordType($option->optionName);
        }
        return $this->channelMultiSelectType->getData($newValue);
    }
}
