<?php

namespace Concept\SimpleHttp\Layout;


use Concept\Config\Config;
use Concept\Config\Contract\ConfigurableInterface;
use Concept\Http\App\Config\AppConfigInterface;
use Concept\SimpleHttp\Layout\Component\ComponentFactoryInterface;
use Concept\SimpleHttp\Layout\Component\ComponentInterface;

class LayoutBuilder implements LayoutBuilderInterface
{
    

    public function __construct(private ComponentFactoryInterface $componentFactory)
    {}

    /**
     * {@inheritdoc}
     */
    public function build(array $config): ComponentInterface
    {
        $this->normalizeConfig($config);

        $config = [LayoutBuilderInterface::CONFIG_NODE_ROOT => $config];

        $this->propagateRootConfig(
            $config, 
            $config
                [LayoutBuilderInterface::CONFIG_NODE_ROOT]
                    [LayoutBuilderInterface::CONFIG_NODE_CONFIG] ?? []
        );

        $rootComponent = $this->buildComponents($config);

        return $rootComponent;
    }

    /**
     * Normalize the config array to convert dot/slash notation into nested arrays
     * example: 
     *  ['.foo.bar' => [...]] 
     *  becomes 
     *  ['foo' => ['__children' => ['bar' => ['___config' => ...]]]]
     *
     * @param array $config
     * @return void
     */
    protected function normalizeConfig(array &$config, array $parentConfig = []): void
    {
        foreach ($config as $nodeName => $nodeConfigData) {
            /**
             * Only process nodes that have children defined using dot or slash notation
             */
            if (strchr($nodeName, '.') || strchr($nodeName, '/') || strchr($nodeName, '\\')) {
                
                /**
                 * Sugar for config definitions using dot or slash notation
                 */
                $nodeName = str_replace(['/', '\\'], '.', $nodeName);
                /**
                 * Create nested structure
                 */
                $parts = explode('.', $nodeName);
                $ref = &$config;

                foreach ($parts as $part) {

                    $part = trim($part);

                    /**
                     * Skip empty parts (root or double dots)
                     */
                    if (empty($part)) {
                        //$part = 'root';
                        continue;
                    }
                    
                    /**
                     * Create the child node if it doesn't exist
                     */
                    $ref[LayoutBuilderInterface::CONFIG_NODE_CHILDREN][$part] ??= [];
                    

                    /**
                     * Descend into the child node
                     */
                    $ref = &$ref[LayoutBuilderInterface::CONFIG_NODE_CHILDREN][$part];
                }
                $ref[LayoutBuilderInterface::CONFIG_NODE_CONFIG] = $nodeConfigData;
                
                /**
                 * Remove the original entry
                 */
                unset($config[$nodeName]);
            }
        }
    }

    /**
     * Propagate configuration from parent to children recursively
     */
    // protected function propagateConfig(array &$config)
    // {
    //     foreach ($config as $id => $componentData) {
    //         $componentConfig = $componentData['___config'] ?? [];

    //         if (isset($componentData['___children'])) {
    //             foreach ($componentData['___children'] as $childId => $childData) {
    //                 $childConfig = $childData['___config'] ?? [];
                


    //                 $childConfig = array_replace_recursive($componentConfig, $childConfig);
    //                 $config[$id]['___children'][$childId]['___config'] = $childConfig;

    //                 if (isset($childData['___children'])) {
    //                     $this->propagateConfig($config[$id]['___children']);
    //                 }
    //             }
    //         }
    //     }
    // }

    protected function propagateRootConfig(array &$config, array $rootConfig)
    {
        foreach ($config as $id => $componentData) {
            $componentConfig = $componentData[LayoutBuilderInterface::CONFIG_NODE_CONFIG] ?? [];

            $componentConfig = array_replace_recursive($rootConfig, $componentConfig);
            $config[$id][LayoutBuilderInterface::CONFIG_NODE_CONFIG] = $componentConfig;

            if (isset($componentData[LayoutBuilderInterface::CONFIG_NODE_CHILDREN])) {
                $this->propagateRootConfig(
                    $config[$id][LayoutBuilderInterface::CONFIG_NODE_CHILDREN], 
                    $rootConfig
                );
            }
        }
    }

    /**
     * Recursively build components from the config array
     *
     * @param array $config
     * @param ComponentInterface|null $parent
     * 
     * @return ComponentInterface
     */
    protected function buildComponents(array $config, ?ComponentInterface $parent = null): ComponentInterface
    {
        foreach ($config as $id => $componentData) {

            $componentConfig = $componentData[LayoutBuilderInterface::CONFIG_NODE_CONFIG] ?? [];
            
            /**
             * Create the component
            */
            $cConfig = Config::fromArray($componentConfig);
            $component = $this
                ->getComponentFactory()->setConfig($cConfig)
                    ->create();

            $component->id($id);

            /**
             * Set config for component
             */
            $component instanceof ConfigurableInterface && $component->setConfig($cConfig);

            /**
             * Hydrate context for component
             */
            $component->getContext()->hydrate(
                $cConfig->get(LayoutBuilderInterface::CONFIG_NODE_CONTEXT) ?? []
            );

            /**
             * Add the component to its parent
             */
            $parent?->addChild($id, $component);

            /**
             * Recursively build child components
             */
            if (isset($componentData[LayoutBuilderInterface::CONFIG_NODE_CHILDREN])) {
                $this->buildComponents(
                    $componentData[LayoutBuilderInterface::CONFIG_NODE_CHILDREN], 
                    $component
                );
            }
        }

        return $component;
    }

    /**
     * Get the component factory
     *
     * @return ComponentFactoryInterface
     */
    protected function getComponentFactory(): ComponentFactoryInterface
    {
        return $this->componentFactory;
    }

}