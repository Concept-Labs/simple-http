<?php
namespace Concept\SimpleHttp\Handler;

use Concept\Config\Contract\ConfigurableTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Concept\Http\Router\Route\Handler\RequestHandler;
use Concept\Phtml\Template\Factory\PhtmlFactoryInterface;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

abstract class SimpleHandler extends RequestHandler implements SimpleHandlerInterface
{
    use ConfigurableTrait;

    /**
     * @var ServerRequestInterface|null
     */
    protected ?ServerRequestInterface $request = null;

    /**
     * Dependency injection constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param PhtmlFactoryInterface $phtmlFactory
     */
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        /** @var SimpleRequestInterface The simplified request object */
        protected SimpleRequestInterface $simpleRequest,
        //protected LayoutFactoryInterface $layoutFactory, // Optional, if you need layout rendering
        /** @var PhtmlFactoryInterface The PHTML factory for rendering templates */
        protected PhtmlFactoryInterface $phtmlFactory
    )
    {
        parent::__construct($responseFactory);
    }

    /**
     * Specific version of the handler that must be implemented by subclasses.
     *
     * @param SimpleRequestInterface $request The simple request object
     *
     * @return static
     */
    abstract public function exec(SimpleRequestInterface $request): static;

    /**
     * Get the PHTML factory
     *
     * @return PhtmlFactoryInterface
     * @throws \RuntimeException
     */
    protected function getPhtmlFactory(): PhtmlFactoryInterface
    {
        if (!$this->phtmlFactory instanceof PhtmlFactoryInterface) {
            throw new \RuntimeException('Phtml factory is not set or invalid.');
        }

        return $this->phtmlFactory;
    }

    /**
     * Set the request object
     *
     * @param ServerRequestInterface $request
     * @return static
     */
    protected function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;
        $this->simpleRequest->setServerRequest($request);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequest(): ServerRequestInterface
    {
        if ($this->request === null) {
            throw new \RuntimeException('Request object is not set. Call exec() before accessing the request.');
        }

        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function getSimpleRequest(): SimpleRequestInterface
    {
        return $this->simpleRequest;
    }

    /**
     * Handle the incoming request and return a response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Set the request object and the simple request
        $this->setRequest($request);

        // Handle the request using the specific implementation
        $this->exec($this->getSimpleRequest());

        // Return the response object
        return $this->getResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function status(int $code): static
    {
        $this->response = $this->getResponse()->withStatus($code);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function header(string $name, string $value): static
    {
        $this->response = $this->getResponse()->withHeader($name, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function body(string $body): static
    {
        $this->getResponse()
            ->getBody()
            ->write($body);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function application(string $type, string $body): static
    {
        return $this
            ->header('Content-Type', $type)
            ->body($body);
    }

    /**
     * {@inheritDoc}
     */
    public function json(array $data): static
    {
        $this->application('application/json', json_encode($data, JSON_THROW_ON_ERROR));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function download(string $filename, string $contentType = 'application/octet-stream'): static
    {
        $this->response = $this->getResponse()
            ->withHeader('Content-Disposition', 'attachment; filename="' . basename($filename) . '"')
            ->withHeader('Content-Type', $contentType);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function phtml(string $template, array $context = []): static
    {
        $phtmlFactory = $this->getPhtmlFactory();
        $phtml = $phtmlFactory->create();
        $content = $phtml->render($template, $context);

        $this
            ->status(200)
            ->header('Content-Type', 'text/html')
            ->body($content)
        ;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function redirect(string $url, int $statusCode = 302): ResponseInterface
    {
        $parsed = parse_url($url);

        if (isset($parsed['scheme']) || isset($parsed['host'])) {
            throw new \InvalidArgumentException('Redirect URL must be relative or absolute without scheme and host.');
        }
        
        $this->response = $this->getResponse()
            ->withStatus($statusCode)
            ->withoutHeader('Location')
            ->withHeader('Location', $url);

        return $this->getResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function redirectReferer(int $statusCode = 302): ResponseInterface
    {
        $serverParams = $this->getRequest()->getServerParams();
        $referer = $this->getRequest()
            ->getHeaderLine('Referer') ?: $serverParams['HTTP_REFERER'] ?? '/'; // Fallback to HTTP_REFERER if not set

        $parsed = parse_url($referer);

        $path = $parsed['path'] ?? '/';
        $query = $parsed['query'] ?? '';

        if ($query) {
            $referer = $path . '?' . $query;
        } else {
            $referer = $path;
        }

        if (empty($referer)) {
            throw new \RuntimeException('No referer URL found for redirection.');
        }

        return $this->redirect($referer, $statusCode);
    }

}