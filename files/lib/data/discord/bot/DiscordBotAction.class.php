<?php
namespace wcf\data\discord\bot;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\AJAXException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

class DiscordBotAction extends AbstractDatabaseObjectAction {
    protected $permissionsDelete = ['admin.discord.canManageConnection'];

    protected $permissionsGetBotToken = ['admin.discord.canManageConnection'];

    public function validateGetBotToken() {
        if (is_array($this->permissionsGetBotToken) && !empty($this->permissionsGetBotToken)) {
			WCF::getSession()->checkPermissions($this->permissionsGetBotToken);
		} else {
			throw new PermissionDeniedException();
		}
    }

    public function getBotToken() {
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
