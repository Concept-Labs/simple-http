<?php

namespace Concept\SimpleHttp;

use Concept\Http\AppInterface;
use Concept\SimpleHttp\App\SimpleAppFactory;

class Bootstrap extends \Concept\Http\Bootstrap
{

    /**
     * Create the application instance
     *
     * @return AppInterface
     */
    // public function app(): AppInterface
    // {
    //     return $this
    //         ->getContainer()
    //             ->get(SimpleAppFactory::class)
    //                 ->setConfig(
    //                     $this->getConfig()//->node('app')
    //                 )
    //             ->create();
    // }
    
}