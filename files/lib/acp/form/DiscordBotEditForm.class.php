<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use Override;
use wcf\data\discord\bot\DiscordBot;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\FileProcessorFormField;

class DiscordBotEditForm extends DiscordBotAddForm
{
    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[Override]
    public function readParameters()
    {
        parent::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
            $this->formObject = new DiscordBot($queryParameters['id']);

            if (!$this->formObject->botID) {
                throw new IllegalLinkException();
            }
        } catch (MappingError) {
            throw new IllegalLinkException();
        }
    }

    #[Override]
    protected function createForm()
    {
        parent::createForm();

        $webhookIconFormField = $this->form->getNodeById('webhookIconID');
        \assert($webhookIconFormField instanceof FileProcessorFormField);
        $webhookIconFormField->context([
            'botID' => $this->formObject->botID,
        ]);
    }
}
