<?php
namespace wcf\system\discord\type;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

class RoleMultiSelectDiscordType extends AbstractDiscordType {
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

    public function getFormElement($value) {
        $roles = [];
        $guildRoles = $this->getGuildRoles();
        foreach ($this->getDiscordBotList() as $discordBot) {
            $rolesTmp = [];
            if (isset($guildRoles[$discordBot->botID])) {
                $rolesTmp = $guildRoles[$discordBot->botID];
            }
            // $rolesTmp = $rolesTmp['body'];
        //     array_multisort(array_column($channelsTmp, 'position'), SORT_ASC, $channelsTmp);

        //     $channelsGroupedTmp = [];
        //     foreach ($channelsTmp as $channel) {
        //         if (empty($channel['parent_id'])) {
        //             $childs = [];
        //             if (isset($channelsGroupedTmp[$channel['id']]['childs'])) {
        //                 $childs = $channelsGroupedTmp[$channel['id']]['childs'];
        //             }
        //             $channel['childs'] = $childs;
        //             $channelsGroupedTmp[$channel['id']] = $channel;
        //         } else {
        //             $channelsGroupedTmp[$channel['parent_id']]['childs'][] = $channel;
        //         }
        //     }
            
        //     $channels[] = [
        //         'botID' => $discordBot->botID,
        //         'botName' => $discordBot->botName,
        //         'channels' => $channelsGroupedTmp
        //     ];
        }

        WCF::getTPL()->assign([
			'bots' => $roles,
			'optionName' => $this->optionName,
			'value' => explode(',', $value)
		]);

        return WCF::getTPL()->fetch('discordRoleMultiSelect');
    }

    public function validate($newValue) {
        // $guildChannels = $this->getGuildChannels();
        // foreach ($newValue as $botID => $channelIDs) {
        //     if (empty($channelIDs)) continue;

        //     if (!isset($guildChannels[$botID])) {
        //         throw new UserInputException($this->optionName);
        //     }
        //     $channels = $guildChannels[$botID]['body'];
        //     $channelIDsTmp = array_column($channels, 'id');
        //     foreach ($channelIDs as $channelID) {
        //         if (!in_array($channelID, $channelIDsTmp)) {
        //             throw new UserInputException($this->optionName);
        //         }
        //     }
        // }
    }

    public function getData($newValue) {
        if (!is_array($newValue)) $newValue = [];
		return implode(',', $newValue);
    }

    /**
     * gibt Liste von Discord-Bots zurück
     * 
     * @return DiscordBotList
     */
    protected function getDiscordBotList() {
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
    protected function getGuildRoles() {
        if ($this->guildRoles === null) {
            foreach ($this->getDiscordBotList() as $discordBot) {
                $discordApi = $discordBot->getDiscordApi();
                $this->guildRoles[$discordBot->botID] = $discordApi->getGuildRoles();
            }
        }
        return $this->guildRoles;
    }
}
