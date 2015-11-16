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
    protected $_framework;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Backend\ObjectBackendAbstract
     */
    protected $_moduleObjectBackend;
    
    /**
     * @access protected
     * @var array
     */
    protected $_moduleDirectories = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $_activatedModules = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $_modules = array();

    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param \Zepi\Turbo\Backend\ObjectBackendAbstract $moduleObjectBackend
     */
    public function __construct(Framework $framework, ObjectBackendAbstract $moduleObjectBackend)
    {
        $this->_framework = $framework;
        $this->_moduleObjectBackend = $moduleObjectBackend;
    }
    
    /**
     * Initializes the module system. Loads the activated modules from the 
     * object backend and loads all modules.
     * 
     * @access public
     */
    public function initializeModuleSystem()
    {
        $activatedModules = $this->_moduleObjectBackend->loadObject();
        if (!is_array($activatedModules)) {
            $activatedModules = array();
        }
        
        $this->_activatedModules = $activatedModules;
        
        foreach ($this->_activatedModules as $activatedModule) {
            $this->_initializeModule($activatedModule['path']);
        }
    }
    
    /**
     * Adds a directory as module directory
     * 
     * @access public
     * @param string $directory
     * @return boolean
     */
    public function registerModuleDirectory($directory)
    {
        if (!in_array($directory, $this->_moduleDirectories)) {
            $this->_moduleDirectories[] = $directory;
            
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
        return $this->_modules;
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
        if (!isset($this->_modules[$namespace])) {
            return false;
        }
        
        return $this->_modules[$namespace];
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
        if (isset($this->_activatedModules[$namespace])) {
            return true;
        }
        
        // Search the path for the module and initialize it
        $path = $this->_searchModulePath($namespace);
        if ($path === false) {
            throw new Exception('Can not find the module "' . $namespace . '".');
        }
        
        // Get the version
        $moduleProperties = $this->_parseModuleIni($path);
        $version = $moduleProperties['module']['version'];
        
        $module = $this->_initializeModule($path, $activateDependencies);
        $module->activate($version, 0);
        
        // Save the path in the activated modules array
        $this->_activatedModules[$namespace] = array('version' => $version, 'path' => $path);
        $this->_saveActivatedModules();
        
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
        if (!isset($this->_activatedModules[$namespace])) {
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
        unset($this->_activatedModules[$namespace]);
        unset($this->_modules[$namespace]);
        $this->_saveActivatedModules();
        
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
        
        foreach ($this->_modules as $moduleNamespace => $module) {
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
        foreach ($this->_modules as $module) {
            $moduleProperties = $this->_parseModuleIni($module->getDirectory());
            $version = $moduleProperties['module']['version'];

            $this->activateModule($moduleProperties['module']['namespace'], true);
            $module->activate($version, $version);
        }
    }
    
    /**
     * Returns an array with the properties for the given path.
     * 
     * @access public
     * @param string $path
     * @return array
     */
    public function getModuleProperties($path)
    {
        return $this->_parseModuleIni($path);
    }
    
    /**
     * Returns an array with the properties of the module from
     * the Module.ini file in the given path.
     * 
     * @access protected
     * @param string $path
     * @return array
     * 
     * @throws Zepi\Turbo\Exception Cannot find Module.ini in the path "$path".
     */
    protected function _parseModuleIni($path)
    {
        if (!file_exists($path . '/Module.ini')) {
            throw new Exception('Cannot find Module.ini in the path "' . $path . '".');
        }
        
        $moduleProperties = parse_ini_file($path . '/Module.ini', true);
        
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
    protected function _getNamespaceFromModuleIni($path)
    {
        $moduleProperties = $this->_parseModuleIni($path);
        
        if (!isset($moduleProperties['module']['namespace'])) {
            throw new Exception('The namespace is not set in the module properties for the module in "' . $path . '".');
        }
        
        return Framework::prepareNamespace($moduleProperties['module']['namespace']);
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
    protected function _initializeModule($path, $activateDependencies = false)
    {
        $moduleNamespace = $this->_getNamespaceFromModuleIni($path);
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
        $this->_handleModuleDependencies($moduleNamespace, $path, $activateDependencies);
        
        // Load the module
        require_once($path . '/Module.php');
        $moduleClassName = Framework::prepareClassName($moduleNamespace . 'Module');
        
        // Initialize the module
        $module = new $moduleClassName($this->_framework, $moduleNamespace, $path);
        $this->_modules[$moduleNamespace] = $module;
        
        $module->initialize();
        
        return $module;
    }

    /**
     * Loads the module.ini and checks it for dependencies. If the module has
     * dependencies the function will verify the modules and activate them
     * if the parameter $activateDependencies is set to true.
     * 
     * @access public
     * @param string $moduleNamespace
     * @param string $path
     * @param boolean $activateDependencies
     */
    protected function _handleModuleDependencies($moduleNamespace, $path, $activateDependencies)
    {
        $moduleProperties = $this->_parseModuleIni($path);

        // If the ini file has no dependencies we have nothing to do...
        if (!isset($moduleProperties['dependencies']) || count($moduleProperties['dependencies']) === 0) {
            return;
        }
        
        foreach ($moduleProperties['dependencies'] as $type => $dependencies) {
            switch ($type) {
                case 'required':
                    $this->_handleRequiredDependencies($moduleNamespace, $dependencies, $activateDependencies);
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
    protected function _handleRequiredDependencies($moduleNamespace, $dependencies, $activateDependencies)
    {
        foreach ($dependencies as $dependencyModuleNamespace) {
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
    protected function _searchModulePath($namespace)
    {
        $targetPath = false;
        
        // Iterate trough the module directories
        foreach ($this->_moduleDirectories as $directory) {
            $recursiveDirectoryIterator = new \RecursiveDirectoryIterator($directory);
            $iterator = new \RecursiveIteratorIterator($recursiveDirectoryIterator);
            $regexIterator = new \RegexIterator($iterator, '/^.+\/Module\.ini$/i');
            
            foreach ($regexIterator as $item) {
                $moduleNamespace = $this->_getNamespaceFromModuleIni($item->getPath());
                
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
    protected function _saveActivatedModules()
    {
        $this->_moduleObjectBackend->saveObject($this->_activatedModules);
    }
}
