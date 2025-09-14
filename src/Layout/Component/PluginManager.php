<?php
namespace Concept\SimpleHttp\Layout\Component;

class PluginManager implements PluginManagerInterface
{
    /** @var array<string, callable> */
    protected array $plugins = [];

    /**
     * {@inheritDoc}
     */
    public function register(string $name, callable $callback): static
    {
        $this->plugins[$name] = $callback;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $name): ?callable
    {
        return $this->plugins[$name] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $name): bool
    {
        return isset($this->plugins[$name]);
    }
}