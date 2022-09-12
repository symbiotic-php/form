<?php

declare(strict_types=1);

namespace Symbiotic\Form\Fields;


class Input extends FieldAbstract
{

    protected string $template = 'fields/input';

    /**
     * @param array $data field properties
     */
    public function __construct(array $data)
    {
        $this->data['attributes']['type'] = 'text';
        parent::__construct($data);
    }
}