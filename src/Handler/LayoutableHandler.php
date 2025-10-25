<?php

namespace Concept\SimpleHttp\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Concept\Arrays\RecursiveApi;
use Concept\Http\App\Config\AppConfigInterface;
use Concept\SimpleHttp\Layout\Component\ComponentInterface;
use Concept\SimpleHttp\Layout\LayoutBuilderInterface;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

abstract class LayoutableHandler extends SimpleHandler implements LayoutableHandlerInterface
{
    /**
     * Cached layout configuration
     *
     * @var array|null
     */
    protected ?array $layoutConfig = null;

    /**
     * Cached layout instance
     *
     * @var ComponentInterface|null
     */
    protected ?ComponentInterface $layout = null;

    /**
     * Dependency injection constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param SimpleRequestInterface $simpleRequest
     * @param AppConfigInterface $appConfig
     * @param LayoutBuilderInterface $layoutBuilder
     */
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private LayoutBuilderInterface $layoutBuilder
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }

    /**
     * Layoutable specific implementation of the handle method
     *
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /*$this->response = */parent::handle($request);
        /*
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $this->getLayout()->render(), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        */
        $this
            ->body(
                $this->getLayout()->render()
        );

        return $this->getResponse();
    }

    /**
     * Get the layout builder
     *
     * @return LayoutBuilderInterface The layout builder instance
     */
    protected function getLayoutBuilder(): LayoutBuilderInterface
    {
        return $this->layoutBuilder;
    }

    /**
     * Get the layout instance, building it if not already built
     *
     * @param string|null $component The component ID to retrieve, or null for the root
     *
     * @return ComponentInterface The layout(root component) instance
     */
    protected function getLayout(?string $component = null): ComponentInterface
    {
        $this->layout ??= $this->getLayoutBuilder()
            ->build($this->getLayoutConfig());

        return (null !== $component) 
            ? $this->layout->getChild($component) ?? throw new \RuntimeException('Component not found.')
            : $this->layout;
    }

    /**
     * Get the layout configuration by merging the application and handler layout configurations
     *
     * @return array The merged layout configuration
     */
    protected function getLayoutConfig(): array
    {
        return $this->layoutConfig ??= $this->mergeHandlerLayoutConfig();
    }

    /**
     * Get the application layout configuration
     *
     * @return array The application layout configuration
     */
    protected function getAppLayoutConfig(): array
    {
        return $this->getAppConfig()->get(LayoutBuilderInterface::CONFIG_NODE_LAYOUT) ?? [];
    }

    /**
     * Get the handler-specific layout configuration
     *
     * @return array The handler-specific layout configuration
     */
    protected function getHandlerLayoutConfig(): array
    {
        return $this->getConfig()->get(LayoutBuilderInterface::CONFIG_NODE_LAYOUT) ?? [];
    }

    /**
     * Build the layout configuration by merging the application and handler layout configurations
     *
     * @return array The merged layout configuration
     */
    private function mergeHandlerLayoutConfig(): array
    {
        $layoutConfig = $this->getAppLayoutConfig();
        $handlerLayoutConfig = $this->getHandlerLayoutConfig();

        RecursiveApi::merge(
            $layoutConfig,
            $handlerLayoutConfig,
            RecursiveApi::MERGE_OVERWRITE
        );
        //array_replace_recursive($layoutConfig, $handlerLayoutConfig);

        return $layoutConfig;
    }

}
