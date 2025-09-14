<?php

namespace Concept\SimpleHttp\Layout\Component\Plugin;

use Concept\SimpleHttp\Layout\Component\ComponentInterface;

class JsonPlugin implements PluginInterface
{
    public function __invoke(mixed $value, ?ComponentInterface $component = null): mixed
    {
        return json_encode($value, JSON_PRETTY_PRINT);
    }
}