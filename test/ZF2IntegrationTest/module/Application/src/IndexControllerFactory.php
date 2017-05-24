<?php

namespace Application;

class IndexControllerFactory
{
    public function __invoke($services)
    {
        $logger = $services->getServiceLocator()->get('EnliteMonologService');
        
        return new IndexController($logger);
    }
}
