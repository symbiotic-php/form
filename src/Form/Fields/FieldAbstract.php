<?php

declare(strict_types=1);

namespace Symbiotic\Form\Fields;

use Symbiotic\Form\FillableInterface;
use Symbiotic\Form\FormBuilder;
use Symbiotic\Form\ValidatorInterface;
use Symbiotic\View\ViewFactory;


class FieldAbstract implements FillableInterface
{
    use FieldNameTrait;

    protected string $template = '';

    protected array $data = [
        'label' => '',
        'description' => '',
        'name' => '',
        'prefix' => '',
        'meta' => [],
        'value' => null,
        'default' => null,
        'placeholder' => '',
        'attributes' => [],
        'validators' => [],
        'error' => null,
    ];

    protected ?ViewFactory $view = null;


    public function __construct(array $data = [])
    {
        $this->data = \array_merge($this->data, $data);
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel(string $label = ''): static
    {
        $this->data['label'] = $label;
        return $this;
    }

    /**
     * @param array|string|null $value
     *
     * @return $this
     */
    public function setValue(string|array|null $value): static
    {
        $this->data['value'] = $value;
        return $this;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription(string $description): static
    {
        $this->data['description'] = $description;
        return $this;
    }

    /**
     * @param array|string|int $default
     *
     * @return $this
     */
    public function setDefault(string|array|int $default): static
    {
        $this->data['default'] = $default;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->data['attributes'][$key] = $value;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->data['label'];
    }

    /**
     * @return array|string|null
     */
    public function getValue(): string|array|null
    {
        return $this->data['value'];
    }

    /**
     * @return array|string|null
     */
    public function getDefault(): string|array|null
    {
        return \is_null($this->data['default']) ? null : (string)$this->data['default'];
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->data['description'];
    }

    /**
     * @return string
     */
    public function getAttributesHtml(): string
    {
        $attributes = [];
        foreach ($this->getAttributes() as $name => $value) {
            // TODO: test and fix js attributes with code
            $attributes[] = \htmlspecialchars($name) . '="' . \htmlspecialchars($value) . '"';
        }
        return \implode(' ', $attributes);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->data['attributes'];
    }

    /**
     * @return array
     */
    public function getValidators(): array
    {
        return $this->data['validators'];
    }

    /**
     * @param ValidatorInterface $validator
     *
     * @return $this
     */
    public function addValidator(ValidatorInterface $validator): static
    {
        $this->data['validators'][] = $validator;
        return $this;
    }

    /**
     * @param string|array|null $value
     *
     * @return bool
     */
    public function validate(string|array|null $value): bool
    {
        $result = true;
        /**
         * @var ValidatorInterface $validator
         */
        foreach ($this->data['validators'] as $validator) {
            // todo: clone validator?
            if (!$validator->validate($value)) {
                $this->data['error'] .= $validator->getError() . ',';
                $result = false;
                // todo: array errors...
            }
        }
        return $result;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->data['error'];
    }

    /**
     * @param string $val
     *
     * @return $this
     */
    public function setPlaceholder(string $val): static
    {
        $this->data['placeholder'] = $val;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->data['placeholder'];
    }

    /**
     * @param string|null $template
     *
     * @return string
     * @throws \Symbiotic\Core\SymbioticException
     * @throws \Symbiotic\Packages\ResourceExceptionInterface
     */
    public function render(string $template = null): string
    {
        if (!$template) {
            $template = $this->template;
            if (!\str_contains($template, '::')) {
                $template = FormBuilder::getTemplatesPackageId() . '::' . $template;
            }
        }

        return $this->view->make($template, ['field' => $this])->fetch();
    }

    /**
     * @param ViewFactory $viewFactory
     *
     * @return $this
     */
    public function setView(ViewFactory $viewFactory): static
    {
        $this->view = $viewFactory;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return \array_merge($this->data, ['template' => $this->template]);
    }

    /**
     * @return string
     * @throws \Symbiotic\Packages\ResourceExceptionInterface
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @return void
     */
    public function __clone(): void
    {
        $this->data['value'] = null;
        $this->data['error'] = null;
        foreach ($this->data['validators'] as &$v) {
            $v = clone $v;
        }
        unset($v);
    }
}