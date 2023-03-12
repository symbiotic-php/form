<?php

declare(strict_types=1);

namespace Symbiotic\Form;


abstract class Validator implements ValidatorInterface
{
    protected array $data = [];

    protected ?string $error = null;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return null|string
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    public function __clone(): void
    {
        $this->error = null;
    }
}