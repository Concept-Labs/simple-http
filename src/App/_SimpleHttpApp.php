<?php
namespace Concept\SimpleHttp\App;

use Concept\Http\App\HttpApp;

/**
 * SimpleHttpApp class
 * This class extends HttpApp to provide a simple HTTP application implementation.
 * It can be used to create and manage HTTP applications with minimal configuration.
 * It need to be used with SimpleHttpAppFactory to create an instance of this class.
 * Purpose: container(singularity) dependency stack to resolve services according simple-http package namespace.
 */
class SimpleHttpApp extends HttpApp
{
}