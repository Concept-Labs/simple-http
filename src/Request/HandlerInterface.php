<?php
namespace Concept\SimpleHttp\Request;

use Concept\Http\Router\Route\Handler\RequestHandlerInterface as HttpRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

interface HandlerInterface extends HttpRequestHandlerInterface
{

    /**
     * Set the response status code
     *
     * @param int $statusCode
     * @return self
     */
    public function status(int $statusCode): self;

    /**
     * Set a header for the response
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function header(string $name, string $value): self;

    /**
     * Set the response body
     *
     * @param string $body
     * @return self
     */
    public function body(string $body): self;

    /**
     * Set the response content type and body
     *
     * @param string $type
     * @param string $body
     * @return self
     */
    public function application(string $type, string $body): self;

    /**
     * Set the response content disposition to attachment
     *
     * @param string $filename
     * @param string $contentType
     * @return self
     */
    public function download(string $filename, string $contentType = 'application/octet-stream'): self;

    /**
     * Render a PHTML template with the given context
     *
     * @param string $template
     * @param array $context
     * @return self
     */
    public function phtml(string $template, array $context = []): string;

    /**
     * Redirect to a URL
     *
     * @param string $url
     * @param int $statusCode
     * 
     * @return ResponseInterface
     */
    public function redirect(string $url, int $statusCode = 302): ResponseInterface;

    /**
     * Redirect to the referer URL
     *
     * @param int $statusCode
     * 
     * @return ResponseInterface
     */
    public function redirectReferer(int $statusCode = 302): ResponseInterface;

}