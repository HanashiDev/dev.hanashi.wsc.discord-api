<?php

namespace wcf\system\option;

use Override;
use wcf\data\option\Option;
use wcf\system\discord\type\RoleMultiSelectDiscordType;

/**
 * Option-Type für die Auswahl mehrerer Discord-Rollen
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Option
 */
class DiscordRoleMultiSelectOptionType extends AbstractOptionType
{
    protected $roleMultiSelectType = [];

    #[Override]
    public function getFormElement(Option $option, $value)
    {
        if (!isset($this->roleMultiSelectType[$option->optionName])) {
            $this->roleMultiSelectType[$option->optionName] = new RoleMultiSelectDiscordType($option->optionName);
        }

        return $this->roleMultiSelectType[$option->optionName]->getFormElement($value);
    }

    #[Override]
    public function validate(Option $option, $newValue)
    {
        if (!isset($this->roleMultiSelectType[$option->optionName])) {
            $this->roleMultiSelectType[$option->optionName] = new RoleMultiSelectDiscordType($option->optionName);
        }
        $this->roleMultiSelectType[$option->optionName]->validate($newValue);
    }

    #[Override]
    public function getData(Option $option, $newValue)
    {
        if (!isset($this->roleMultiSelectType[$option->optionName])) {
            $this->roleMultiSelectType[$option->optionName] = new RoleMultiSelectDiscordType($option->optionName);
        }

        return $this->roleMultiSelectType[$option->optionName]->getData($newValue);
    }
}
