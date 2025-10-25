<?php
namespace Concept\SimpleHttp\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\SimpleHttp\Util\HeaderUtilInterface;

class Throwable implements ThrowableInterface
{
    use ConfigurableTrait;

    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (\Throwable $e) {
            // Handle the exception and create a response
            $response = $this->handleException($e);
        }

        return $response;
    }

    /**
     * Handle the exception and create a response.
     *
     * @param \Throwable $e
     * @return ResponseInterface
     */
    protected function handleException(\Throwable $e): ResponseInterface
    {
        /**
         * @todo Handle the exception and create a response.
         */
        // Create a response with the exception details
        $response = $this->getResponseFactory()->createResponse(500)
            ->withHeader(HeaderUtilInterface::HEADER_CONTENT_TYPE, HeaderUtilInterface::CONTENT_TYPE_TEXT);

        $response->getBody()->write(
            "Throwable caught:\n" .
            $e->getMessage() . "\n" .
            $e->getTraceAsString()
        );

        return $response;
    }

    /**
     * Get the response factory.
     *
     * @return ResponseFactoryInterface
     */
    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

}