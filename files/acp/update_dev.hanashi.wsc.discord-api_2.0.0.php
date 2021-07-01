<?php

use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\WCF;
use wcf\util\StringUtil;

// Neue Spalte hinzufügen
$tables = [
    // wcf1_discord_bot
    DatabaseTable::create('wcf1_discord_bot')
        ->columns([
            VarcharDatabaseTableColumn::create('publicKey')
                ->length(100),
        ]),
];

(new DatabaseTableChangeProcessor(
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor()
))->process();

// Dateien von .pic zu .png umbenennen, damit sie im UploadFormField korrekt angezeigt werden
$dirPath = WCF_DIR . 'images/discord_webhook/';
if (file_exists($dirPath)) {
    $files = scandir($dirPath);
    foreach ($files as $file) {
        if (!StringUtil::endsWith($file, '.pic')) {
            continue;
        }

        rename($dirPath . $file, $dirPath . str_replace('.pic', '.png', $file));
    }
}
