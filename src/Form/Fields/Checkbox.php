<?php

declare(strict_types=1);

namespace Symbiotic\Form\Fields;

/**
 * Class Checkbox
 * @package Symbiotic\Form
 *
 * @method setValue(array $value) : static
 */
class Checkbox extends FieldSelectable
{
    /**
     * @var string
     */
    protected string $template = 'fields/checkbox';
}