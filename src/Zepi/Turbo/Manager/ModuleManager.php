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
 * The ModuleManager manages all modules in the framework. Please do
 * not create an instance of the module manager. Use the framework to get 
 * the ModuleManager instance.
 * 
 * @package Zepi\Turbo\Manager
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Manager;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Backend\ObjectBackendAbstract;
use \Zepi\Turbo\Exception;
use \Zepi\Turbo\Module\ModuleAbstract;

/**
 * The ModuleManager manages all modules in the framework. Please do
 * not create an instance of the module manager. Use the framework to get 
 * the ModuleManager instance.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class ModuleManager
{
    /**
     * @access protected
     * @var Framework
     */
    protected $framework;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Backend\ObjectBackendAbstract
     */
    protected $moduleObjectBackend;
    
    /**
     * @access protected
     * @var array
     */
    protected $moduleDirectories = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $activatedModules = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $modules = array();

    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param \Zepi\Turbo\Backend\ObjectBackendAbstract $moduleObjectBackend
     */
    public function __construct(Framework $framework, ObjectBackendAbstract $moduleObjectBackend)
    {
        $this->framework = $framework;
        $this->moduleObjectBackend = $moduleObjectBackend;
    }
    
    /**
     * Initializes the module system. Loads the activated modules from the 
     * object backend and loads all modules.
     * 
     * @access public
     */
    public function initializeModuleSystem()
    {
        $activatedModules = $this->moduleObjectBackend->loadObject();
        if (!is_array($activatedModules)) {
            $activatedModules = array();
        }
        
        $this->activatedModules = $activatedModules;
        
        foreach ($this->activatedModules as $activatedModule) {
            $this->initializeModule($activatedModule['path']);
        }
    }
    
    /**
     * Adds a directory as module directory
     * 
     * @access public
     * @param string $directory
     * @param string $excludePattern
     * @return boolean
     */
    public function registerModuleDirectory($directory, $excludePattern = '/\/tests\//')
    {
        if (!isset($this->moduleDirectories[$directory])) {
            $this->moduleDirectories[$directory] = $excludePattern;
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns all activated modules.
     * 
     * @access public
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }
    
    /**
     * Returns the module for the given module namespace or false, 
     * if the module wasn't initialized.
     * 
     * @access public
     * @param string $namespace
     * @return ModuleAbstract|boolean
     */
    public function getModule($namespace)
    {
        if (!isset($this->modules[$namespace])) {
            return false;
        }
        
        return $this->modules[$namespace];
    }
    
    /**
     * Searches and activates the module with the given 
     * namespace. The register method of the module will 
     * be executed.
     * 
     * @access public
     * @param string $namespace
     * @param boolean $activateDependencies
     * @return boolean
     * 
     * @throws Zepi\Turbo\Exception Can not find the module "$namespace".
     */
    public function activateModule($namespace, $activateDependencies = false)
    {
        $namespace = Framework::prepareNamespace($namespace);

        // If the module already is activated we won't activate it again
        if (isset($this->activatedModules[$namespace])) {
            return true;
        }
        
        // Search the path for the module and initialize it
        $path = $this->searchModulePath($namespace);
        if ($path === false) {
            throw new Exception('Can not find the module "' . $namespace . '".');
        }
        
        // Get the version
        $moduleProperties = $this->parseModuleJson($path);
        $version = $moduleProperties->module->version;
        
        $module = $this->initializeModule($path, $activateDependencies);
        $module->activate($version, 0);
        
        // Save the path in the activated modules array
        $this->activatedModules[$namespace] = array('version' => $version, 'path' => $path);
        $this->saveActivatedModules();
        
        return true;
    }
    
    /**
     * Deactivates an activated module. The deregister method
     * of the module will be executed.
     * 
     * @access public
     * @param string $namespace
     * @return boolean
     */
    public function deactivateModule($namespace)
    {
        $namespace = Framework::prepareNamespace($namespace);

        // If the module isn't activated we have nothing to deactivate
        if (!isset($this->activatedModules[$namespace])) {
            return false;
        }
        
        // Load the module to deactivate it
        $namespace = Framework::prepareNamespace($namespace);
        $module = $this->getModule($namespace);

        // If the module isn't initialized it isn't active
        if ($module === false) {
            return false;
        }
        
        // Deactivate the module
        $module->deactivate();
        
        // Remove the module and save the module cache
        unset($this->activatedModules[$namespace]);
        unset($this->modules[$namespace]);
        $this->saveActivatedModules();
        
        return true;
    }
    
    /**
     * Searches the module for the given class name.
     * 
     * @access public
     * @param string $className
     * @return boolean|Module
     */
    public function getModuleByClassName($className)
    {
        $longest = 0;
        $foundModule = false;
        
        $className = Framework::prepareClassName($className);
        
        foreach ($this->modules as $moduleNamespace => $module) {
            $moduleNamespace = Framework::prepareNamespace($moduleNamespace);
            $sameNamespace = strpos($className, $moduleNamespace);
            $length = strlen($moduleNamespace);

            if ($sameNamespace !== false && $length > $longest) {
                $longest = $length;
                $foundModule = $module;
            }
        }
        
        return $foundModule;
    }
    
    /**
     * Iterates trough the modules and activates each of the
     * modules.
     * 
     * @access public
     */
    public function reactivateModules()
    {
        foreach ($this->modules as $module) {
            $moduleProperties = $this->parseModuleJson($module->getDirectory());
            $version = $moduleProperties->module->version;

            $this->activateModule($moduleProperties->module->namespace, true);
            $module->activate($version, $version);
        }
    }
    
    /**
     * Returns an object with the properties for the given path.
     * 
     * @access public
     * @param string $path
     * @return \stdClass
     */
    public function getModuleProperties($path)
    {
        return $this->parseModuleJson($path);
    }
    
    /**
     * Returns an object with the properties of the module from
     * the Module.json file in the given path.
     * 
     * @access protected
     * @param string $path
     * @return \stdClass
     * 
     * @throws Zepi\Turbo\Exception Cannot find Module.json in the path "$path".
     */
    protected function parseModuleJson($path)
    {
        if (!file_exists($path . '/Module.json')) {
            throw new Exception('Cannot find Module.json in the path "' . $path . '".');
        }

        $moduleProperties = json_decode(file_get_contents($path . '/Module.json'));
        
        return $moduleProperties;
    }
    
    /**
     * Returns the namespace for the module in the given path.
     * 
     * @access protected
     * @param string $path
     * @return string
     * 
     * @throws Zepi\Turbo\Exception The namespace is not set in the module properties for the Module in "$path".
     */
    protected function getNamespaceFromModuleJson($path)
    {
        $moduleProperties = $this->parseModuleJson($path);

        if (!isset($moduleProperties->module->namespace)) {
            throw new Exception('The namespace is not set in the module properties for the module in "' . $path . '".');
        }
        
        return Framework::prepareNamespace($moduleProperties->module->namespace);
    }
    
    /**
     * Initializes the module. This creates an new Module object if the 
     * given module path is valid. The function returns the initialized 
     * module or false, if the module can't be initialized.
     * 
     * @access protected
     * @param string $path
     * @param boolean $activateDependencies
     * @return ModuleAbstract
     * 
     * @throws Zepi\Turbo\Exception The module "$path" is not valid
     */
    protected function initializeModule($path, $activateDependencies = false)
    {
        $moduleNamespace = $this->getNamespaceFromModuleJson($path);
        $module = $this->getModule($moduleNamespace);
        
        // If the module is already initialized, return it
        if ($module instanceof ModuleAbstract) {
            return $module;
        }
        
        // Try to find and load the module
        if (!file_exists($path . '/Module.php')) {
            throw new Exception('The module "' . $path . '" is not valid!');
        }

        // Look for dependencies and warn the user or activate the dependencies
        $this->handleModuleDependencies($moduleNamespace, $path, $activateDependencies);
        
        // Load the module
        require_once($path . '/Module.php');
        $moduleClassName = Framework::prepareClassName($moduleNamespace . 'Module');
        
        // Initialize the module
        $module = new $moduleClassName($this->framework, $moduleNamespace, $path);
        $this->modules[$moduleNamespace] = $module;
        
        $module->initialize();
        
        return $module;
    }

    /**
     * Loads the Module.json and checks it for dependencies. If the module has
     * dependencies the function will verify the modules and activate them
     * if the parameter $activateDependencies is set to true.
     * 
     * @access public
     * @param string $moduleNamespace
     * @param string $path
     * @param boolean $activateDependencies
     */
    protected function handleModuleDependencies($moduleNamespace, $path, $activateDependencies)
    {
        $moduleProperties = $this->parseModuleJson($path);

        // If the config file has no dependencies we have nothing to do...
        if (!isset($moduleProperties->dependencies) || $moduleProperties->dependencies === null) {
            return;
        }
        
        foreach ($moduleProperties->dependencies as $type => $dependencies) {
            switch ($type) {
                case 'required':
                    $this->handleRequiredDependencies($moduleNamespace, $dependencies, $activateDependencies);
                break;
            }
        }
    }

    /**
     * Handles all required dependencies.
     * 
     * @access public
     * @param string $moduleNamespace
     * @param array $dependencies
     * @param boolean $activateDependencies
     * 
     * @throws Zepi\Turbo\Exception Can not activate the module "$moduleNamespace". The module requires the module "$dependencyModuleNamespace" which isn't activated.
     */
    protected function handleRequiredDependencies($moduleNamespace, $dependencies, $activateDependencies)
    {
        foreach ($dependencies as $dependencyModuleNamespace => $version) {
            $dependencyModuleNamespace = Framework::prepareNamespace($dependencyModuleNamespace);
            
            $module = $this->getModule($dependencyModuleNamespace);
            if ($module === false) {
                if ($activateDependencies) {
                    $this->activateModule($dependencyModuleNamespace, $activateDependencies);
                } else {
                    throw new Exception('Can not activate the module "' . $moduleNamespace . '". The module requires the module "' . $dependencyModuleNamespace . '" which isn\'t activated.');
                }
            }
        }
    }
    
    /**
     * Iterates over the available module directories and searches the 
     * target namespace in the module directories. If the namespace is 
     * found the function return the path to the module.
     * 
     * @access protected
     * @param string $namespace
     * @return string
     */
    protected function searchModulePath($namespace)
    {
        $targetPath = false;
        
        // Iterate trough the module directories
        foreach ($this->moduleDirectories as $directory => $excludePattern) {
            $recursiveDirectoryIterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
            $iterator = new \RecursiveIteratorIterator($recursiveDirectoryIterator);
            $regexIterator = new \RegexIterator($iterator, '/^.+\/Module\.json$/i');

            foreach ($regexIterator as $item) {
                // Ignore modules which are located inside a tests directory
                if ($excludePattern !== false && preg_match($excludePattern, $item->getPath())) {
                    continue;
                }
                
                $moduleNamespace = $this->getNamespaceFromModuleJson($item->getPath());
                
                if ($moduleNamespace === $namespace) {
                    $targetPath = $item->getPath();
                    break 2;
                }
            }
        }
        
        return $targetPath;
    }
    
    /**
     * Saves the activated modules in the object backend.
     * 
     * @access protected
     */
    protected function saveActivatedModules()
    {
        $this->moduleObjectBackend->saveObject($this->activatedModules);
    }
}
