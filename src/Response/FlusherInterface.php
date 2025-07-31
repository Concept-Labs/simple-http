<?php
namespace Concept\SimpleHttp\Response;

use Concept\Config\Contract\ConfigurableInterface;
use Psr\Http\Server\MiddlewareInterface;

interface FlusherInterface extends MiddlewareInterface, ConfigurableInterface
{

}