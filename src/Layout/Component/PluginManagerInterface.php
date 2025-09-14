<?php

namespace Concept\SimpleHttp\Layout\Component;

interface PluginManagerInterface 
{
    /**
     * Register a plugin.
     *
     * @param string $name
     * @param callable $callback
     * @return static
     */
    public function register(string $name, callable $callback): static;

    /**
     * Get a plugin by name.
     *
     * @param string $name
     * @return callable|null
     */
    public function get(string $name): ?callable;

    /**
     * Check if a plugin exists.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;
}