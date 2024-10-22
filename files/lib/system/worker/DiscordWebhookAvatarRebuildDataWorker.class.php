<?php

namespace wcf\system\worker;

use Override;
use wcf\data\discord\bot\DiscordBotEditor;
use wcf\data\discord\bot\DiscordBotList;
use wcf\data\file\FileEditor;

final class DiscordWebhookAvatarRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = DiscordBotList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 100;

    #[Override]
    public function execute()
    {
        parent::execute();

        if (!\count($this->objectList)) {
            return;
        }

        foreach ($this->objectList as $bot) {
            $avatarFile = \sprintf('%simages/discord_webhook/%s.png', WCF_DIR, $bot->botID);
            if (!\file_exists($avatarFile)) {
                continue;
            }

            $editor = new DiscordBotEditor($bot);

            $file = FileEditor::createFromExistingFile(
                $avatarFile,
                $bot->botID . '.png',
                'dev.hanashi.wsc.discord.webhook.avatar'
            );

            if ($file === null) {
                continue;
            }

            $editor->update([
                'webhookIconID' => $file->fileID,
            ]);
        }
    }

    #[Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->getConditionBuilder()->add('webhookIconID IS NULL');
    }
}
