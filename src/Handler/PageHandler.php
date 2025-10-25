<?php

namespace Concept\SimpleHttp\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class PageHandler extends LayoutableHandler implements PageHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        parent::handle($request);

        $this
            //->header(HeaderUtilInterface::HEADER_CONTENT_TYPE, 'text/html; charset=utf-8')
            //->header(HeaderUtilInterface::HEADER_X_CONTENT_TYPE_OPTIONS, 'nosniff')
            ;

        return $this->getResponse();
    }
}