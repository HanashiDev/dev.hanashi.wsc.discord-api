<?php

namespace wcf\system\endpoint\controller\hanashi\discord\webhook;

use Laminas\Diactoros\Response\JsonResponse;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\discord\webhook\DiscordWebhook;
use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

#[DeleteRequest('/hanashi/discord/webhook/{id:\d+}')]
final class DeleteWebhook implements IController
{
    #[Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $bot = Helper::fetchObjectFromRequestParameter($variables['id'], DiscordWebhook::class);

        WCF::getSession()->checkPermissions(['admin.discord.canManageWebhooks']);

        $action = new DiscordWebhookAction([$bot], 'delete');
        $action->executeAction();

        return new JsonResponse([]);
    }
}
