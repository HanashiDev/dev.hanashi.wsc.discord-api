<?php

namespace wcf\data\discord\bot;

use Override;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\cache\builder\DiscordGuildChannelsCacheBuilder;
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
 *
 * @method  DiscordBotEditor[] getObjects()
 * @method  DiscordBotEditor   getSingleObject()
 */
final class DiscordBotAction extends AbstractDatabaseObjectAction
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
     * @inheritDoc
     */
    public $className = DiscordBotEditor::class;

    #[Override]
    public function create()
    {
        if (isset($this->parameters['data']['useApplicationCommands'])) {
            unset($this->parameters['data']['useApplicationCommands']);
        }

        return parent::create();
    }

    #[Override]
    public function update()
    {
        if (isset($this->parameters['data']['botToken']) && $this->parameters['data']['botToken'] === '') {
            unset($this->parameters['data']['botToken']);
        }
        if (isset($this->parameters['data']['clientSecret']) && $this->parameters['data']['clientSecret'] === '') {
            unset($this->parameters['data']['clientSecret']);
        }

        parent::update();
    }

    #[Override]
    protected function resetCache()
    {
        DiscordGuildChannelsCacheBuilder::getInstance()->reset();
    }

    /**
     * validiert die Methode getBotToken
     *
     * @throws PermissionDeniedException
     */
    public function validateGetBotToken()
    {
        if (\is_array($this->permissionsGetBotToken) && $this->permissionsGetBotToken !== []) {
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
        if (!isset($this->parameters['data']['botID'])) {
            throw new AJAXException('invalid bot id');
        }
        $botID = $this->parameters['data']['botID'];
        $discordBot = new DiscordBot($botID);
        if (!$discordBot->botID) {
            throw new AJAXException('invalid discord bot');
        }

        return [
            'token' => $discordBot->botToken,
        ];
    }
}
