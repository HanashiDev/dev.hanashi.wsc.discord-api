<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Backport für WSC 5.2 und 5.3
 */
class HaPasswordFormField extends AbstractFormField implements
    IAutoFocusFormField,
    IImmutableFormField,
    IMaximumLengthFormField,
    IMinimumLengthFormField,
    IPlaceholderFormField
{
    use TAutoFocusFormField;
    use TDefaultIdFormField;
    use TImmutableFormField;
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;
    use TPlaceholderFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = '__haPasswordFormField';

    /**
     * @inheritDoc
     */
    protected function getValidInputModes(): array
    {
        return [
            'text',
        ];
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $value = $this->getValue();
        $hasValue = $this->getValue() !== null && $this->getValue() !== '';

        if ($this->isRequired() && !$hasValue) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        } elseif ($hasValue) {
            $this->validateMinimumLength($value);
            $this->validateMaximumLength($value);
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'password';
    }
}
