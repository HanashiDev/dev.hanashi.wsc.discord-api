<?php

use wcf\util\StringUtil;

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
