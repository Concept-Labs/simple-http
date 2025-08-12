<?php
namespace Concept\SimpleHttp\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Psr\Http\Server\MiddlewareInterface;

class NotFound implements MiddlewareInterface
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
        //Give the request to be handled first
        $response = $handler->handle($request);

        if ($response->getStatusCode() === 404) {
            /**
             @todo: Handle 404 Not Found response with layout object or custom rendering logic
             */
            $response = $this->getResponseFactory()
                ->createResponse(404)
                    ->withHeader('Content-Type', 'text/plain');

            $response->getBody()->write(
                sprintf(
                    '404 Not Found: The requested resource %s was not found on this server. Handled by %s',
                    $request->getUri()->getPath(),
                    static::class
                )
            );
        }

        return $response;
    }

    // /**
    //  * Handle the exception and create a response.
    //  *
    //  * @param \Throwable $e
    //  * @return ResponseInterface
    //  */
    // protected function handleException(\Throwable $e): ResponseInterface
    // {
    //     // Create a response with the exception details
    //     $response = $this->getResponseFactory()->createResponse(500)
    //         ->withHeader('Content-Type', 'application/json');
            
    //     $response->getBody()->write(json_encode([
    //         'error' => $e->getMessage(),
    //         'code' => $e->getCode(),
    //         'file' => $e->getFile(),
    //         'line' => $e->getLine(),
    //     ]));

    //     return $response->withHeader('Content-Type', 'application/json');
    // }

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