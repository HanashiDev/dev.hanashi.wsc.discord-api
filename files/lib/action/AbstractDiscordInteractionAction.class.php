<?php

namespace wcf\action;

use BadMethodCallException;
use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use OutOfBoundsException;
use UnexpectedValueException;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\discord\DiscordApi;
use wcf\util\JSON;

abstract class AbstractDiscordInteractionAction extends AbstractAction implements IDiscordInteractionAction
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        try {
            $serverRequest = ServerRequest::fromGlobals();
            $body = (string)$serverRequest->getBody();
            if (empty($body)) {
                throw new BadMethodCallException('body is empty');
            }
            $data = [];
            try {
                $data = JSON::decode($body, true);
            } catch (Exception $e) {
                throw new BadMethodCallException('body is not valid json');
            }

            $publicKeys = $this->getPublicKeys();
            if (empty($publicKeys)) {
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

            if (empty($data['type'])) {
                throw new BadMethodCallException('type is empty');
            }

            switch ($data['type']) {
                case DiscordApi::DISCORD_PING:
                    $this->sendPong();
                    break;
                case DiscordApi::DISCORD_APPLICATION_COMMAND:
                    $this->handleApplicationCommand($data);
                    break;
                case DiscordApi::DISCORD_MESSAGE_COMPONENT:
                    $this->handleMessageCommand($data);
                    break;
                case DiscordApi::DISCORD_APPLICATION_COMMAND_AUTOCOMPLETE:
                    $this->handleApplicationCommandAutocomplete($data);
                    break;
                case DiscordApi::DISCORD_MODAL_SUBMIT:
                    $this->handleModalCommand($data);
                    break;
                default:
                    throw new BadMethodCallException('unknown component');
                    break;
            }
        } catch (BadMethodCallException $e) {
            @\header('HTTP/1.1 400 Bad Request');
            echo $e->getMessage();
        } catch (OutOfBoundsException $e) {
            @\header('HTTP/1.1 401 Unauthorized');
            echo $e->getMessage();
        } catch (UnexpectedValueException $e) {
            @\header('HTTP/1.1 501 Not Implemented');
            echo $e->getMessage();
        }

        $this->executed();
    }

    /**
     * sendet pong zurück an Discord
     *
     * @return void
     */
    private function sendPong()
    {
        echo JSON::encode([
            'type' => DiscordApi::DISCORD_PONG,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getPublicKeys(): array
    {
        $publicKeys = [];

        $botList = new DiscordBotList();
        $botList->getConditionBuilder()->add('publicKey IS NOT NULL');
        $botList->getConditionBuilder()->add('publicKey != ?', ['']);
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
