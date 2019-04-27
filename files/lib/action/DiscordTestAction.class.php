<?php
namespace wcf\action;
use wcf\system\discord\DiscordApi;

// TODO: Klasse wieder löschen
class DiscordTestAction extends AbstractAction {
    public function execute() {
        parent::execute();

        // $discord = new DiscordApi(388240292400332802, 'Mzg4MjQwNDEwODg5Mjg5NzI4.XMP3zw._sA_XWOEqVXsygFvIOI6gdNB0ZI');
        // wcfDebug($discord->oauth2Authorize(388240410889289728, ['email', 'guilds.join'], 'https://testsrv.de/wsc31/index.php?discord-test2/'));
        $discord = new DiscordApi(388240292400332802, 'PEHHZAa7CI0p98Qtd0LaOHmDS0Cqst', 'Bearer');
        wcfDebug($discord->getCurrentUser());
    }
}
