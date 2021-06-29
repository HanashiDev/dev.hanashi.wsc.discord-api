<?php

namespace wcf\data\discord\bot;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\AJAXException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Discord-Bot-Objekt-Action
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Bot
 */
class DiscordBotAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.discord.canManageConnection'];

    /**
     * Rechte um den Token zu erhalten
     *
     * @var array
     */
    protected $permissionsGetBotToken = ['admin.discord.canManageConnection'];

    /**
     * validiert die Methode getBotToken
     *
     * @throws PermissionDeniedException
     */
    public function validateGetBotToken()
    {
        if (is_array($this->permissionsGetBotToken) && !empty($this->permissionsGetBotToken)) {
            WCF::getSession()->checkPermissions($this->permissionsGetBotToken);
        } else {
            throw new PermissionDeniedException();
        }
    }

    /**
     * gibt den Bot Token der Bot ID zurück
     *
     * @return array
     */
    public function getBotToken()
    {
        if (empty($this->parameters['data']['botID'])) {
            throw new AJAXException('invalid bot id');
        }
        $botID = $this->parameters['data']['botID'];
        $discordBot = new DiscordBot($botID);
        if (!$discordBot->botID) {
            throw new AJAXException('invalid discord bot');
        }
        return [
            'token' => $discordBot->botToken
        ];
    }
}
