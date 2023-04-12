<?php

declare(strict_types=1);

namespace Symbiotic\Form\Fields;

use Symbiotic\Form\FieldSelectableInterface;


abstract class FieldSelectable extends FieldAbstract implements FieldSelectableInterface
{
    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data['variants'] = [];
        parent::__construct($data);
    }

    /**
     * @param array $variants
     *
     * @return $this
     */
    public function variants(array $variants): static
    {
        $this->data['variants'] = $variants;

        return $this;
    }

    /**
     * @return array
     */
    public function getVariants(): array
    {
        return $this->data['variants'];
    }
}