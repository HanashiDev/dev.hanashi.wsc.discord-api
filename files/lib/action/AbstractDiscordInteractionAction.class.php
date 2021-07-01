<?php

namespace wcf\action;

use wcf\system\discord\DiscordApi;
use wcf\system\exception\SystemException;
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
            $body = file_get_contents('php://input');
            if (empty($body)) {
                throw new \BadMethodCallException('body is empty');
            }
            $data = [];
            try {
                $data = JSON::decode($body, true);
            } catch (SystemException $e) {
                throw new \BadMethodCallException('body is not valid json');
            }

            $publicKey = $this->getPublicKey();
            if (empty($publicKey)) {
                throw new \UnexpectedValueException('public key is empty');
            }

            if (!DiscordApi::verifyRequest($publicKey, $body)) {
                throw new \OutOfBoundsException('invalid request signature');
            }

            if (empty($data['type'])) {
                throw new \BadMethodCallException('type is empty');
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
                default:
                    throw new \BadMethodCallException('unknown component');
                    break;
            }
        } catch (\BadMethodCallException $e) {
            @header('HTTP/1.1 400 Bad Request');
            echo $e->getMessage();
        } catch (\OutOfBoundsException $e) {
            @header('HTTP/1.1 401 Unauthorized');
            echo $e->getMessage();
        } catch (\UnexpectedValueException $e) {
            @header('HTTP/1.1 501 Not Implemented');
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
            'type' => DiscordApi::DISCORD_PONG
        ]);
    }
}
