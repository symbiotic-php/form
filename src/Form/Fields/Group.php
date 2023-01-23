<?php

declare(strict_types=1);

namespace Symbiotic\Form\Fields;

use Symbiotic\Form\FieldInterface;
use Symbiotic\Form\FormBuilder;
use Symbiotic\Form\FormException;
use Symbiotic\Form\GroupInterface;
use Symbiotic\View\ViewFactory;

/**
 * Class FieldsCollection
 * @package Symbiotic\Form
 */
class Group implements GroupInterface
{

    protected array $data = [
        'name' => '',
        'title' => '',
        'collapsed' => false,
        'fields' => [],
    ];

    protected string $template = 'fields/group';

    /**
     * @var ViewFactory
     */
    protected ViewFactory $view;

    /**
     * @param array       $data
     * @param FormBuilder $formBuilder
     *
     * @throws FormException
     */
    public function __construct(array $data, protected FormBuilder $formBuilder)
    {
        $this->data = array_merge($this->data, $data);
        $this->data['fields'] = $formBuilder->fromArray($this->data['fields']);
    }

    /**
     * @param FieldInterface $item
     *
     * @return static
     */
    public function add(FieldInterface $item): static
    {
        $this->data['fields'][] = $item;
        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setValues(array $data): static
    {
        $this->data['fields'] = $this->formBuilder->setValues($this->data['fields'], $data);
        return $this;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->data['title'];
    }

    /**
     * @return bool
     */
    public function isCollapsed(): bool
    {
        return (bool)$this->data['collapsed'];
    }


    /**
     * @return FieldInterface[]
     */
    public function getFields(): array
    {
        return $this->data['fields'];
    }

    /**
     * @return FieldInterface[]
     */
    public function getFieldsArray(): array
    {
        $fields = [];
        foreach ($this->data['fields'] as $field) {
            if ($field instanceof GroupInterface) {
                // Do you need support for nested groups?
                $fields = array_merge($fields, $field->getFields());
            } else {
                $fields[] = $field;
            }
        }
        return $fields;
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

        return $this->view->make($template, ['group' => $this])->fetch();
    }

    public function setView(ViewFactory $viewFactory): static
    {
        $this->view = $viewFactory;

        return $this;
    }

    /**
     * @return string
     * @throws \Symbiotic\Core\SymbioticException
     * @throws \Symbiotic\Packages\ResourceExceptionInterface
     */
    public function __toString():string
    {
        return $this->render();
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}