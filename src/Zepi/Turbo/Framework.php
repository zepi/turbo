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
 * The Framework object delivers the root functionality for
 * zepi Turbo.
 * 
 * @package Zepi\Turbo
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo;

use \Zepi\Turbo\Manager\ModuleManager;
use \Zepi\Turbo\Manager\EventManager;
use \Zepi\Turbo\Manager\RouteManager;
use \Zepi\Turbo\Manager\RequestManager;
use \Zepi\Turbo\Request\RequestAbstract;
use \Zepi\Turbo\Response\Response;

/**
 * The Framework object delivers the root functionality for
 * zepi Turbo.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class Framework
{
    /**
     * @static
     * @access protected
     * @var Framework
     */
    static protected $instance;
    
    /**
     * @access protected
     * @var string
     */
    protected $_rootDirectory;
    
    /**
     * @access protected
     * @var array
     */
    protected $_moduleDirectories = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $_moduleNamespaces = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $_modules = array();

    /**
     * @access protected
     * @var ModuleManager
     */
    protected $_moduleManager;
    
    /**
     * @access protected
     * @var EventManager
     */
    protected $_eventManager;
    
    /**
     * @access protected
     * @var RouteManager
     */
    protected $_routeManager;
    
    /**
     * @access protected
     * @var RequestManager
     */
    protected $_requestManager;
    
    /**
     * @access protected
     * @var RequestAbstract
     */
    protected $_request;
    
    /**
     * @access protected
     * @var Response
     */
    protected $_response;
    
    /**
     * Constructs the object
     * 
     * @access private
     * @param string $rootDirectory
     */
    private function __construct($rootDirectory)
    {
        $this->_rootDirectory = $rootDirectory;
    }
    
    /**
     * Returns a instance of the Framework
     * 
     * @static
     * @access public
     * @param string $rootDirectory
     * @return Framework
     */
    static public function getFrameworkInstance($rootDirectory)
    {
        if (self::$instance === null) {
            self::$instance = new Framework($rootDirectory);
            self::$instance->_initializeFramework();
        }
        
        return self::$instance;
    }
    
    /**
     * Returns the path to the framework directory.
     * 
     * @access public
     * @return string
     */
    public function getRootDirectory()
    {
        return $this->_rootDirectory;
    }
    
    /**
     * Initializes the framework and creates all needed managers.
     * 
     * @access protected
     */
    protected function _initializeFramework()
    {
        $this->_registerAutoloader();
        
        $this->_requestManager = new RequestManager($this);
        $this->_request = $this->_requestManager->buildRequest();
        
        $this->_response = new Response($this->_request);
        
        $this->_moduleManager = new ModuleManager($this, $this->getInstance('Zepi\\Turbo\\Backend\\VirtualModuleBackend'));
        $this->_moduleManager->initializeModuleSystem();
        
        $this->_eventManager = new EventManager($this, $this->getInstance('Zepi\\Turbo\\Backend\\VirtualEventBackend'));
        $this->_eventManager->initializeEventSystem();
        
        $this->_routeManager = new RouteManager($this, $this->getInstance('Zepi\\Turbo\\Backend\\VirtualRouteBackend'));
        $this->_routeManager->initializeRoutingTable();
    }

    /**
     * Returns the module manager for the framework
     * 
     * @access public
     * @return ModuleManager
     */
    public function getModuleManager()
    {
        return $this->_moduleManager;
    }
    
    /**
     * Returns the event manager for the framework
     * 
     * @access public
     * @return EventManager
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }
    
    /**
     * Returns the route manager for the framework 
     * 
     * @access public
     * @return RouteManager
     */
    public function getRouteManager()
    {
        return $this->_routeManager;
    }
    
    /**
     * Returns the RequestManager object
     * 
     * @access public
     * @return RequestManager
     */
    public function getRequestManager()
    {
        return $this->_requestManager;
    }
    
    /**
     * Returns the request object for the request
     * 
     * @access public
     * @return RequestAbstract
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * Returns the response for the request
     * 
     * @access public
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * Registers the global autloader.
     * 
     * @access protected
     */
    protected function _registerAutoloader()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }
    
    /**
     * Prepares the class name and adds a backslash in front
     * of the class name if there isn't a bachslash.
     * 
     * @static
     * @access public
     * @param string $className
     * @return string
     */
    static public function prepareClassName($className)
    {
        if (substr($className, 0, 1) !== '\\') {
            $className = '\\' . $className;
        }
        
        return $className;
    }
    
    /**
     * Prepares the namespace and adds on both sides of the
     * namespace the backslashes.
     * 
     * @static
     * @access public
     * @param string $namespace
     * @return string
     */
    static public function prepareNamespace($namespace)
    {
        if (substr($namespace, 0, 1) !== '\\') {
            $namespace = '\\' . $namespace;
        }
        
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }
        
        return $namespace;
    }
    
    /**
     * Framework autoloader: This function is called from the SPL Autoloader
     * to load the correct class. If the class isn't in the framework the
     * function will trying to load and initialize the module.
     * 
     * @access public
     * @param string $className
     * 
     * @throws \Exception Cannot find the class "$className"!
     */
    public function loadClass($className)
    {
        $path = false;
        $className = self::prepareClassName($className);
        
        if (strpos($className, '\\Zepi\\Turbo\\') === 0) {
            $sourceDirectory = realpath(__DIR__ . '/../../');
            
            // The class is from the framework, so we load the class file from the framework directory
            $path = $sourceDirectory . str_replace('\\', '/', $className) . '.php';
        } else {
            // The class isn't from the framework, so we need the module for the given class name
            $module = $this->_moduleManager->getModuleByClassName($className);
            
            if ($module !== false) {
                $fileName = substr($className, strlen($module->getNamespace()));
                $path = $module->getDirectory() . '/src/' . str_replace('\\', '/', $fileName) . '.php';
            }
        }

        if ($path !== false && file_exists($path)) {
            require_once($path);
        } else {
            throw new \Exception('Cannot find the class "' . $className . '"!');
        }
    }
    
    /**
     * Returns an instance of an object. If the module for the object
     * isn't initialized, the function will load the module and 
     * initialize the module.
     * 
     * @access public
     * @param string $className
     * 
     * @throws \Zepi\Turbo\Exception Cannot find the module for the given class name.
     * @throws \Zepi\Turbo\Exception Instance isn't an object!
     */
    public function getInstance($className)
    {
        $className = self::prepareClassName($className);
        
        if (strpos($className, '\\Zepi\\Turbo') === 0) {
            $instance = $this->_getInstance($className);
        } else {
            $module = $this->_moduleManager->getModuleByClassName($className);
            
            if ($module === false) {
                throw new Exception('Cannot find the module for the given class name "' . $className . '".');
            }
            
            $instance = $module->getInstance($className);
        }
        
        if (!is_object($instance)) {
            throw new Exception('Instance isn\'t an object!');
        }
        
        return $instance;
    }
    
    /**
     * Returns the instance of a framework object
     * 
     * @access protected
     * @param string $className
     * @return mixed
     */
    protected function _getInstance($className)
    {
        $className = self::prepareClassName($className);
        
        switch ($className) {
            case '\\Zepi\\Turbo\\Backend\\VirtualModuleBackend':
                $path = $this->_rootDirectory . '/data/modules.data';
                return new \Zepi\Turbo\Backend\FileObjectBackend($path);
            break;
            case '\\Zepi\\Turbo\\Backend\\VirtualEventBackend':
                $path = $this->_rootDirectory . '/data/events.data';
                return new \Zepi\Turbo\Backend\FileObjectBackend($path);
            break;
            case '\\Zepi\\Turbo\\Backend\\VirtualRouteBackend':
                $path = $this->_rootDirectory . '/data/routes.data';
                return new \Zepi\Turbo\Backend\FileObjectBackend($path);
            break;
            default:
                return new $className();
            break;
        }
    }
    
    /**
     * Executes the framework. This executes the pre and post execution events.
     * Between these two events we call the correct request event. The 
     * routing table from the RouteManager returns the needed event name.
     * 
     * @access protected
     */
    public function execute()
    {
        // Execute the before execution event
        $this->_eventManager->executeEvent('\\Zepi\\Turbo\\Event\\BeforeExecution');
        
        // Get the event name for the request and execute the event
        $eventName = $this->_routeManager->getEventNameForRoute($this->_request);
        
        if ($eventName !== false) {
            $this->_eventManager->executeEvent($eventName);
        } else {
            $this->_eventManager->executeEvent('\\Zepi\\Turbo\\Event\\RouteNotFound');
        }
        
        // Execute the after execution event
        $this->_eventManager->executeEvent('\\Zepi\\Turbo\\Event\\AfterExecution');

        // Finalize the output
        $this->_eventManager->executeEvent('\\Zepi\\Turbo\\Event\\FinalizeOutput');
        
        // Execute the before output event
        $this->_eventManager->executeEvent('\\Zepi\\Turbo\\Event\\BeforeOutput');
        
        // Print the output
        echo $this->_response->getOutput();
        
        // Execute the after output event
        $this->_eventManager->executeEvent('\\Zepi\\Turbo\\Event\\AfterOutput');
    }
}
