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
        if (!empty($data['fields']) && is_array($data['fields'])) {
            $data['fields'] = $this->formBuilder->fromArray($data['fields']);
        }

        $this->data = array_merge($this->data, $data);
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
     * @param string $method
     *
     * @return FormInterface
     * @throws FormException
     */
    public function setMethod(string $method): FormInterface
    {
        if (!in_array(strtolower($method), ['get', 'post'])) {
            throw new FormException(' Invalid method [' . $method . '], only get, post!');
        }

        $this->data['method'] = strtolower($method);

        return $this;
    }

    /**
     * @param string $action
     *
     * @return FormInterface
     */
    public function setAction(string $action): FormInterface
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
     * @return void
     */
    public function setValues(array $data): void
    {
        $this->formBuilder->setValues($this->data['fields'], $data);
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
     * @param array $data
     *
     * @return FieldsValidator
     */
    public function getValidator(array $data = []): FieldsValidator
    {
        return new FieldsValidator($this->data['fields'], $data);
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
            if (!str_contains($template, '::')) {
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