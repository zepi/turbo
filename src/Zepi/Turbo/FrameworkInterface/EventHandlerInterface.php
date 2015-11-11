<?php
/**
 * This interface defines an event handler. The event handler
 * will be called from the event manager and executes different
 * actions.
 * 
 * @package Zepi\Turbo\FrameworkInterface
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\FrameworkInterface;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Request\RequestAbstract;
use \Zepi\Turbo\Response\Response;

/**
 * This interface defines an event handler. The event handler
 * will be called from the event manager and executes different
 * actions.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
interface EventHandlerInterface
{
    /**
     * Executes the event. This function must handle all exceptions. 
     * If the function doesn't catch an exception, the exception 
     * will terminate the whole process.
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param \Zepi\Turbo\Request\RequestAbstract $request
     * @param \Zepi\Turbo\Response\Response $response
     * @param mixed $value
     */
    public function executeEvent(Framework $framework, RequestAbstract $request, Response $response, $value = null);
}
