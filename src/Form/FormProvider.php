<?php

declare(strict_types=1);

namespace Symbiotic\Form;

use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\ServiceProvider;

class FormProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FormBuilder::class, function(CoreInterface $app) {
            return new FormBuilder($app);
        });
    }

}