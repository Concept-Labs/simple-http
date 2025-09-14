<?php

namespace Concept\SimpleHttp\Layout\Component\Plugin;

use Concept\SimpleHttp\Layout\Component\ComponentInterface;

class RawPlugin implements PluginInterface
{
    

    public function __invoke(mixed $value, ?ComponentInterface $component = null): mixed
    {
        return $value;
    }

}