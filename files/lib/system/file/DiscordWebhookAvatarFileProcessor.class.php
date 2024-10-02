<?php

namespace wcf\system\file;

use Override;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotEditor;
use wcf\data\file\File;
use wcf\system\cache\runtime\DiscordBotRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\file\processor\AbstractFileProcessor;
use wcf\system\file\processor\FileProcessorPreflightResult;
use wcf\system\WCF;
use wcf\util\FileUtil;

final class DiscordWebhookAvatarFileProcessor extends AbstractFileProcessor
{
    #[Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        if (isset($context['botID'])) {
            $bot = $this->getBot($context);
            if ($bot === null) {
                return FileProcessorPreflightResult::InvalidContext;
            }
        }

        if (!FileUtil::endsWithAllowedExtension($filename, $this->getAllowedFileExtensions($context))) {
            return FileProcessorPreflightResult::FileExtensionNotPermitted;
        }

        return FileProcessorPreflightResult::Passed;
    }

    #[Override]
    public function validateUpload(File $file): void
    {
        $imageData = @\getimagesize($file->getPathname());
        if ($imageData === false) {
            throw new UserInputException('file', 'noImage');
        }
        if ($imageData[0] < 128 || $imageData[1] < 128) {
            throw new UserInputException('file', 'tooSmall');
        }
    }

    #[Override]
    public function adopt(File $file, array $context): void
    {
        $bot = $this->getBot($context);

        if ($bot !== null) {
            (new DiscordBotEditor($bot))->update([
                'webhookIconID' => $file->fileID,
            ]);
        }
    }

    #[Override]
    public function canDelete(File $file): bool
    {
        return WCF::getSession()->getPermission('admin.discord.canManageConnection');
    }

    #[Override]
    public function canDownload(File $file): bool
    {
        return WCF::getSession()->getPermission('admin.discord.canManageConnection');
    }

    #[Override]
    public function delete(array $fileIDs, array $thumbnailIDs): void
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('webhookIconID IN (?)', [$fileIDs]);

        $sql = "UPDATE wcf1_discord_bot 
                SET    webhookIconID = NULL
                {$conditionBuilder}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
    }

    #[Override]
    public function getObjectTypeName(): string
    {
        return 'dev.hanashi.wsc.discord.webhook.avatar';
    }

    #[Override]
    public function getAllowedFileExtensions(array $context): array
    {
        return [
            'jpg',
            'jpeg',
            'png',
            'gif',
        ];
    }

    #[Override]
    public function getMaximumSize(array $context): ?int
    {
        return 8000000;
    }

    private function getBot(array $context): ?DiscordBot
    {
        $botID = $context['botID'] ?? null;
        if ($botID === null) {
            return null;
        }

        return DiscordBotRuntimeCache::getInstance()->getObject($botID);
    }
}
