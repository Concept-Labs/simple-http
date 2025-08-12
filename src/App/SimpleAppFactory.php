<?php
namespace Concept\SimpleHttp\App;

use Concept\Http\App\AppFactory;

class SimpleAppFactory extends AppFactory
{
    /**
     * Create app instance
     * Here we need to create an instance of SimpleHttpApp
     * to use simple-http package namespace to resolve services according container(singularity) dependency stack
     * 
     * @return static
     */
    protected function createAppInstance(array $args = []): static
    {
        $this->app = $this
            //->createService(AppInterface::class, $args)
            ->createService(SimpleHttpApp::class, $args)
            ->setConfig($this->getConfig());

        return $this;
    }
}
