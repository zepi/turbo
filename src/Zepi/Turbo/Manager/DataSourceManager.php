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
    protected $_framework;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\Backend\ObjectBackendAbstract
     */
    protected $_dataSourceObjectBackend;
    
    /**
     * @access protected
     * @var array
     */
    protected $_dataSources = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $_definitions = array();
    
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
        $this->_framework = $framework;
        $this->_dataSourceObjectBackend = $dataSourceObjectBackend;
    }
    
    /**
     * Initializes the data source manager. The function loads all saved
     * events from the object backend.
     *
     * @access public
     */
    public function initializeDataSourceManager()
    {
        $dataSources = $this->_dataSourceObjectBackend->loadObject();
        if (!is_array($dataSources)) {
            $dataSources = array();
        }
    
        $this->_dataSources = $dataSources;
    }
    
    /**
     * Adds a data source to the repository
     * 
     * @access public 
     * @param string $typeClass
     * @param string $driver
     * @param string $class
     */
    public function addDataSource($typeClass, $driver, $class)
    {
        if (!isset($this->_dataSources[$typeClass]) || !is_array($this->_dataSources[$typeClass])) {
            $this->_dataSources[$typeClass] = array();
        }
        
        $this->_dataSources[$typeClass][$driver] = $class;
        $this->_saveDataSources();
        
        return true;
    }
    
    /**
     * Removes a data source from the repository
     * 
     * @access public
     * @param string $typeClass
     * @param string $driver
     * @param string $class
     * @return boolean
     */
    public function removeDataSource($typeClass, $driver, $class)
    {
        if (!isset($this->_dataSources[$typeClass][$driver])) {
            return false;
        }
        
        unset($this->_dataSources[$typeClass][$driver]);
        $this->_saveDataSources();
        
        return true;
    }
    
    /**
     * Saves the registred data sources in the object backend.
     *
     * @access protected
     */
    protected function _saveDataSources()
    {
        $this->_dataSourceObjectBackend->saveObject($this->_dataSources);
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
        $this->_definitions[$selector] = $driver;
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
        if (!isset($this->_definitions[$selector])) {
            return false;
        }
        
        unset($this->_definitions[$selector]);
        return true;
    }
    
    /**
     * Returns the data source for the given type class.
     * 
     * @access public
     * @param string $typeClass
     * @return mixed
     * 
     * @throw \Zepi\Turbo\Exception Cannot find a data source for the given type class.
     */
    public function getDataSource($typeClass)
    {
        $driver = $this->_getDriver($typeClass);
        $dataSourceClass = $this->_searchDataSourceClass($typeClass, $driver);

        // If there is no data source class for the given type class throw an exception
        if ($dataSourceClass === false) {
            throw new Exception('Cannot find a data source for the given type class "' . $typeClass . '" (selected driver: "' . $driver . '").');
        }
        
        return $this->_framework->getInstance($dataSourceClass);
    }
    
    /**
     * Returns the driver for the given type class or false if no 
     * driver is available.
     * 
     * @access protected
     * @param string $typeClass
     * @return false|string
     */
    protected function _getDriver($typeClass)
    {
        $bestDriver = false;
        $numberOfParts = 0;
        
        foreach ($this->_definitions as $selector => $driver) {
            if ($selector === '*' || $selector === $typeClass) {
                $bestDriver = $driver;
                $numerOfParts = $this->_countNumberOfParts($selector);
            } else if (substr($selector, -1) === '*') {
                $selectorWithoutWildcard = substr($selector, 0, -1);
                
                if (strpos($selector, $selectorWithoutWildcard) === 0 || $numberOfParts < $this->_countNumberOfParts($selector)) {
                    $bestDriver = $driver;
                    $numerOfParts = $this->_countNumberOfParts($selector);
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
    protected function _countNumberOfParts($selector)
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
    protected function _searchDataSourceClass($typeClass, $driver)
    {
        if (isset($this->_dataSources[$typeClass][$driver])) {
            return $this->_dataSources[$typeClass][$driver];
        }
        
        return false;
    }
}
