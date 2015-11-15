<?php

namespace TestModule;

use \Zepi\Turbo\FrameworkInterface\EventHandlerInterface;
use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Request\RequestAbstract;
use \Zepi\Turbo\Response\Response;
use \Zepi\Web\Test\Exception;

class TestEventHandler implements EventHandlerInterface
{
    static $executedEvents = array();
    
    /**
     * Test Event HAndler
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param \Zepi\Turbo\Request\RequestAbstract $request
     * @param \Zepi\Turbo\Response\Response $response
     */
    public function execute(Framework $framework, RequestAbstract $request, Response $response)
    {
        $eventName = $response->getData('_executedName');
        
        if (!isset(self::$executedEvents[$eventName])) {
            self::$executedEvents[$eventName] = 1;
        } else {
            self::$executedEvents[$eventName]++;
        }
    }
}
