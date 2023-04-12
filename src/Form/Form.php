<?php

declare(strict_types=1);

namespace Symbiotic\Form;

use Symbiotic\View\ViewFactory;


class Form implements FormInterface
{
    protected array $data = [
        'action' => '',
        'method' => 'post',
        'encode' => self::ENCTYPE_MULTIPART,
        'fields' => [],
        'attributes' => []
    ];

    protected string $template = 'form/form';

    protected ?ViewFactory $view = null;

    public function __construct(
        array $data,
        protected FormBuilder $formBuilder
    ) {
        if (!empty($data['fields']) && \is_array($data['fields'])) {
            $data['fields'] = $this->formBuilder->fromArray($data['fields']);
        }

        $this->data = \array_merge($this->data, $data);
    }


    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->data['action'];
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->data['method'];
    }

    /**
     * @return array
     *
     * @todo It will be necessary to make fields on the classes and give them a collection
     *
     */
    public function getFields(): array
    {
        return $this->data['fields'];
    }

    /**
     * @param string $name
     *
     * @return FillableInterface|null
     */
    public function getField(string $name): ?FillableInterface
    {
        foreach ($this->formBuilder->getFillable($this->getFields()) as $v) {
            if ($v->getDotName() === FormBuilder::getDotName($name)) {
                return $v;
            }
        }
        return null;
    }

    /**
     * @param string $method
     *
     * @return static
     * @throws FormException
     */
    public function setMethod(string $method): static
    {
        if (!\in_array(\strtolower($method), ['get', 'post'])) {
            throw new FormException(' Invalid method [' . $method . '], only get, post!');
        }

        $this->data['method'] = \strtolower($method);

        return $this;
    }

    /**
     * @param string $action
     *
     * @return static
     */
    public function setAction(string $action): static
    {
        $this->data['action'] = $action;

        return $this;
    }

    /**
     * @param string $type
     * @param array  $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function addField(string $type, array $data): FieldInterface
    {
        $field = $this->formBuilder->createField($type, $data);
        $this->data['fields'][] = $field;

        return $field;
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public function setValues(array $data): static
    {
        $this->formBuilder->setValues($this->data['fields'], $data);
        return $this;
    }


    /**
     * @param array $values
     *
     * @return FieldsValidator
     */
    public function getValidator(array $values = []): FieldsValidator
    {
        return new FieldsValidator($this->data['fields'], $values);
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

        return $this->view->make($template, ['form' => $this])->fetch();
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

}