<?php
namespace wcf\action;
use wcf\system\discord\DiscordApi;

// TODO: Klasse wieder löschen
class DiscordTestAction extends AbstractAction {
    public function execute() {
        parent::execute();

        $discord = new DiscordApi(388240292400332802, 388240410889289728, 'jXXAp5DAGQnpL5WP1dIL43jt72lZm8cf', 'Mzg4MjQwNDEwODg5Mjg5NzI4.XMP3zw._sA_XWOEqVXsygFvIOI6gdNB0ZI');
        wcfDebug($discord->getGuildEmoji(571616085472706570));
    }
}
