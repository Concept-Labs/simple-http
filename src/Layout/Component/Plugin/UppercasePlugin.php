<?php

namespace Concept\SimpleHttp\Layout\Component\Plugin;

use Concept\SimpleHttp\Layout\Component\ComponentInterface;

class UppercasePlugin implements PluginInterface
{
    public function __invoke(string $string, ?ComponentInterface $component = null): mixed
    {
        return strtoupper($string);
    }

}