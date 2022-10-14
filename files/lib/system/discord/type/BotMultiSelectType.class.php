<?php

namespace wcf\system\discord\type;

use wcf\data\discord\bot\DiscordBotList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

class BotMultiSelectType extends AbstractDiscordType
{
    public function getFormElement($value)
    {
        $discordBotList = new DiscordBotList();
        $discordBotList->sqlOrderBy = 'botName ASC';
        $discordBotList->readObjects();

        WCF::getTPL()->assign([
            'discordBotList' => $discordBotList,
            'optionName' => $this->optionName,
            'value' => !\is_array($value) ? \explode("\n", $value) : $value,
        ]);

        return WCF::getTPL()->fetch('discordBotMultiSelectOptionType');
    }

    public function validate($newValue)
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }
        $newValue = ArrayUtil::toIntegerArray($newValue);

        $discordBotList = new DiscordBotList();
        $discordBotList->setObjectIDs($newValue);
        $discordBotList->readObjectIDs();

        foreach ($newValue as $value) {
            if (!\in_array($value, $discordBotList->objectIDs)) {
                throw new UserInputException($this->optionName);
            }
        }
    }

    public function getData($newValue)
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }

        return \implode("\n", ArrayUtil::toIntegerArray(StringUtil::unifyNewlines($newValue)));
    }
}
