<?php

declare(strict_types=1);

namespace Symbiotic\Form;

use Symbiotic\Form\Fields\{Boolean, Button, Checkbox, Html, Input, Radio, Select, Textarea, Group};
use Psr\Container\ContainerInterface;
use Symbiotic\Container\CloningContainer;
use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Core\Support\Collection;
use Symbiotic\View\ViewFactory;

/**
 * Class FormHelper
 * @package Symbiotic\Form
 *
 * To add fields, subscribe to an event in the
 * event('FieldTypesRepository::class','\My\HandlerObject') kernel
 */
class FormBuilder implements CloningContainer
{
    /**
     * @var array|null
     */
    protected static ?array $types = null;

    /**
     * @var string
     */
    protected static string $templatesPackageId = 'ui_form';

    /**
     * @param DIContainerInterface $container
     */
    public function __construct(protected DIContainerInterface $container)
    {
        // crutch for symbiotic field types. I did not add it to bootstrap,
        // the fields are needed only in the admin panel
        if (null === static::$types) {
            static::$types = [
                'input' => Input::class,
                'textarea' => Textarea::class,
                'select' => Select::class,
                'radio' => Radio::class,
                'checkbox' => Checkbox::class,
                'button' => Button::class,
                'bool' => Boolean::class,
                'group' => Group::class,
                'html' => Html::class
                /** and virtual input types {@see createField()} **/
            ];

            if (function_exists('_S\\event')) {
                \_S\event($this->container, $this);
            }
        }
    }


    /**
     * @param array       $data
     * @param string|null $formCLass
     *
     * @return FormInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Symbiotic\Container\BindingResolutionException
     * @throws \Symbiotic\Container\NotFoundException
     */
    public function createFromArray(array $data = [], string $formCLass = null): FormInterface
    {
        return $this->container->make($formCLass ?: Form::class, ['data' => $data])->setView(
            $this->container->get(ViewFactory::class)
        );
    }

    /**
     * @param string $package_id
     *
     * @return void
     */
    public static function setTemplatesPackageId(string $package_id): void
    {
        static::$templatesPackageId = $package_id;
    }

    public static function getTemplatesPackageId(): string
    {
        return static::$templatesPackageId;
    }

    /**
     * @param string $type  the field type must include the application prefix: filesystems::path
     * @param string $class className implements {@see FieldInterface, FillableInterface}
     */
    public function addType(string $type, string $class): void
    {
        static::$types[$type] = $class;
    }

    /**
     * @param array $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function text(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::TEXT, $data);
    }

    /**
     * @param string $type
     * @param array  $data = [
     *                     'name' => 'string',
     *                     'label' => 'string',
     *                     'value' => null,
     *                     'attributes' => [],
     *                     'validators' => [],
     *                     ]
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function createField(string $type, array $data): FieldInterface
    {
        $types = static::$types;

        if (isset($types[$type])) {
            $class = $types[$type];
        } elseif (
            in_array(
                $type,
                [
                    'text',
                    'hidden',
                    'file',
                    'number',
                    'submit',
                    'password',
                    'url',
                    'date',
                    'email'
                ]
            )) {
            $class = $types['input'];
            if (!isset($data['attributes'])) {
                $data['attributes'] = [];
            }
            $data['attributes']['type'] = $type;
        } else {
            throw  new FormException('Field type [' . $type . '] not found!');
        }
        try {
            return $this->container->make($class, ['data' => $data])->setView(
                $this->container->get(ViewFactory::class)
            );
        } catch (\Throwable $e) {
            throw new FormException('Field with type [' . $type . '] cannot be created!', $e->getCode(), $e);
        }
    }

    /**
     * @return array|FieldInterface[]
     * @throws FormException
     */
    public function fromArray(array $fields): array
    {
        foreach ($fields as &$v) {
            if (is_array($v)) {
                $v = $this->createField($v['type'], $v);
            }
        }
        unset($v);

        return $fields;
    }

    /**
     * @param array|FieldInterface[] $result
     *
     * @return array|FieldInterface[]
     */
    public function getCollapsedFieldsArray(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            if ($field instanceof GroupInterface) {
                // Do you need support for nested groups?
                $result = array_merge($result, $field->getFieldsArray());
            } else {
                $result[] = $field;
            }
        }
        return $result;
    }

    /**
     * @param array|FieldInterface $fields
     *
     * @return array|FillableInterface[]
     */
    public function getFillable(array $fields): array
    {
        return array_filter($this->getCollapsedFieldsArray($fields), function ($v) {
            return $v instanceof FillableInterface;
        });
    }


    public static function getDotName(string $name): string
    {
        return trim(\str_replace(['][', ']', '['], ['.', '.', '.'], $name), '.');
    }

    /**
     * @param array|FieldInterface[] $fields
     * @param array                  $values
     *
     * @return array
     */
    public function setValues(array $fields, array $values): array
    {
        $values = new Collection($values);
        foreach ($fields as $v) {
            if ($v instanceof FillableInterface) {
                $name = $v->getDotName();
                $v->setValue($values->get($name));
            } elseif ($v instanceof Group) {
                $v->setValues($values->toArray());
            }
        }

        return $fields;
    }

    /**
     * @param array $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function textarea(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::TEXTAREA, $data);
    }

    /**
     * @param array $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function select(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::SELECT, $data);
    }

    /**
     * @param array $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function radio(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::RADIO, $data);
    }

    /**
     * @param array $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function checkbox(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::CHECKBOX, $data);
    }

    /**
     * @param array $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function hidden(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::HIDDEN, $data);
    }

    /**
     * @param array $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function file(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::FILE, $data);
    }

    /**
     * @param string      $title
     * @param string|null $name
     * @param array       $fields
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function group(string $title, string $name = null, array $fields = []): FieldInterface
    {
        return $this->createField(FieldInterface::GROUP, compact('title', 'name', 'fields'));
    }

    /**
     * @param array $data
     *
     * @return FieldInterface
     * @throws FormException
     */
    public function submit(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::GROUP, $data);
    }

    /**
     * @param ContainerInterface|null $container
     *
     * @return object|null
     */
    public function cloneInstance(?ContainerInterface $container): ?object
    {
        /**
         * @var DIContainerInterface $container
         */
        $new = clone $this;
        $new->container = $container;
        return $new;
    }
}