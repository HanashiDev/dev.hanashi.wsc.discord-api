<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

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
    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        $discordBotList = new DiscordBotList();
        $discordBotList->sqlOrderBy = 'botName ASC';
        $discordBotList->readObjects();

        WCF::getTPL()->assign([
            'discordBotList' => $discordBotList,
            'option' => $option,
            'value' => !is_array($value) ? explode("\n", $value) : $value
        ]);
        return WCF::getTPL()->fetch('discordBotMultiSelectOptionType');
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        if (!is_array($newValue)) {
            $newValue = [];
        }
        $newValue = ArrayUtil::toIntegerArray($newValue);

        $discordBotList = new DiscordBotList();
        $discordBotList->setObjectIDs($newValue);
        $discordBotList->readObjectIDs();

        foreach ($newValue as $value) {
            if (!in_array($value, $discordBotList->objectIDs)) {
                throw new UserInputException($option->optionName);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue)
    {
        if (!is_array($newValue)) {
            $newValue = [];
        }
        return implode("\n", ArrayUtil::toIntegerArray(StringUtil::unifyNewlines($newValue)));
    }
}
