<?php

namespace Concept\SimpleHttp\Layout\Component\Plugin;

use Concept\Http\App\Config\AppConfigInterface;
use Concept\SimpleHttp\Layout\Component\ComponentInterface;

class EscapePlugin implements PluginInterface
{
    public function __construct(private AppConfigInterface $appConfig)
    {
    }

    protected function getAppConfig(): AppConfigInterface
    {
        return $this->appConfig;
    }

    public function __invoke(mixed $value, ?ComponentInterface $component = null): mixed
    {
        return is_string($value) 
            ? htmlspecialchars($value, ENT_QUOTES, $this->getAppConfig()->get('charset') ?? 'UTF-8') 
            : $value;
    }

}