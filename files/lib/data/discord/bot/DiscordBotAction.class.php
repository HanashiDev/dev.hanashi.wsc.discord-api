<?php

namespace wcf\data\discord\bot;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\AJAXException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\file\upload\UploadFile;
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
    public function create()
    {
        if (isset($this->parameters['data']['useApplicationCommands'])) {
            unset($this->parameters['data']['useApplicationCommands']);
        }

        $discordBot = parent::create();

        if (isset($this->parameters['webhookIcon']) && \is_array($this->parameters['webhookIcon'])) {
            $this->processWebhookIcon($discordBot->botID);
        }

        return $discordBot;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        if (isset($this->parameters['data']['botToken']) && $this->parameters['data']['botToken'] === '') {
            unset($this->parameters['data']['botToken']);
        }
        if (isset($this->parameters['data']['clientSecret']) && $this->parameters['data']['clientSecret'] === '') {
            unset($this->parameters['data']['clientSecret']);
        }

        parent::update();

        foreach ($this->getObjects() as $object) {
            if (isset($this->parameters['webhookIcon']) && \is_array($this->parameters['webhookIcon'])) {
                if ($this->parameters['webhookIcon'] === '') {
                    $filename = \sprintf('%simages/discord_webhook/%s.png', WCF_DIR, $object->botID);
                    if (\file_exists($filename)) {
                        \unlink($filename);
                    }
                } else {
                    $this->processWebhookIcon($object->botID);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $returnValues = parent::delete();

        foreach ($this->getObjects() as $object) {
            $filename = \sprintf('%simages/discord_webhook/%s.png', WCF_DIR, $object->botID);
            if (\file_exists($filename)) {
                \unlink($filename);
            }
        }

        return $returnValues;
    }

    /**
     * verarbeitet hochgeladenes Icon
     *
     * @param  mixed $botID
     * @return void
     */
    protected function processWebhookIcon(int $botID)
    {
        $iconFile = \reset($this->parameters['webhookIcon']);
        if ($iconFile instanceof UploadFile && !$iconFile->isProcessed()) {
            $filename = \sprintf('%simages/discord_webhook/%s.png', WCF_DIR, $botID);

            \rename($iconFile->getLocation(), $filename);
            $iconFile->setProcessed($filename);
        }
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
