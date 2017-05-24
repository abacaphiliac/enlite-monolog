<?php

namespace Application;

use Psr\Log\LoggerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    /** @var LoggerInterface */
    private $logger;
    
    /**
     * IndexController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function indexAction()
    {
        $this->logger->info(__METHOD__);
        
        return new JsonModel();
    }
}
