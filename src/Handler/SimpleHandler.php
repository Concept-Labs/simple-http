<?php
namespace Concept\SimpleHttp\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Http\App\Config\AppConfigInterface;
use Concept\Http\Router\Route\Handler\RequestHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;
use Concept\SimpleHttp\Util\HeaderUtilInterface;

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
     */
    public function __construct(
        protected SimpleRequestInterface $simpleRequest,
        protected AppConfigInterface $appConfig
    )
    {
    }

    /**
     * Specific version of the handler that must be implemented by subclasses.
     *
     * @param SimpleRequestInterface $request The simple request object
     *
     * @return static
     */
    abstract public function act(SimpleRequestInterface $request): static;

    /**
     * Get the application config
     *
     * @return AppConfigInterface
     */
    protected function getAppConfig(): AppConfigInterface
    {
        return $this->appConfig;
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
        $this->getSimpleRequest()->setServerRequest($request);

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
        // Set the request object and create the simple request
        $this->setRequest($request);

        // Handle the request using the specific implementation
        $this->act($this->getSimpleRequest());

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
            ->header(HeaderUtilInterface::HEADER_CONTENT_TYPE, $type)
            ->body($body);
    }

    /**
     * {@inheritDoc}
     */
    public function json(array $data): static
    {
        $this->application(
            HeaderUtilInterface::CONTENT_TYPE_JSON, 
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function download(string $filename, ?string $as = null, string $contentType = HeaderUtilInterface::CONTENT_TYPE_OCTET_STREAM): static
    {
        $as = $as ?: basename($filename);

        $this->response = $this->getResponse()
            ->withHeader(
                HeaderUtilInterface::HEADER_CONTENT_DISPOSITION,
                'attachment; filename="' . $as . '"'
            )
            ->withHeader(HeaderUtilInterface::HEADER_CONTENT_TYPE, $contentType);

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