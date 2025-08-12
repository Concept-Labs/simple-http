<?php
namespace Concept\SimpleHttp\Layout;

class Layout 
{
    protected array $components = [];

    public function add(RenderableInterface $component): void
    {
        $this->components[] = $component;
    }

    public function render(): string
    {
        $output = '';
        foreach ($this->components as $component) {
            $output .= $component->render();
        }
        return $output;
    }
}