<?php
namespace Concept\SimpleHttp\Layout\Component;

use Concept\Config\Contract\ConfigurableInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Singularity\Factory\ServiceFactory;

class ComponentFactory extends ServiceFactory implements ComponentFactoryInterface, ConfigurableInterface
{

    use ConfigurableTrait;

    public function create(array $args = []): ComponentInterface
    {
        //$this->getConfig()->hydrate($args);

        $componentType = $this->getConfig()->get('type');

        if (empty($componentType)) {
            throw new \RuntimeException('Component type is not defined');
        }

        if (!class_exists($componentType)) {
            throw new \RuntimeException(sprintf('Component class "%s" does not exist', $componentType));
        }

        if (!is_a($componentType, ComponentInterface::class, true)) {
            throw new \RuntimeException(sprintf('Component class "%s" must implement %s', $componentType, ComponentInterface::class));
        }

        $component = $this->createService($componentType, $args);
        

        $plugins = $this->getConfig()->get('plugins', []);

        foreach ($plugins as $name => $options) {

            if (null === $pluginServiceId = $options['preference'] ?? $options['class'] ?? null) {
                throw new \RuntimeException(sprintf('No valid plugin service ID found for plugin "%s"', $name));
            }

            $pluginService = $this->createService($pluginServiceId, $options['arguments'] ?? []);

            $component->getPluginManager()->register($name, $pluginService);
        }

        return $component;
    }
}