<?php
namespace Concept\SimpleHttp\Request;

use Psr\Http\Message\ServerRequestInterface;

interface SimpleRequestInterface
{
    /**
     * Set the PSR server request object.
     *
     * @param ServerRequestInterface $request
     * @return static
     */
    public function setServerRequest(ServerRequestInterface $request): static;

    /**
     * Get the PSR server request object.
     *
     * @return ServerRequestInterface
     */
    public function getServerRequest(): ServerRequestInterface;

    /**
     * Get a query parameter from the request.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Get a POST (parsed body) parameter from the request.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function post(string $name, mixed $default = null): mixed;

    /**
     * Get or set a session parameter from the request.
     *
     * @param string $name
     * @param mixed $value
     * 
     * @return mixed
     */
    public function session(string $name, mixed $value = null): mixed;

    /**
     * Get a cookie parameter from the request.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function cookie(string $name, mixed $default = null): mixed;

    /**
     * Get a header parameter from the request.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function header(string $name, mixed $default = null): mixed;
}