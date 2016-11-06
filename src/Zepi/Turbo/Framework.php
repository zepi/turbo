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

use \Zepi\Turbo\Manager\DataSourceManager;
use \Zepi\Turbo\Manager\ModuleManager;
use \Zepi\Turbo\Manager\RuntimeManager;
use \Zepi\Turbo\Manager\RouteManager;
use \Zepi\Turbo\Manager\RequestManager;
use \Zepi\Turbo\Manager\DependencyInjectionManager;
use \Zepi\Turbo\Request\RequestAbstract;
use \Zepi\Turbo\Response\Response;
use Zepi\Turbo\Manager\Zepi\Turbo\Manager;

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
    protected $rootDirectory;
    
    /**
     * @access protected
     * @var array
     */
    protected $moduleDirectories = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $moduleNamespaces = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $modules = array();
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Manager\DataSourceManager
     */
    protected $dataSourceManager;

    /**
     * @access protected
     * @var \Zepi\Turbo\Manager\ModuleManager
     */
    protected $moduleManager;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Manager\RuntimeManager
     */
    protected $runtimeManager;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Manager\RouteManager
     */
    protected $routeManager;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Manager\RequestManager
     */
    protected $requestManager;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Manager\DependencyInjectionManager
     */
    protected $dependencyInjectionManager;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Request\RequestAbstract
     */
    protected $request;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Response\Response
     */
    protected $response;
    
    /**
     * Constructs the object
     * 
     * @access private
     * @param string $rootDirectory
     */
    private function __construct($rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }
    
    /**
     * Returns a instance of the Framework
     * 
     * @static
     * @access public
     * @param string $rootDirectory
     * @return Framework
     */
    public static function getFrameworkInstance($rootDirectory)
    {
        if (self::$instance === null) {
            self::$instance = new Framework($rootDirectory);
            self::$instance->initializeFramework();
        }
        
        return self::$instance;
    }
    
    /**
     * Resets the framework
     * 
     * @static
     * @access public
     */
    public static function resetFramework()
    {
        self::$instance = null;
    }
    
    /**
     * Returns the path to the framework directory.
     * 
     * @access public
     * @return string
     */
    public function getRootDirectory()
    {
        return $this->rootDirectory;
    }
    
    /**
     * Initializes the framework and creates all needed managers.
     * 
     * @access protected
     */
    protected function initializeFramework()
    {
        $this->registerAutoloader();

        $this->dependencyInjectionManager = new DependencyInjectionManager($this);
        
        $this->requestManager = new RequestManager($this);
        $this->request = $this->requestManager->buildRequest();
        
        $this->response = new Response($this->request);
        
        $this->dataSourceManager = new DataSourceManager($this, $this->getInstance('Zepi\\Turbo\\Backend\\VirtualDataSourceBackend'));
        $this->dataSourceManager->initializeDataSourceManager();
        
        $this->moduleManager = new ModuleManager($this, $this->getInstance('Zepi\\Turbo\\Backend\\VirtualModuleBackend'));
        $this->moduleManager->initializeModuleSystem();
        
        $this->runtimeManager = new RuntimeManager($this, $this->getInstance('Zepi\\Turbo\\Backend\\VirtualHandlerBackend'));
        $this->runtimeManager->initializeManager();
        
        $this->routeManager = new RouteManager($this, $this->getInstance('Zepi\\Turbo\\Backend\\VirtualRouteBackend'));
        $this->routeManager->initializeRoutingTable();
    }
    
    /**
     * Returns the data source manager for the framework
     *
     * @access public
     * @return \Zepi\Turbo\Manager\DataSourceManager
     */
    public function getDataSourceManager()
    {
        return $this->dataSourceManager;
    }

    /**
     * Returns the module manager for the framework
     * 
     * @access public
     * @return \Zepi\Turbo\Manager\ModuleManager
     */
    public function getModuleManager()
    {
        return $this->moduleManager;
    }
    
    /**
     * Returns the runtime manager for the framework
     * 
     * @access public
     * @return \Zepi\Turbo\Manager\RuntimeManager
     */
    public function getRuntimeManager()
    {
        return $this->runtimeManager;
    }
    
    /**
     * Returns the route manager for the framework 
     * 
     * @access public
     * @return \Zepi\Turbo\Manager\RouteManager
     */
    public function getRouteManager()
    {
        return $this->routeManager;
    }
    
    /**
     * Returns the RequestManager object
     * 
     * @access public
     * @return \Zepi\Turbo\Manager\RequestManager
     */
    public function getRequestManager()
    {
        return $this->requestManager;
    }
    
    /**
     * Returns the DependencyInjectionManager object
     *
     * @access public
     * @return \Zepi\Turbo\Manager\DependencyInjectionManager
     */
    public function getDependencyInjectionManager()
    {
        return $this->dependencyInjectionManager;
    }
    
    /**
     * Returns the request object for the request
     * 
     * @access public
     * @return \Zepi\Turbo\Request\RequestAbstract
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Returns the response for the request
     * 
     * @access public
     * @return \Zepi\Turbo\Response\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    /**
     * Registers the global autloader.
     * 
     * @access protected
     */
    protected function registerAutoloader()
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
    public static function prepareClassName($className)
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
    public static function prepareNamespace($namespace)
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
        $module = false;
        $className = self::prepareClassName($className);
        
        if (strpos($className, '\\Zepi\\Turbo\\') === 0) {
            $sourceDirectory = realpath(__DIR__ . '/../../');
            
            // The class is from the framework, so we load the class file from the framework directory
            $path = $sourceDirectory . str_replace('\\', '/', $className) . '.php';
        } else {
            // The class isn't from the framework, so we need the module for the given class name
            $module = $this->moduleManager->getModuleByClassName($className);
            
            if ($module !== false) {
                $fileName = substr($className, strlen($module->getNamespace()));
                $path = $module->getDirectory() . '/src/' . str_replace('\\', '/', $fileName) . '.php';
            }
        }

        if ($path !== false && file_exists($path)) {
            require_once($path);
        } else if ($module !== false) {
            throw new Exception('Cannot find the class "' . $className . '"!');
        }
    }
    
    /**
     * Initiates the given class name
     * 
     * @param string $className
     * @param array $additionalParameters
     * @return object
     */
    public function initiateObject($className, $additionalParameters = array())
    {
        return $this->dependencyInjectionManager->initiateObject($className, $additionalParameters);
    }
    
    /**
     * Returns an instance of an object. If the module for the object
     * isn't initialized, the function will load the module and 
     * initialize the module.
     * 
     * @access public
     * @param string $className
     * @return mixed
     * 
     * @throws \Zepi\Turbo\Exception Cannot find the module for the given class name.
     * @throws \Zepi\Turbo\Exception Instance isn't an object!
     */
    public function getInstance($className)
    {
        $className = self::prepareClassName($className);
        
        if (strpos($className, '\\Zepi\\Turbo') === 0) {
            $instance = $this->getCoreInstance($className);
        } else {
            $module = $this->moduleManager->getModuleByClassName($className);
            
            if ($module === false) {
                throw new Exception('Cannot find the module for the given class name "' . $className . '".');
            }
            
            $instance = $module->getInstance($className);
        }
        
        if (!is_object($instance)) {
            throw new Exception('Instance for class name "' . $className . '" isn\'t an object!');
        }
        
        return $instance;
    }
    
    /**
     * Returns the instance of a framework object
     * 
     * @access protected
     * @param string $className
     * @return mixed
     * 
     * @throws \Zepi\Turbo\Exception Class "{className}" is not defined.
     */
    protected function getCoreInstance($className)
    {
        $className = self::prepareClassName($className);

        switch ($className) {
            case '\\Zepi\\Turbo\\Backend\\VirtualModuleBackend':
                $path = $this->rootDirectory . '/data/modules.data';
                return new \Zepi\Turbo\Backend\FileObjectBackend($path);
                break;
            case '\\Zepi\\Turbo\\Backend\\VirtualHandlerBackend':
                $path = $this->rootDirectory . '/data/handlers.data';
                return new \Zepi\Turbo\Backend\FileObjectBackend($path);
                break;
            case '\\Zepi\\Turbo\\Backend\\VirtualRouteBackend':
                $path = $this->rootDirectory . '/data/routes.data';
                return new \Zepi\Turbo\Backend\FileObjectBackend($path);
                break;
            case '\\Zepi\\Turbo\\Backend\\VirtualDataSourceBackend':
                $path = $this->rootDirectory . '/data/data-sources.data';
                return new \Zepi\Turbo\Backend\FileObjectBackend($path);
                break;
            default:
                if (class_exists($className, true)) {
                    return new $className();
                } else {
                    throw new Exception('Class "' . $className . '" is not defined.');
                }
                break;
        }
    }
    
    /**
     * Executes the framework. This executes the pre and post execution events.
     * Between these two events we call the correct request event. The 
     * routing table from the RouteManager returns the needed event name.
     * 
     * @access public
     */
    public function execute()
    {
        // Execute the before execution event
        $this->runtimeManager->executeEvent('\\Zepi\\Turbo\\Event\\BeforeExecution');
        
        // Get the event name for the request and execute the event
        $eventName = $this->routeManager->getEventNameForRoute($this->request);
        $eventName = $this->runtimeManager->executeFilter('\\Zepi\\Turbo\\Filter\\VerifyEventName', $eventName);

        if ($eventName !== false && $eventName != '') {
            $this->runtimeManager->executeEvent($eventName);
        } else {
            $this->runtimeManager->executeEvent('\\Zepi\\Turbo\\Event\\RouteNotFound');
        }
        
        // Execute the after execution event
        $this->runtimeManager->executeEvent('\\Zepi\\Turbo\\Event\\AfterExecution');

        // Finalize the output
        $this->runtimeManager->executeEvent('\\Zepi\\Turbo\\Event\\FinalizeOutput');
        
        // Execute the before output event
        $this->runtimeManager->executeEvent('\\Zepi\\Turbo\\Event\\BeforeOutput');
        
        // Print the output
        echo $this->response->getOutput();
        
        // Execute the after output event
        $this->runtimeManager->executeEvent('\\Zepi\\Turbo\\Event\\AfterOutput');
    }
}
