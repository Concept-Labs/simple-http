<?php
namespace Concept\SimpleHttp\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Concept\Config\Contract\ConfigurableTrait;
use Concept\Http\App\Exception\RuntimeException;

class Flusher implements FlusherInterface
{
    use ConfigurableTrait;

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        
        $response = $handler->handle($request);
        
        $this->flush($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function flush(ResponseInterface $response): void
    {
        $this
            ->sendHeaders($response)
            ->sendBody($response);
    }

    /**
     * Send the response headers.
     *
     * @param ResponseInterface $response
     * @return static
     */
    protected function sendHeaders(ResponseInterface $response): static
    {
        http_response_code($response->getStatusCode());
        
        if (headers_sent()) {
            throw new RuntimeException('Headers already sent');
        }
        $this->sendHeader('X-Powered-By', ['Concept-Labs', 'Viktor Halitskyi (concept.galitsky@gmail.com)']);
        foreach ($response->getHeaders() as $name => $values) {
            $this->sendHeader($name, $values);
        }

        if ($response->getReasonPhrase() !== '') {
            $this->sendHeader('Status', [sprintf('%d %s', $response->getStatusCode(), $response->getReasonPhrase())]);
        }

        return $this;
    }

    /**
     * Send a single header.
     *
     * @param string $name
     * @param array $values
     * @return static
     */
    protected function sendHeader(string $name, array $values): static
    {
        header(
            sprintf(
                '%s: %s',
                $name,
                implode(', ', $values)
            ),
            false
        );

        return $this;
    }

    /**
     * Send the response body.
     *
     * @param ResponseInterface $response
     * @return static
     */
    protected function sendBody(ResponseInterface $response): static
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            $oh = fopen('php://output', 'wb');
            if ($oh === false) {
                throw new RuntimeException('Failed to open output stream');
            }
            stream_copy_to_stream($body->detach(), $oh);
            fclose($oh);

        }
        //echo $body->read(8192);

        return $this;
    }
}