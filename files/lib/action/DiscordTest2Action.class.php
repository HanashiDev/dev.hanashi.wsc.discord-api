<?php
namespace wcf\action;
use wcf\system\discord\DiscordApi;

class DiscordTest2Action extends AbstractAction {
    public function execute() {
        parent::execute();

        $discord = new DiscordApi(388240292400332802, 'Mzg4MjQwNDEwODg5Mjg5NzI4.XMP3zw._sA_XWOEqVXsygFvIOI6gdNB0ZI');
        wcfDebug($discord->oauth2Token(388240410889289728, 'jXXAp5DAGQnpL5WP1dIL43jt72lZm8cf', $_GET['code'], 'https://testsrv.de/wsc31/index.php?discord-test2/'));
    }
}
