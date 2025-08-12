<?php
namespace Concept\SimpleHttp\Example;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;
use Concept\SimpleHttp\Response\HeaderUtilInterface;
use Fig\Http\Message\StatusCodeInterface;

class SimpleHelloWorld extends SimpleHandler
{

    public function __construct()
    {
        die('lallalalala');
    }
    /**
     * The simple handler method that returns a simple HTML response.
     *
     * @return static
     */
    public function exec(SimpleRequestInterface $request): static
    {
        return $this
            ->status(StatusCodeInterface::STATUS_OK)
            ->header(HeaderUtilInterface::HEADER_CONTENT_TYPE, HeaderUtilInterface::CONTENT_TYPE_HTML)
            ->body('<h1>Simple hello world</h1>');

        // Example of rendering a PHTML template with data
        // return $this->phtml('test.phtml', [
        //     'title' => 'Hello, World!',
        //     'message' => 'This is a test message from the SimpleController.'
        // ]);
    }
}