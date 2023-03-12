<?php

declare(strict_types=1);

namespace Symbiotic\Form\Fields;

use Symbiotic\Form\FormBuilder;

trait FieldNameTrait
{
    /**
     * @param string $prefix key or key[subkey]
     *
     * @return $this
     */
    public function setPrefix(string $prefix): static
    {
        $this->data['prefix'] = $prefix;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->data['name'] = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->data['prefix'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    /**
     * Return prefix and name
     *
     * @return string
     */
    public function getFullName(): string
    {
        return empty($this->data['prefix']) ?
            $this->data['name'] :
            $this->data['prefix'] . '[' . $this->data['name'] . ']';
    }

    /**
     * Returns prefix and name with dot syntax
     *
     * @return string
     */
    public function getDotName(): string
    {
        return FormBuilder::getDotName($this->getFullName());
    }
}