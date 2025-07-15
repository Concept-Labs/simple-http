<?php

namespace Concept\SimpleHttp\App;

class App implements AppInterface
{
    /**
     * The middleware stack
     * 
     * @var MiddlewareInterface[]
     */
    private array $middlewareStack = [];

    public function withMiddleware(MiddlewareInterface $middleware): static
    {
        $this->middlewareStack[] = $middleware;

        return $this;
    }


    /**
     * Run the application
     * 
     * @return void
     */
    public function run(): void
    {

        foreach ($this->middlewareStack as $middleware) {
            $middleware->handle();
        }
    }
}