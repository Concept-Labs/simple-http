<?php

namespace Concept\SimpleHttp;

use Concept\Config\ConfigInterface;

class Bootstrap
{
    public static function init(string $configFile): App\AppInterface
    {
        $config = new \Concept\Config\Config($configFile);
        $config->load();

        if (!$config->has('app')) {
            throw new \RuntimeException('Application configuration not found.');
        }

        $config = $config->get('app');

        if (!is_array($config)) {
            throw new \RuntimeException('Invalid application configuration format.');
        }

        return self::createApp($config);
    }
    
}