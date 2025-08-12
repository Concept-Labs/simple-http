<?php
namespace Concept\SimpleHttp\Layout;

use Concept\Arrays\DotArray\DotArrayInterface;
use Concept\Config\Contract\ConfigurableTrait;

class Component implements ComponentInterface
{
    use ConfigurableTrait;

    protected array $children = [];
    protected ComponentInterface $parent;
    protected DotArrayInterface $context;

    public function __toString(): string
    {
        return $this->render();
    }

    public function get(string $name): mixed
    {
        return $this->getContext($name);
    }

    public function getContext(?string $name = null): DotArrayInterface
    {
        if ($name !== null) {
            return $this->context->get($name);
        }
        return $this->context;
    }

    public function addChild(ComponentInterface $child): void
    {
        $child->parent = $this;
        $this->children[] = $child;
    }

    protected function getParent(): ComponentInterface
    {
        return $this->parent;
    }

    public function render(): string
    {
        $output = '';
        foreach ($this->children as $child) {
            $output .= (string)$child;
        }
        return $output;
    }

}
