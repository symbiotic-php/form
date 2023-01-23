<?php

declare(strict_types=1);

namespace Symbiotic\Form\Fields;


use Symbiotic\Form\FieldInterface;
use Symbiotic\View\ViewFactory;


class Html implements FieldInterface
{

    protected array $data = [
        'value' => ''
    ];



    /**
     * @var ViewFactory
     */
    protected ViewFactory $view;

    public function __construct(array $data = [])
    {
        $this->data = array_merge($this->data, $data);
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
       return $this->data['value'];
    }

    public function setView(ViewFactory $viewFactory): static
    {
        $this->view = $viewFactory;

        return $this;
    }


    public function __toString():string
    {
        return $this->render();
    }

    public function jsonSerialize()
    {
       return $this->data;
    }
}