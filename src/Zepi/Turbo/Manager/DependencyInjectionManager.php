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
 * The DependenyInjectionManager manages the initiation of new
 * objects. The manager will analyze the construct method of the
 * given class name and loads the needed objects for the construction.
 * 
 * @package Zepi\Turbo\Manager
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2016 zepi
 */

namespace Zepi\Turbo\Manager;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Exception;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * The DependenyInjectionManager manages the initiation of new
 * objects. The manager will analyze the construct method of the
 * given class name and loads the needed objects for the construction.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2016 zepi
 */
class DependencyInjectionManager
{
    /**
     * @access protected
     * @var Framework
     */
    protected $framework;
    
    /**
     * @var array
     */
    protected $sharedInstances = array();
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     */
    public function __construct(Framework $framework)
    {
        $this->framework = $framework;
    }
    
    /**
     * Initiates the given class name
     *
     * @param string $className
     * @param array $additionalParameters
     * @param boolean $shared
     * @return object
     */
    public function initiateObject($className, $additionalParameters = array(), $shared = false)
    {
        if (isset($this->sharedInstances[$className])) {
            return $this->sharedInstances[$className];
        }
        
        $reflection = new ReflectionClass($className);
        
        if ($reflection->hasMethod('__construct')) {
            $constructor = $reflection->getConstructor();
            $parameters = $this->prepareParameters($constructor, $additionalParameters);
            
            $instance = $reflection->newInstanceArgs($parameters);
        } else {
            $instance = new $className();
        }
        
        if ($shared) {
            $this->sharedInstances[$className] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * Prepares the parameters for the given constructor
     * 
     * @param \ReflectionMethod $constructor
     * @param array $additionalParameters
     * @return array
     * 
     * @throws \Zepi\Turbo\Exception Cannot find correct value for parameter "{parameterName}" in class "{className}". 
     */
    protected function prepareParameters(ReflectionMethod $constructor, $additionalParameters)
    {
        $parameters = array();
        foreach ($constructor->getParameters() as $parameter) {
            $parameterValue = null;
        
            if (isset($additionalParameters[$parameter->name])) {
                $parameterValue = $additionalParameters[$parameter->name];
            } else if ($parameter->getClass() !== null) {
                $parameterValue = $this->getInstance($parameter->getClass());
            }
        
            if ($parameterValue === null) {
                throw new Exception('Cannot find correct value for parameter "' . $parameter->name . '" in class "' . $constructor->class . '".');
            }
        
            $parameters[] = $parameterValue;
        }
        
        return $parameters;
    }
    
    /**
     * Returns the instance for the given class
     * 
     * @param \ReflectionClass $parameterClass
     * @return null|object
     */
    protected function getInstance(ReflectionClass $parameterClass)
    {
        if (!class_exists($parameterClass->name, true)) {
            return null;
        }
        
        if (strpos($parameterClass->name, 'Zepi\\Turbo\\') === 0) {
            return $this->getFrameworkInstance($parameterClass);
        }
        
        if ($parameterClass->isInstantiable()) {
            return $this->framework->getInstance($parameterClass->name);
        }
    }
    
    /**
     * Returns the instance for the given class if the class path
     * starts with Zepi\Turbo.
     * 
     * @param \ReflectionClass $parameterClass
     * @return object
     */
    protected function getFrameworkInstance(ReflectionClass $parameterClass)
    {
        if ($parameterClass->name == 'Zepi\\Turbo\\Framework') {
            return $this->framework;
        }
        
        if ($parameterClass->name == 'Zepi\\Turbo\\Request\\RequestAbstract') {
            return $this->framework->getRequest();
        }
        
        if ($parameterClass->name == 'Zepi\\Turbo\\Response\\Response') {
            return $this->framework->getResponse();
        }
        
        if ($parameterClass->getNamespaceName() === 'Zepi\\Turbo\\Manager') {
            return $this->getFrameworkManager($parameterClass->name);
        }
    }
    
    /**
     * Returns the framework manager for the given class name
     * 
     * @param string $className
     * @return object
     * 
     * @throws \Zepi\Turbo\Exception Cannot find framework manager "{className}".
     */
    protected function getFrameworkManager($className)
    {
        switch ($className) {
            case 'Zepi\\Turbo\\Manager\\DataSourceManager':
                return $this->framework->getDataSourceManager();
            break;
            
            case 'Zepi\\Turbo\\Manager\\DependencyInjectionManager':
                return $this->framework->getDependencyInjectionManager();
            break;
                
            case 'Zepi\\Turbo\\Manager\\ModuleManager':
                return $this->framework->getModuleManager();
            break;
                
            case 'Zepi\\Turbo\\Manager\\RequestManager':
                return $this->framework->getRequestManager();
            break;
                
            case 'Zepi\\Turbo\\Manager\\RouteManager':
                return $this->framework->getRouteManager();
            break;
            
            case 'Zepi\\Turbo\\Manager\\RuntimeManager':
                return $this->framework->getRuntimeManager();
            break;
            
            default:
                throw new Exception('Cannot find framework manager "' . $className . '".');
            break;
        }
    }
}
