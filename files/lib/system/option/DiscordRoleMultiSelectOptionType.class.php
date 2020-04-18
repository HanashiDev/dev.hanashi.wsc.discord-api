<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\discord\type\RoleMultiSelectDiscordType;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option-Type für die Auswahl mehrerer Discord-Rollen
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\System\Option
 */
class DiscordRoleMultiSelectOptionType extends AbstractOptionType {
    protected $roleMultiSelectType;

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value) {
        if ($this->roleMultiSelectType === null) {
            $this->roleMultiSelectType = new RoleMultiSelectDiscordType($option->optionName);
        }
        return $this->roleMultiSelectType->getFormElement($value);
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue) {
        if ($this->roleMultiSelectType === null) {
            $this->roleMultiSelectType = new RoleMultiSelectDiscordType($option->optionName);
        }
        $this->roleMultiSelectType->validate($newValue);
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue) {
		if ($this->roleMultiSelectType === null) {
            $this->roleMultiSelectType = new RoleMultiSelectDiscordType($option->optionName);
        }
        return $this->roleMultiSelectType->getData($newValue);
    }
}
