<?php
namespace Concept\SimpleHttp\Request;


use Concept\Http\Router\Route\Handler\RequestHandler as HttpRequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Handler extends HttpRequestHandler implements HandlerInterface
{

    /**
     * @var ServerRequestInterface|null
     */
    protected ?ServerRequestInterface $request = null;

    /**
     * Execute the request handler and return the response
     *
     * @return static
     */
    abstract public function exec(): static;

    /**
     * Handle the incoming request and return a response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Set the request object
        $this->request = $request;

        // Handle the request and return the response
        $this->exec();

        // Return the response object
        return $this->getResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function status(int $code): self
    {
        $this->response = $this->getResponse()->withStatus($code);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function header(string $name, string $value): self
    {
        $this->response = $this->getResponse()->withHeader($name, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function body(string $body): self
    {
        $this->getResponse()
            ->getBody()
            ->write($body);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function application(string $type, string $body): self
    {
        return $this
            ->header('Content-Type', $type)
            ->body($body);
    }

    /**
     * {@inheritDoc}
     */
    public function download(string $filename, string $contentType = 'application/octet-stream'): self
    {
        $this->response = $this->getResponse()
            ->withHeader('Content-Disposition', 'attachment; filename="' . basename($filename) . '"')
            ->withHeader('Content-Type', $contentType);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function phtml(string $template, array $context = []): string
    {
        $this->response = $this->getResponse()
            ->withHeader('Content-Type', 'text/html');

        ob_start() or throw new \RuntimeException('Failed to start output buffering');
        (new class {
            public function var(string $name, mixed $default = null): mixed
            {
                return $this->context[$name] ?? $default;
            }
            public function __construct(private string $template, private array $context) {
                if (!file_exists($this->template)) {
                    throw new \RuntimeException("Template file not found: {$this->template}");
                }
                include $this->template;
            }
        })($template, $context);

        $content = ob_get_clean() or throw new \RuntimeException('Failed to get output buffer content');

        return $content;
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
            ->withHeader('Location', $url);

        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function redirectReferer(int $statusCode = 302): ResponseInterface
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';

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