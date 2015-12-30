<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 zepi
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

/**
 * The RuntimeManager registers and executes all handlers. The
 * handlers are saved in an object backend.
 * 
 * @package Zepi\Turbo\Manager
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Manager;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Backend\ObjectBackendAbstract;
use \Zepi\Turbo\Request\RequestAbstract;
use \Zepi\Turbo\FrameworkInterface\EventHandlerInterface;
use Zepi\Turbo\FrameworkInterface\CliEventHandlerInterface;

/**
 * The RuntimeManager registers and executes all handlers. The
 * handlers are saved in an object backend.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class RuntimeManager
{
    const EVENT = 'event';
    const FILTER = 'filter';
    
    /**
     * @access protected
     * @var Framework
     */
    protected $_framework;
    
    /**
     * @access protected
     * @var ObjectBackendAbstract
     */
    protected $_handlerObjectBackend;
    
    /**
     * @access protected
     * @var array
     */
    protected $_handlers = array();
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param \Zepi\Turbo\Backend\ObjectBackendAbstract $handlerObjectBackend
     */
    public function __construct(Framework $framework, ObjectBackendAbstract $handlerObjectBackend)
    {
        $this->_framework = $framework;
        $this->_handlerObjectBackend = $handlerObjectBackend;
    }
    
    /**
     * Initializes the event system. The function loads all saved
     * events from the object backend.
     * 
     * @access public
     */
    public function initializeManager()
    {
        $handlers = $this->_handlerObjectBackend->loadObject();
        if (!is_array($handlers)) {
            $handlers = array();
        }
        
        $this->_handlers = $handlers;
    }
    
    /**
     * Executes the events for the given event name
     * 
     * @access public
     * @param string $eventName
     */
    public function executeEvent($eventName)
    {
        $this->_executeItems(self::EVENT, $eventName);
    }
    
    /**
     * Executes the filter for the given filter name
     *
     * @access public
     * @param string $eventName
     * @param mixed $value
     */
    public function executeFilter($eventName, $value = null)
    {
        return $this->_executeItems(self::FILTER, $eventName, $value);
    }
    
    /**
     * Executes the given event handlers. Every event handler will
     * be initialized and executed.
     * 
     * @access protected
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    protected function _executeItems($type, $name, $value = null)
    {
        if (!isset($this->_handlers[$type][$name])) {
            return;
        }
        
        $request = $this->_framework->getRequest();

        foreach ($this->_filterHandlers($type, $name, $request) as $handlerName) {
            $handler = $this->_framework->getInstance($handlerName);
                
            $response = $this->_framework->getResponse();
            $response->setData('_executedType', $type);
            $response->setData('_executedName', $name);
                
            // Execute the handler
            $handlerResult = $handler->execute(
                $this->_framework, 
                $this->_framework->getRequest(), 
                $response,
                $value
            );
                
            // Save the handler result to the variable if this is an filter handler
            if ($type === self::FILTER) {
                $value = $handlerResult;
            }
        }
        
        // Return the value if this is an filter handler
        if ($type === self::FILTER) {
            return $value;
        }
    }
    
    /**
     * Filters the handler and returns an single array with
     * all queued handlers
     *
     * @access protected
     * @param string $type            
     * @param string $name            
     * @param RequestAbstract $request            
     * @return array
     */
    protected function _filterHandlers($type, $name, RequestAbstract $request)
    {
        $filteredHandlers = array();
        
        foreach ($this->_handlers[$type][$name] as $priority => $handlers) {
            foreach ($handlers as $handlerName) {
                if ($type === self::EVENT && !$this->_compareRequestWithInterface($request, $handlerName)) {
                    continue;
                }
                
                $filteredHandlers[] = $handlerName;
            }
        }

        return array_unique($filteredHandlers);
    }
    
    /**
     * Returns true if the event handler and the request are from the same interface,
     * e.g. both are from cli. If the event handler is neutral return true. Return false
     * if the event handler isn't neutral and the request is not from the same interface.
     * 
     * @access protected
     * @param RequestAbstract $request
     * @param string $handlerName
     * @return boolean
     */
    protected function _compareRequestWithInterface(RequestAbstract $request, $handlerName)
    {
        $implementedInterfaces = class_implements($handlerName, true);

        // If the event is a cli event but the request isn't a cli request
        // we skip this event
        if (isset($implementedInterfaces['Zepi\\Turbo\\FrameworkInterface\\CliEventHandlerInterface'])
            && get_class($request) != 'Zepi\\Turbo\\Request\\CliRequest') {
            return false;
        }

        // If the event is a web event but the request isn't a web request
        // we skip this event
        if (isset($implementedInterfaces['Zepi\\Turbo\\FrameworkInterface\\WebEventHandlerInterface'])
            && get_class($request) != 'Zepi\\Turbo\\Request\\WebRequest') {
            return false;
        }
        
        return true;
    }
    
    /**
     * Adds an event handler
     * 
     * @access public
     * @param string $eventName
     * @param string $eventHandlerName
     * @param integer $priority
     */
    public function addEventHandler($eventName, $eventHandlerName, $priority = 50)
    {
        $this->_addHandler(self::EVENT, $eventName, $eventHandlerName, $priority);
    }
    
    /**
     * Adds an filter handler
     * 
     * @access public
     * @param string $filterName
     * @param string $filterHandlerName
     * @param integer $priority
     */
    public function addFilterHandler($filterName, $filterHandlerName, $priority = 50)
    {
        $this->_addHandler(self::FILTER, $filterName, $filterHandlerName, $priority);
    }
    
    /**
     * Adds a new handler
     * 
     * @access protected
     * @param string $type
     * @param string $name
     * @param string $handlerName
     * @param integer $priority
     */
    protected function _addHandler($type, $name, $handlerName, $priority)
    {
        // If the priority isn't existing we add the priority as 
        // a new array.
        if (!isset($this->_handlers[$type][$name][$priority])) {
            $this->_handlers[$type][$name][$priority] = array();
            ksort($this->_handlers[$type][$name]);
        }
        
        // If we had the event handler already registred, return at this point
        if (in_array($handlerName, $this->_handlers[$type][$name][$priority])) {
            return;
        }
        
        // Add the event handler and save the new events array
        $this->_handlers[$type][$name][$priority][] = $handlerName;
        $this->_saveHandlers();
    }
    
    /**
     * Removes an event handler
     * 
     * @access public
     * @param string $eventName
     * @param string $eventHandlerName
     * @param integer $priority
     */
    public function removeEventHandler($eventName, $eventHandlerName, $priority = 50)
    {
        $this->_removeHandler(self::EVENT, $eventName, $eventHandlerName, $priority);
    }
    
    /**
     * Removes an filter handler
     * 
     * @access public
     * @param string $filterName
     * @param string $filterHandlerName
     * @param integer $priority
     */
    public function removeFilterHandler($filterName, $filterHandlerName, $priority = 50)
    {
        $this->_removeHandler(self::FILTER, $filterName, $filterHandlerName, $priority);
    }
    
    /**
     * Removes an handler
     * 
     * @access protected
     * @param string $type
     * @param string $name
     * @param string $handlerName
     * @param string $priority
     */
    protected function _removeHandler($type, $name, $handlerName, $priority)
    {
        // If the event isn't set we add an array to the events array
        if (!isset($this->_handlers[$type][$name][$priority])) {
            return;
        }
        
        // If the event handler isn't registred we return with true.
        if (!in_array($handlerName, $this->_handlers[$type][$name][$priority])) {
            return;
        }
        
        // Remove the event handler from the array
        $index = array_search($handlerName, $this->_handlers[$type][$name][$priority]);
        unset($this->_handlers[$type][$name][$priority][$index]);
        $this->_saveHandlers();
    }
    
    /**
     * Clears the handlers cache and reactivates the modules
     * to rebuild the cache.
     * 
     * @access public
     * @param boolean $reactivateModules
     */
    public function clearCache($reactivateModules = true)
    {
        $this->_handlers = array();
        
        if ($reactivateModules) {
            $this->_framework->getModuleManager()->reactivateModules();
        }
    }
    
    /**
     * Saves the registred handlers in the object backend.
     * 
     * @access protected
     */
    protected function _saveHandlers()
    {
        $this->_handlerObjectBackend->saveObject($this->_handlers);
    }
}
