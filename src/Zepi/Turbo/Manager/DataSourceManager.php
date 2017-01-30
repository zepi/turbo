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
 * The DataSourceManager manages and delivers the available
 * data sources.
 * 
 * @package Zepi\Turbo\Manager
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Manager;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Exception;
use \Zepi\Turbo\Backend\ObjectBackendAbstract;

/**
 * The DataSourceManager manages and delivers the available
 * data sources.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class DataSourceManager
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
    protected $dataSourceObjectBackend;
    
    /**
     * @access protected
     * @var array
     */
    protected $dataSources = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $definitions = array();
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param \Zepi\Turbo\Backend\ObjectBackendAbstract $dataSourceObjectBackend
     */
    public function __construct(
        Framework $framework, 
        ObjectBackendAbstract $dataSourceObjectBackend
    ) {
        $this->framework = $framework;
        $this->dataSourceObjectBackend = $dataSourceObjectBackend;
    }
    
    /**
     * Initializes the data source manager. The function loads all saved
     * events from the object backend.
     *
     * @access public
     */
    public function initializeDataSourceManager()
    {
        $dataSources = $this->dataSourceObjectBackend->loadObject();
        if (!is_array($dataSources)) {
            $dataSources = array();
        }
    
        $this->dataSources = $dataSources;
    }
    
    /**
     * Adds a data source to the repository
     * 
     * @access public 
     * @param string $interfaceName
     * @param string $driver
     * @param string $className
     */
    public function addDataSource($interfaceName, $driver, $className)
    {
        if (!isset($this->dataSources[$interfaceName]) || !is_array($this->dataSources[$interfaceName])) {
            $this->dataSources[$interfaceName] = array();
        }
        
        $this->dataSources[$interfaceName][$driver] = $className;
        $this->saveDataSources();
        
        return true;
    }
    
    /**
     * Removes a data source from the repository
     * 
     * @access public
     * @param stirng $interfaceName
     * @param string $driver
     * @param string $className
     * @return boolean
     */
    public function removeDataSource($interfaceName, $driver, $className)
    {
        if (!isset($this->dataSources[$interfaceName][$driver])) {
            return false;
        }
        
        unset($this->dataSources[$interfaceName][$driver]);
        $this->saveDataSources();
        
        return true;
    }
    
    /**
     * Saves the registred data sources in the object backend.
     *
     * @access protected
     */
    protected function saveDataSources()
    {
        $this->dataSourceObjectBackend->saveObject($this->dataSources);
    }
    
    /**
     * Adds a definition
     * 
     * @access public
     * @param string $selector
     * @param string $driver
     */
    public function addDefinition($selector, $driver)
    {
        $this->definitions[$selector] = $driver;
    }
    
    /**
     * Removes a definition
     * 
     * @access public
     * @param string $selector
     * @return boolean
     */
    public function removeDefinition($selector)
    {
        if (!isset($this->definitions[$selector])) {
            return false;
        }
        
        unset($this->definitions[$selector]);
        return true;
    }
    
    /**
     * Returns the data source for the given type class.
     * 
     * @access public
     * @param string $typeClass
     * @return mixed
     * 
     * @throws \Zepi\Turbo\Exception Cannot find a driver for the given type class.
     * @throws \Zepi\Turbo\Exception Cannot find a data source for the given type class.
     */
    public function getDataSource($typeClass)
    {
        $driver = $this->getDriver($typeClass);
        
        // If there is no driver for the given type class throw an exception
        if ($driver === false) {
            throw new Exception('Cannot find a driver for the given type class "' . $typeClass . '".');
        }
        
        $dataSourceClass = $this->searchDataSourceClass($typeClass, $driver);

        // If there is no data source class for the given type class throw an exception
        if ($dataSourceClass === false) {
            throw new Exception('Cannot find a data source for the given type class "' . $typeClass . '" (selected driver: "' . $driver . '").');
        }
        
        return $this->framework->getInstance($dataSourceClass);
    }
    
    /**
     * Returns an array with all DataSource type classes
     * 
     * @access public
     * @return array
     */
    public function getDataSourceTypeClasses()
    {
        return array_keys($this->dataSources);
    }
    
    /**
     * Returns the driver for the given type class or false if no 
     * driver is available.
     * 
     * @access protected
     * @param string $typeClass
     * @return false|string
     */
    protected function getDriver($typeClass)
    {
        $bestDriver = false;
        $numberOfParts = 0;
        
        foreach ($this->definitions as $selector => $driver) {
            if ($selector === '*' || $selector === $typeClass) {
                $bestDriver = $driver;
                $numberOfParts = $this->countNumberOfParts($selector);
            } else if (substr($selector, -1) === '*') {
                $selectorWithoutWildcard = substr($selector, 0, -1);
                
                if (strpos($selector, $selectorWithoutWildcard) === 0 || $numberOfParts < $this->countNumberOfParts($selector)) {
                    $bestDriver = $driver;
                    $numberOfParts = $this->countNumberOfParts($selector);
                }
            }
        }
        
        return $bestDriver;
    }
    
    /**
     * Returns the number of parts
     * 
     * @access protected
     * @param string $selector
     * @return integer
     */
    protected function countNumberOfParts($selector)
    {
        $selector = trim($selector, '*\\');
        
        if ($selector === '') {
            return 0;
        }
        
        return substr_count($selector, '\\');
    }
    
    /**
     * Returns the DataSource class for the given type class and driver
     * @param string $typeClass
     * @param string $driver
     * @return false|string
     */
    protected function searchDataSourceClass($typeClass, $driver)
    {
        if (isset($this->dataSources[$typeClass][$driver])) {
            return $this->dataSources[$typeClass][$driver];
        }
        
        return false;
    }
}
