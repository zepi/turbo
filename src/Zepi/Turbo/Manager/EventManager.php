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
 * The EventManager registers and executes all events. The
 * events are saved in an object backend.
 * 
 * @package Zepi\Turbo\Manager
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Manager;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Backend\ObjectBackendAbstract;

/**
 * The EventManager registers and executes all events. The
 * events are saved in an object backend.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class EventManager
{
    /**
     * @access protected
     * @var Framework
     */
    protected $_framework;
    
    /**
     * @access protected
     * @var ObjectBackendAbstract
     */
    protected $_eventObjectBackend;
    
    /**
     * @access protected
     * @var array
     */
    protected $_events = array();
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param \Zepi\Turbo\Backend\ObjectBackendAbstract $eventObjectBackend
     */
    public function __construct(Framework $framework, ObjectBackendAbstract $eventObjectBackend)
    {
        $this->_framework = $framework;
        $this->_eventObjectBackend = $eventObjectBackend;
    }
    
    /**
     * Initializes the event system. The function loads all saved
     * events from the object backend.
     * 
     * @access public
     */
    public function initializeEventSystem()
    {
        $events = $this->_eventObjectBackend->loadObject();
        if (!is_array($events)) {
            $events = array();
        }
        
        $this->_events = $events;
    }
    
    /**
     * Executes the events for the given event name
     * 
     * @access public
     * @param string $eventName
     * @param mixed $value
     * @return mixed
     */
    public function executeEvent($eventName, $value = null)
    {
        if (isset($this->_events[$eventName])) {
            $value = $this->_executeEvents($this->_events[$eventName], $value);
        }
        
        return $value;
    }
    
    /**
     * Executes the given event handlers. Every event handler will
     * be initialized and executed.
     * 
     * @access protected
     * @param array $events
     * @param mixed $value
     * @return mixed
     */
    protected function _executeEvents($events, $value = null)
    {
        foreach ($events as $priority => $eventHandlers) {
            foreach ($eventHandlers as $eventHandlerName) {
                $eventHandler = new $eventHandlerName();
                $value = $eventHandler->executeEvent(
                    $this->_framework, 
                    $this->_framework->getRequest(), 
                    $this->_framework->getResponse(),
                    $value
                );
            }
        }
        
        return $value;
    }
    
    /**
     * Adds an event handler for the given event.
     * 
     * @access public
     * @param string $eventName
     * @param string $eventHandlerName
     * @param integer $priority
     * @return boolean
     */
    public function addEventHandler($eventName, $eventHandlerName, $priority = 50)
    {
        // If the event isn't set we add an array to the events array
        if (!isset($this->_events[$eventName])) {
            $this->_events[$eventName] = array();
        }
        
        // If the priority isn't existing we add the priority as 
        // a new array.
        if (!isset($this->_events[$eventName][$priority])) {
            $this->_events[$eventName][$priority] = array();
            ksort($this->_events[$eventName]);
        }
        
        // If we had the event handler already registred, return at this point
        if (in_array($eventHandlerName, $this->_events[$eventName][$priority])) {
            return true;
        }
        
        // Add the event handler and save the new events array
        $this->_events[$eventName][$priority][] = $eventHandlerName;
        $this->_saveEvents();
        
        return true;
    }
    
    /**
     * Removes an event handler for the given event.
     * 
     * @access public
     * @param string $eventName
     * @param string $eventHandlerName
     * @param integer $priority
     * @return boolean
     */
    public function removeEventHandler($eventName, $eventHandlerName, $priority = 50)
    {
        // If the event isn't set we add an array to the events array
        if (!isset($this->_events[$eventName][$priority])) {
            return true;
        }
        
        // If the event handler isn't registred we return with true.
        if (!in_array($eventHandlerName, $this->_events[$eventName][$priority])) {
            return true;
        }
        
        // Remove the event handler from the array
        $index = array_search($eventHandlerName, $this->_events[$eventName][$priority]);
        unset($this->_events[$eventName][$priority][$index]);
        $this->_saveEvents();
        
        return true;
    }
    
    /**
     * Clears the events cache and reactivates the modules
     * to rebuild the cache.
     * 
     * @access public
     * @param boolean $reactivateModules
     */
    public function clearCache($reactivateModules = true)
    {
        $this->_events = array();
        
        if ($reactivateModules) {
            $this->_framework->getModuleManager()->reactivateModules();
        }
    }
    
    /**
     * Saves the registred events in the object backend.
     * 
     * @access protected
     */
    protected function _saveEvents()
    {
        $this->_eventObjectBackend->saveObject($this->_events);
    }
}
