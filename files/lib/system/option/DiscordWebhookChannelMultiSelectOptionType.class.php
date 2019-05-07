<?php
namespace wcf\system\option;
use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\data\option\Option;
use wcf\system\discord\type\WebhookChannelMultiSelectDiscordType;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Option-Type für die Auswahl mehrerer Discord-Channel mit Webhook-Bezug
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\System\Option
 */
class DiscordWebhookChannelMultiSelectOptionType extends DiscordChannelMultiSelectOptionType {
    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue) {
        $channelMultiSelectType = new WebhookChannelMultiSelectDiscordType($option->optionName);
        $channelMultiSelectType->validate($newValue);
    }
}
