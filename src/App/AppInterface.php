<?php

namespace Concept\SimpleHttp\App;

interface AppInterface
{
    public function withMiddleware(callable $middleware): static;
    public function run(): void;
}
