<?php

declare(strict_types=1);

namespace Symbiotic\Form\Fields;

use Symbiotic\Form\FieldInterface;
use Symbiotic\Form\FormBuilder;
use Symbiotic\Form\FormException;
use Symbiotic\Form\GroupInterface;
use Symbiotic\View\ViewFactory;


class Group implements GroupInterface
{

    use FieldNameTrait;

    protected array $data = [
        'name' => '',
        'prefix' => '',
        'title' => '',
        'collapsed' => false,
        'fields' => [],
        'is_multi' => false,
        'sub_groups' => []
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
        $this->data = \array_merge($this->data, $data);
        $fields = $formBuilder->fromArray($this->data['fields']);
        $this->data['fields'] = [];
        foreach ($fields as $v) {
            $this->add($v);
        }
    }

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix(string $prefix): static
    {
        $this->data['prefix'] = $prefix;

        foreach ($this->data['fields'] as $v) {
            $v->setPrefix($this->getFullName());
        }

        if ($this->isMulti()) {
            $this->data['sub_groups'][0] = $this->createSubGroup('0', []);
        }

        return $this;
    }

    /**
     * @param FieldInterface $item
     *
     * @return static
     */
    public function add(FieldInterface $item): static
    {
        $this->data['fields'][] = $item->setPrefix($this->getFullName());

        return $this;
    }

    /**
     * @param iterable $data
     *
     * @return $this
     */
    public function setValue(array $data): static
    {
        if ($this->isMulti()) {
            foreach ($data as $index => $values) {
                $this->data['sub_groups'][$index] = $this->createSubGroup($index, $values);
            }
        } else {
            $this->data['fields'] = $this->formBuilder->setValues($this->data['fields'], $data);
        }

        return $this;
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
     * @return bool
     */
    public function isMulti(): bool
    {
        return (bool)$this->data['is_multi'];
    }

    /**
     * @return FieldInterface[]
     */
    public function getFields(): iterable
    {
        return $this->data['fields'];
    }

    /**
     * @return array|null
     */
    public function getSubGroups(): ?array
    {
        return $this->isMulti() ? $this->data['sub_groups'] : null;
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
     * @param string $index
     * @param array  $values
     *
     * @return GroupInterface
     * @throws FormException
     */
    protected function createSubGroup(string $index, array $values): GroupInterface
    {
        if (empty($this->data['name'])) {
            throw new FormException('For a multi-group namespace is required!');
        }
        $group = new Group(
            [
                'prefix' => $this->getFullName(),
                'name' => $index,
            ],
            $this->formBuilder
        );

        foreach ($this->getFields() as $v) {
            $field = clone $v;
            $group->add($field);
        }
        $group->setValue($values);

        return $group;
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
     * @return string
     * @throws \Symbiotic\Core\SymbioticException
     * @throws \Symbiotic\Packages\ResourceExceptionInterface
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    /**
     * @return void
     */
    public function __clone(): void
    {
        foreach ($this->data['fields'] as &$v) {
            $v = clone $v;
        }
        unset($v);
        // Todo: need clone?
        /*foreach ($this->data['sub_groups'] as &$v) {
            $v = clone $v;
        }
        unset($v);*/
    }
}