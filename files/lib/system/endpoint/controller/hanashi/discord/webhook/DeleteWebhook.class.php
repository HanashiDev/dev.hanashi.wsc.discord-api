<?php

namespace wcf\system\endpoint\controller\hanashi\discord\webhook;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\discord\webhook\DeleteDiscordWebhook;
use wcf\data\discord\webhook\DiscordWebhook;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

#[DeleteRequest('/hanashi/discord/webhook/{id:\d+}')]
final class DeleteWebhook implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $webhook = Helper::fetchObjectFromRequestParameter($variables['id'], DiscordWebhook::class);

        WCF::getSession()->checkPermissions(['admin.discord.canManageWebhooks']);

        (new DeleteDiscordWebhook($webhook))();

        return new JsonResponse([]);
    }
}
