<?php
namespace Concept\SimpleHttp\Layout\Component\Plugin;

use Concept\SimpleHttp\Layout\Component\ComponentInterface;

interface PluginInterface
{
    public function __invoke(string $string, ?ComponentInterface $component = null): mixed;
}