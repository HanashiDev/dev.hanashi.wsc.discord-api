<?php

namespace wcf\action;

use BadMethodCallException;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use LogicException;
use OutOfBoundsException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UnexpectedValueException;
use wcf\data\discord\bot\DiscordBotList;
use wcf\data\discord\interaction\log\DiscordInteractionLogAction;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\SystemException;
use wcf\util\JSON;

abstract class AbstractDiscordInteractionAction implements RequestHandlerInterface, IDiscordInteractionAction
{
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            try {
                $body = (string)$request->getBody();

                if (ENABLE_DEBUG_MODE) {
                    $action = new DiscordInteractionLogAction([], 'create', [
                        'data' => [
                            'log' => $body,
                            'time' => TIME_NOW,
                        ],
                    ]);
                    $action->executeAction();
                }

                if ($body === '') {
                    throw new BadMethodCallException('body is empty');
                }
                $data = [];
                try {
                    $data = JSON::decode($body, true);
                } catch (SystemException) {
                    throw new BadMethodCallException('body is not valid json');
                }

                $publicKeys = $this->getPublicKeys();
                if ($publicKeys === []) {
                    throw new UnexpectedValueException('public key is empty');
                }

                $validRequest = false;
                foreach ($publicKeys as $publicKey) {
                    if (DiscordApi::verifyRequest($publicKey, $body)) {
                        $validRequest = true;
                        break;
                    }
                }
                if (!$validRequest) {
                    throw new OutOfBoundsException('invalid request signature');
                }

                if (!isset($data['type'])) {
                    throw new BadMethodCallException('type is empty');
                }

                switch ($data['type']) {
                    case DiscordApi::DISCORD_PING:
                        return $this->sendPong();
                        break;
                    case DiscordApi::DISCORD_APPLICATION_COMMAND:
                        return $this->handleApplicationCommand($data);
                        break;
                    case DiscordApi::DISCORD_MESSAGE_COMPONENT:
                        return $this->handleMessageCommand($data);
                        break;
                    case DiscordApi::DISCORD_APPLICATION_COMMAND_AUTOCOMPLETE:
                        return $this->handleApplicationCommandAutocomplete($data);
                        break;
                    case DiscordApi::DISCORD_MODAL_SUBMIT:
                        return $this->handleModalCommand($data);
                        break;
                    default:
                        throw new BadMethodCallException('unknown component');
                        break;
                }
            } catch (BadMethodCallException $e) {
                return new HtmlResponse($e->getMessage(), 400);
            } catch (OutOfBoundsException $e) {
                return new HtmlResponse($e->getMessage(), 401);
            } catch (UnexpectedValueException $e) {
                return new HtmlResponse($e->getMessage(), 501);
            }
        } else {
            throw new LogicException('Unreachable');
        }
    }

    /**
     * sendet pong zurück an Discord
     */
    private function sendPong(): JsonResponse
    {
        return new JsonResponse([
            'type' => DiscordApi::DISCORD_PONG,
        ]);
    }

    #[Override]
    public function getPublicKeys(): array
    {
        $publicKeys = [];

        $botList = new DiscordBotList();
        $botList->getConditionBuilder()->add('publicKey IS NOT NULL');
        $botList->getConditionBuilder()->add('publicKey <> ?', ['']);
        $botList->readObjects();

        foreach ($botList as $bot) {
            if (\in_array($bot->publicKey, $publicKeys)) {
                continue;
            }
            $publicKeys[] = $bot->publicKey;
        }

        return $publicKeys;
    }
}
