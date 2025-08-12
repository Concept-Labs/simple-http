<?php
namespace Concept\SimpleHttp\Request;


use Psr\Http\Message\ServerRequestInterface;

class SimpleRequest  implements SimpleRequestInterface
{
    protected ?ServerRequestInterface $serverRequest = null;


    public function setServerRequest(ServerRequestInterface $request): static
    {
        $this->serverRequest = $request;

        return $this;
    }

    public function getServerRequest(): ServerRequestInterface
    {
        if ($this->serverRequest === null) {
            throw new \RuntimeException('Server request is not set. Please set it using setServerRequest() method.');
        }

        return $this->serverRequest;
    }
    /**
     * {@inheritDoc}
     */
    public function get(string $name, mixed $default = null): mixed
    {
        $queryParams = $this->getServerRequest()->getQueryParams();
        return $queryParams[$name] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function post(string $name, mixed $default = null): mixed
    {
        $parsedBody = $this->getServerRequest()->getParsedBody();
        return $parsedBody[$name] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function cookie(string $name, mixed $default = null): mixed
    {
        $cookies = $this->getServerRequest()->getCookieParams();
        return $cookies[$name] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function header(string $name, mixed $default = null): mixed
    {
        $headers = $this->getServerRequest()->getHeaders();
        $name = strtolower($name);
        return $headers[$name] ?? $default;
    }
}
    