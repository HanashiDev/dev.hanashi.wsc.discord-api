<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

class DiscordBotSelectOptionType extends AbstractOptionType {
    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value) {
        $discordBotList = new DiscordBotList();
        $discordBotList->sqlOrderBy = 'botName ASC';
        $discordBotList->readObjects();

        WCF::getTPL()->assign([
			'discordBotList' => $discordBotList,
			'option' => $option,
			'value' => $value
		]);
        return WCF::getTPL()->fetch('discordBotSelectOptionType');
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue) {
        if (!empty($newValue)) {
            $discordBot = new DiscordBot($newValue);
            if (!$discordBot->botID) {
                throw new UserInputException($option->optionName);
            }
        }
    }
}
