<?php

namespace wcf\system\discord\type;

use wcf\data\discord\bot\DiscordBotList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

class RoleMultiSelectDiscordType extends AbstractDiscordType
{
    /**
     * Liste von Discord-Bots
     *
     * @var DiscordBotList
     */
    protected $discordBotList;

    /**
     * Liste von Server-Rollen
     *
     * @var array
     */
    protected $guildRoles;

    public function getFormElement($value)
    {
        $roles = [];
        $guildRoles = $this->getGuildRoles();
        foreach ($this->getDiscordBotList() as $discordBot) {
            $rolesTmp = [];
            if (isset($guildRoles[$discordBot->botID])) {
                $rolesTmp = $guildRoles[$discordBot->botID];
            }
            $rolesTmp = $rolesTmp['body'];
            if (!is_array($rolesTmp)) {
                $rolesTmp = [];
            }
            array_multisort(array_column($rolesTmp, 'position'), SORT_DESC, $rolesTmp);

            $roles[] = [
                'botID' => $discordBot->botID,
                'botName' => $discordBot->botName,
                'roles' => $rolesTmp
            ];
        }

        WCF::getTPL()->assign([
            'bots' => $roles,
            'optionName' => $this->optionName,
            'value' => unserialize($value)
        ]);

        return WCF::getTPL()->fetch('discordRoleMultiSelect');
    }

    public function validate($newValue)
    {
        $guildRoles = $this->getGuildRoles();
        foreach ($newValue as $botID => $roleIDs) {
            if (empty($roleIDs)) {
                continue;
            }

            if (!isset($guildRoles[$botID])) {
                throw new UserInputException($this->optionName);
            }
            $roles = $guildRoles[$botID]['body'];
            $roleIDsTmp = array_column($roles, 'id');
            foreach ($roleIDs as $roleID) {
                if (!in_array($roleID, $roleIDsTmp)) {
                    throw new UserInputException($this->optionName);
                }
            }
        }
    }

    public function getData($newValue)
    {
        if (!is_array($newValue)) {
            $newValue = [];
        }
        return serialize($newValue);
    }

    /**
     * gibt Liste von Discord-Bots zurück
     *
     * @return DiscordBotList
     */
    protected function getDiscordBotList()
    {
        if ($this->discordBotList === null) {
            $this->discordBotList = new DiscordBotList();
            $this->discordBotList->sqlOrderBy = 'botName ASC';
            $this->discordBotList->readObjects();
        }
        return $this->discordBotList;
    }

    /**
     * Gibt Liste von Discord-Rollen zurück
     *
     * @return array
     */
    protected function getGuildRoles()
    {
        if ($this->guildRoles === null) {
            foreach ($this->getDiscordBotList() as $discordBot) {
                $discordApi = $discordBot->getDiscordApi();
                $this->guildRoles[$discordBot->botID] = $discordApi->getGuildRoles();
            }
        }
        return $this->guildRoles;
    }
}
