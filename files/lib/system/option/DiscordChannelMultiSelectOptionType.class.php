<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
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
    /**
     * Liste von Discord-Bots
     * 
     * @var DiscordBotList
     */
    protected $discordBotList;

    /**
     * Liste von Server-Channeln
     * 
     * @var array
     */
    protected $guildChannels;

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value) {
        $channels = [];
        $guildChannels = $this->getGuildChannels();
        foreach ($this->getDiscordBotList() as $discordBot) {
            $channelsTmp = [];
            if (isset($guildChannels[$discordBot->botID])) {
                $channelsTmp = $guildChannels[$discordBot->botID];
            }
            $channelsTmp = $channelsTmp['body'];
            array_multisort(array_column($channelsTmp, 'position'), SORT_ASC, $channelsTmp);

            $channelsGroupedTmp = [];
            foreach ($channelsTmp as $channel) {
                if (empty($channel['parent_id'])) {
                    $childs = [];
                    if (isset($channelsGroupedTmp[$channel['id']]['childs'])) {
                        $childs = $channelsGroupedTmp[$channel['id']]['childs'];
                    }
                    $channel['childs'] = $childs;
                    $channelsGroupedTmp[$channel['id']] = $channel;
                } else {
                    $channelsGroupedTmp[$channel['parent_id']]['childs'][] = $channel;
                }
            }
            
            $channels[] = [
                'botID' => $discordBot->botID,
                'botName' => $discordBot->botName,
                'channels' => $channelsGroupedTmp
            ];
        }

        WCF::getTPL()->assign([
			'bots' => $channels,
			'option' => $option,
			'value' => unserialize($value)
		]);
        return WCF::getTPL()->fetch('discordChannelMultiSelectOptionType');
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue) {
        $guildChannels = $this->getGuildChannels();
        foreach ($newValue as $botID => $channelIDs) {
            if (empty($channelIDs)) continue;

            if (!isset($guildChannels[$botID])) {
                throw new UserInputException($option->optionName);
            }
            $channels = $guildChannels[$botID]['body'];
            $channelIDsTmp = array_column($channels, 'id');
            foreach ($channelIDs as $channelID) {
                if (!in_array($channelID, $channelIDsTmp)) {
                    throw new UserInputException($option->optionName);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = [];
		return serialize($newValue);
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
     * Gibt Liste von Discord-Channeln zurück
     * 
     * @return array
     */
    protected function getGuildChannels() {
        if ($this->guildChannels === null) {
            foreach ($this->getDiscordBotList() as $discordBot) {
                $discordApi = $discordBot->getDiscordApi();
                $this->guildChannels[$discordBot->botID] = $discordApi->getGuildChannels();
            }
        }
        return $this->guildChannels;
    }
}
