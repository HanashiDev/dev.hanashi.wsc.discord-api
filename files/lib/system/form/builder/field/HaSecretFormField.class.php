<?php

namespace wcf\system\form\builder\field;

use wcf\data\IStorableObject;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Backport für WSC 5.3
 */
class HaSecretFormField extends AbstractFormField implements
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
    protected $templateName = '__haSecretFormField';

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
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true) {
        // Daten sollen nicht geladen werden, weil geheim
		return $this;
	}

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'haSecret';
    }
}
