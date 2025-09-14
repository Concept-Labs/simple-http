<?php
namespace Concept\SimpleHttp\Layout;

use Concept\SimpleHttp\Layout\Component\ComponentInterface;

interface LayoutBuilderInterface
{

    const CONFIG_NODE_ROOT = 'root';
    const CONFIG_NODE_LAYOUT = 'layout';
    const CONFIG_NODE_CONTEXT = 'context';
    const CONFIG_NODE_CONFIG = '___config';
    const CONFIG_NODE_CHILDREN = '___children';

    /**
     * Build the layout from the given config.
     *
     * @param array $config
     * 
     * @return ComponentInterface
     */
    public function build(array $config): ComponentInterface;
}