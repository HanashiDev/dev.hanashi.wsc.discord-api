<?php

namespace wcf\system\endpoint\controller\hanashi\discord\bot;

use Laminas\Diactoros\Response\JsonResponse;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

#[DeleteRequest('/hanashi/discord/bot/{id:\d+}')]
final class DeleteBot implements IController
{
    #[Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $bot = Helper::fetchObjectFromRequestParameter($variables['id'], DiscordBot::class);

        WCF::getSession()->checkPermissions(['admin.discord.canManageConnection']);

        $action = new DiscordBotAction([$bot], 'delete');
        $action->executeAction();

        return new JsonResponse([]);
    }
}
