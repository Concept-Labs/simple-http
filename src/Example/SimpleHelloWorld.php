<?php
namespace Concept\SimpleHttp\Example;

use Concept\SimpleHttp\Handler\PageHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class SimpleHelloWorld extends PageHandler
{
    /**
     * The simple handler method that returns a simple HTML response.
     *
     * @return static
     */
    public function act(SimpleRequestInterface $request): static
    {
        $this->getLayout()       
            ->with('pageTitle', 'Hello from Body')
            ->context('example-escaped-message', '<h1>Body example message!</h1>')
            ->with('something-raw', '<script>alert("something")</script><h2>Script should do alert</h2>')
            ->with('some_json', ['one' => 1, 'two' => 2, 'three' => 3])

            ->getPluginManager()
                ->register('example.div.red', fn($arg, $component) => "<div style='border: 1px solid red; padding: 10px; margin: 10px;'>Example Plugin Output with arg: " . $arg . "</div>")
                ->register('example.div.nested', fn($arg, $component) => "<div style='border: 1px solid green; padding: 10px; margin: 10px;'>" . $component($arg) . "</div>");

        // Example of raw response without layout
        // return $this
        //     ->status(StatusCodeInterface::STATUS_OK)
        //     ->header(HeaderUtilInterface::HEADER_CONTENT_TYPE, HeaderUtilInterface::CONTENT_TYPE_HTML)
        //     ->body('<html><body><h1>Simple hello world</h1></body></html>');

        return $this;
    }
}