<?php
namespace Concept\SimpleHttp\Layout\Component;

use Concept\Config\Contract\ConfigurableTrait;
use Concept\Http\App\Config\AppConfigInterface;
use Concept\SimpleHttp\Layout\Context\ContextInterface;
use Concept\SimpleHttp\Layout\LayoutInterface;


abstract class AbstractComponent implements ComponentInterface
{
    use ConfigurableTrait;

    //private ?LayoutInterface $layout = null;
    private ?ComponentInterface $parent = null;
    private ?string $id = null;

    
    private array $children = [];

    public function __construct(
        private AppConfigInterface $appConfig,
        private ContextInterface $context, 
        private PluginManagerInterface $pluginManager
    )
    {
    }

    abstract public function __toString(): string;

    /**
     * {@inheritDoc}
     */
    public function id(?string $id = null): ?string
    {
        if ($id !== null) {
            $this->id = $id;
        }

        return $this->id ?? throw new \RuntimeException('Component ID not set.');
    }

    /**
     * Render the component or a child component by ID.
     * ID may contain dots for nested children, e.g. "body.main"
     *
     * @param string|null $id
     *
     * @return string
     */
    public function render(?string $id = null, array $context = []): string
    {
        if ($id !== null) {
            $child = $this->getChild($id);
            if ($child !== null) {
                return $child->render(null, $context);
            }
        }

        if (!empty($context)) {
            $this->getContext()->hydrate($context);
        }

        return (string)$this;
    }

    /**
     * Magic method to allow using the component as a callable.
     *
     * @param string $needle
     * @param mixed $value
     * 
     * @return mixed
     */
    public function __invoke(string $needle): mixed
    {
            $escaper = $this->getPluginManager()->get('escape') 
                ?? fn($value, $component) => htmlspecialchars($value, ENT_QUOTES, $component?->getAppConfig()?->get('charset') ?? 'UTF-8');

            return match (true) {
                /**
                 * Child component rendering, e.g. $this('.child-id')
                 */
                str_starts_with($needle, '.') => $this->getChild(substr($needle, 1))?->render(),
                /**
                 * Use plugin if starts with @, e.g. $this('@pluginName(argument)')
                 */
                str_starts_with($needle, '@') => $this->usePlugin($needle),
                /**
                 * if context contains needle and it is string than escape it
                 */
                is_string($this->context($needle)) => $escaper($this->context($needle), $this),
                /**
                 * raw f.e. object or array
                 */
                null !== $this->context($needle) => $this->context($needle),
                /**
                 * Default: return needle as is
                 */
                default => $needle
            };
    }

    /**
     * {@inheritDoc}
     */
    public function context(string $id, mixed $value = null): mixed
    {
        if ($value !== null) {
            /**
             * Sugar for setting context value if $value is provided
             */
            return $this->with($id, $value);
        }

        /**
         * Get the value from this component's context
         */
        $value = $this->getContext()->get($id);

        /**
         * If not found, propagate to parent
         */
        if (null === $value) {
            $current = $this;
            while (null !== ($current = $current->getParent())) {
                if (null !== $value = $current->getContext()->get($id)) {
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function with(string $id, mixed $value): static
    {
        $this->getContext()->set($id, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?ComponentInterface
    {
        return $this->parent;
    }

    /**
     * {@inheritDoc}
     */
    public function setParent(?ComponentInterface $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addChild(string $id, ComponentInterface $child): static
    {
        if ($child->getParent() !== null) {
            throw new \RuntimeException('Child component already has a parent.');
        }

        $this->children[$id] = $child;

        $child->setParent($this);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChild(string $id): ?ComponentInterface
    {
        if (strchr($id, '.')) {
            $parts = explode('.', $id);

            $current = '';
            while( empty($current) && !empty($parts) ) {
                $current = array_shift($parts);
            }

            $child = $this->children[$current] ?? null;

            if ($child ) {
                return $child;
            }

            return $child->getChild(implode('.', $parts));
        }

        return $this->children[$id] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPluginManager(): PluginManagerInterface
    {
        return $this->pluginManager;
    }

    /**
     * Use a plugin by name.
     *
     * @param string $dsn
     * 
     * @return mixed
     */
    protected function usePlugin(string $uri): mixed
    {

        if (!str_starts_with($uri, '@')) {
            return null;
        }

        $matches = [];

        if (!preg_match('/^@([\w\-\.]*)\((.*)?\)$/', $uri, $matches)) {
            throw new \RuntimeException("Invalid plugin URI: \"$uri\"");
        }

        $name = $matches[1];
        $argument = $matches[2] ?? null;

        /**
         * Nested plugin calls, e.g. @plugin1(@plugin2(arg))
         */
        $argument = $this->context($argument ?? '') ?? $this($argument) ?? $argument;

        if (null === $plugin = $this->getPluginManager()->get($name)) {
            $current = $this;
            while (null !== ($current = $current->getParent())) {
                if (null !== $plugin = $current->getPluginManager()->get($name)) {
                    break;
                }
            }
        }

        if (null !== $plugin) {
            return (string)$plugin($argument,$this) ?: throw new \RuntimeException("Plugin '@{$name}' returned empty value.");
        }

        throw new \RuntimeException("Plugin '@{$name}' not found.");
    }

    /**
     * Get the application config
     * 
     * @return AppConfigInterface
     */
    protected function getAppConfig(): AppConfigInterface
    {
        return $this->appConfig;
    }

}