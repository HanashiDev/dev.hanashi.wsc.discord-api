<?php

namespace wcf\system\option;

use Override;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option-Type für die Auswahl eines Discord-Bots
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Option
 */
class DiscordBotSelectOptionType extends AbstractOptionType
{
    #[Override]
    public function getFormElement(Option $option, $value)
    {
        $discordBotList = new DiscordBotList();
        $discordBotList->sqlOrderBy = 'botName ASC';
        $discordBotList->readObjects();

        WCF::getTPL()->assign([
            'discordBotList' => $discordBotList,
            'option' => $option,
            'value' => $value,
        ]);

        return WCF::getTPL()->fetch('discordBotSelectOptionType');
    }

    #[Override]
    public function validate(Option $option, $newValue)
    {
        if ($newValue !== 0 && $newValue !== '') {
            $discordBot = new DiscordBot($newValue);
            if (!$discordBot->botID) {
                throw new UserInputException($option->optionName);
            }
        }
    }
}
