<?php

namespace wcf\system\discord\field;

use InvalidArgumentException;
use Override;
use UnexpectedValueException;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\exception\InvalidFormFieldValue;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IAttributeFormField;
use wcf\system\form\builder\field\ICssClassFormField;
use wcf\system\form\builder\field\IFilterableSelectionFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\TCssClassFormField;
use wcf\system\form\builder\field\TFilterableSelectionFormField;
use wcf\system\form\builder\field\TImmutableFormField;
use wcf\system\form\builder\field\TInputAttributeFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;

final class DiscordChannelMultiSelectFormField extends AbstractFormField implements
    IAttributeFormField,
    ICssClassFormField,
    IFilterableSelectionFormField,
    IImmutableFormField
{
    use TInputAttributeFormField;
    use TCssClassFormField;
    use TFilterableSelectionFormField;
    use TImmutableFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_discordChannelMultiSelectFormField';

    /**
     * @inheritDoc
     */
    protected $value = [];

    private $allowedTypes = [0, 5];

    #[Override]
    public function hasSaveValue()
    {
        return false;
    }

    #[Override]
    public function getOptions()
    {
        $options = $this->options;

        if ($this->getAllowedTypes() !== []) {
            $allowedTypes = $this->getAllowedTypes();
            $options = \array_filter($options, static function ($option) use ($allowedTypes) {
                return \in_array($option['type'], $allowedTypes) || $option['type'] == 4;
            });
        }

        return $options;
    }

    #[Override]
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'discordWebhookMulti',
                function (IFormDocument $document, array $parameters) {
                    if (!empty($this->getValue())) {
                        $parameters[$this->getObjectProperty()] = $this->getValue();
                    }

                    return $parameters;
                }
            )
        );

        return $this;
    }

    #[Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_array($value)) {
                $this->value = $value;
            }
        }

        return $this;
    }

    #[Override]
    public function validate()
    {
        $value = $this->getValue();

        if (($value === null || empty($value)) && $this->isRequired()) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        } elseif ($value !== null && !empty(\array_diff($this->getValue(), \array_column($this->getOptions(), 'id')))) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.global.form.error.noValidSelection'
            ));
        }

        parent::validate();
    }

    #[Override]
    public function value($value)
    {
        // ignore `null` as value which can be passed either for nullable
        // fields or as value if no options are available
        if ($value === null) {
            return $this;
        }

        if (!\is_array($value)) {
            throw new InvalidFormFieldValue($this, 'array', \gettype($value));
        }

        return parent::value($value);
    }

    #[Override]
    public function supportsNestedOptions()
    {
        return false;
    }

    #[Override]
    public function options($options, $nestedOptions = false, $labelLanguageItems = true)
    {
        if (!\is_array($options)) {
            throw new UnexpectedValueException('options must be an array');
        }

        foreach ($options as $option) {
            foreach (['id', 'type', 'name', 'parent_id', 'position'] as $entry) {
                if (!isset($entry, $option)) {
                    throw new InvalidArgumentException("Option has no {$entry} entry for field '{$this->getId()}'.");
                }
            }
        }

        // Kanäle ohne Kategorien
        $this->options = $this->sortOptions(\array_filter($options, static function ($option) {
            return !isset($option['parent_id']) && $option['type'] != 4;
        }));

        $categoryOptions = $this->sortOptions(\array_filter($options, static function ($option) {
            return $option['type'] == 4;
        }));
        foreach ($categoryOptions as $categoryOption) {
            $childOptions = $this->sortOptions(\array_filter($options, static function ($option) use ($categoryOption) {
                return $option['parent_id'] == $categoryOption['id'];
            }));
            $this->options = \array_merge($this->options, [$categoryOption], $childOptions);
        }

        return $this;
    }

    private function sortOptions(array $options): array
    {
        \usort(
            $options,
            static function ($a, $b) {
                // Kanäle die keine Voice-Kanäle sind, sollen über Voice-Kanälen stehen
                if (!\in_array($a['type'], [2, 13]) && \in_array($b['type'], [2, 13])) {
                    return -1;
                }
                if (\in_array($a['type'], [2, 13]) && !\in_array($b['type'], [2, 13])) {
                    return 1;
                }

                // Sortierung nach Position
                if ($a['position'] < $b['position']) {
                    return -1;
                }
                if ($a['position'] > $b['position']) {
                    return 1;
                }
            }
        );

        return $options;
    }

    public function allowedTypes(array $allowedTypes = [])
    {
        $this->allowedTypes = $allowedTypes;

        return $this;
    }

    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }
}
