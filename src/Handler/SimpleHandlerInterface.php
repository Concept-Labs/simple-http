<?php
namespace Concept\SimpleHttp\Handler;

use Psr\Http\Message\ResponseInterface;
use Concept\Http\Router\Route\Handler\RequestHandlerInterface as HttpRequestHandlerInterface;
use Concept\SimpleHttp\Request\SimpleRequestInterface;
use Concept\SimpleHttp\Util\HeaderUtilInterface;

interface SimpleHandlerInterface extends HttpRequestHandlerInterface
{

    /**
     * Execute the handler with the provided SimpleRequestInterface.
     *
     * This method should implement the specific logic for handling the request
     * and returning a response.
     *
     * @param SimpleRequestInterface $r The simple request object
     * 
     * @return static
     */
    public function act(SimpleRequestInterface $r): static;

    /**
     * Get the request object
     *
     * @return SimpleRequestInterface
     */
    public function getSimpleRequest(): SimpleRequestInterface;

    /**
     * Set the response status code
     *
     * @param int $statusCode
     * @return static
     */
    public function status(int $statusCode): static;

    /**
     * Set a header for the response
     *
     * @param string $name
     * @param string $value
     * @return static
     */
    public function header(string $name, string $value): static;

    /**
     * Set the response body
     *
     * @param string $body
     * @return static
     */
    public function body(string $body): static;

    /**
     * Set the response content type and body
     *
     * @param string $type
     * @param string $body
     * @return static
     */
    public function application(string $type, string $body): static;

    /**
     * Set the response to JSON format
     *
     * @param array $data
     * @return static
     */
    public function json(array $data): static;

    /**
     * Set the response content disposition to attachment
     *
     * @param string $filename
     * @param string $contentType
     * @return static
     */
    public function download(string $filename, string $contentType = HeaderUtilInterface::CONTENT_TYPE_OCTET_STREAM): static;

    /**
     * Render a PHTML template with the given context
     *
     * @param string $template
     * @param array $context
     * @return static
     */
    //public function phtml(string $template, array $context = []): static;

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