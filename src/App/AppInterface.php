<?php

namespace Concept\SimpleHttp\App;

use Psr\Http\Server\MiddlewareInterface;

interface AppInterface
{
    public function withMiddleware(MiddlewareInterface $middleware): static;
    public function run(): void;
}
