<?php
namespace Concept\SimpleHttp\Layout\Component;

use Concept\Config\Contract\ConfigurableInterface;
use Concept\SimpleHttp\Layout\Context\ContextInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;

interface ComponentInterface 
    extends 
    /*StreamInterface,*/
    ConfigurableInterface,
    Stringable
{

    /**
     * Get or set the component ID.
     *
     * @param string|null $id
     * @return string|null
     */
    public function id(?string $id = null): ?string;

    /**
     * Render the component to a string.
     * If an ID is provided, render that child component instead.
     *
     * @param string|null $id
     * @return string
     */
    public function render(?string $id = null): string;

    /**
     * Set context value.
     *
     * @param string $id
     * @param mixed $value
     *
     * @return static
     */
    public function with(string $id, mixed $value): static;

    /**
     * Get or set a context value.
     * If $value is provided, set it. Otherwise, get it.
     *
     * @param string $id
     * @param mixed $value
     * 
     * @return mixed
     */
    public function context(string $id, mixed $value = null);

    /**
     * Get the context.
     *
     * @return ContextInterface
     */
    public function getContext(): ContextInterface;

    /**
     * Get the parent component.
     *
     * @return ComponentInterface|null
     */
    public function getParent(): ?ComponentInterface;

    /**
     * Set the parent component.
     *
     * @param ComponentInterface|null $parent
     *
     * @return static
     */
    public function setParent(?ComponentInterface $parent): static;

    /**
     * Add children components.
     *
     * @param string $id
     * @param ComponentInterface $child
     *
     * @return static
     */
    public function addChild(string $id, ComponentInterface $child): static;

    /**
     * Get a child component by its ID.
     * ID may contain dots for nested children, e.g. "header.logo"
     *
     * @param string $id
     * @return ComponentInterface|null
     */
    public function getChild(string $id): ?ComponentInterface;


    /**
     * Get the plugin manager.
     *
     * @return PluginManagerInterface
     */
    public function getPluginManager(): PluginManagerInterface;
}