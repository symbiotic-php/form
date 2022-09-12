<?php

declare(strict_types=1);

namespace Symbiotic\Form;

use Symbiotic\Core\Support\Collection;
use Symbiotic\Form\Fields\Group;


class FieldsValidator
{

    /**
     * @var array|FillableInterface[]
     */
    protected array $fields = [];

    /**
     * @var Collection|null
     */
    protected ?Collection $values = null;

    protected array $errors = [];

    /**
     * @param array $fields
     * @param array $values
     */
    public function __construct(array $fields, array $values = [])
    {
        $this->fields = $fields;
        $this->setValues($values);
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values): static
    {
        $this->values = new Collection($values);

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $fillable = [];
        foreach ($this->fields as $v) {
            if ($v instanceof FillableInterface) {
                $fillable[$v->getDotName()] = $v;
            } elseif ($v instanceof Group) {
                $fillable = array_merge($fillable, $v->getFieldsArray());
            }
        }
        foreach ($fillable as $field) {
            $value = $this->values->get($field->getDotName());
            if (!$field->validate($value)) {
                $this->errors[$field->getDotName()] = $field->getError();
            }
        }

        return empty($this->errors);
    }
}