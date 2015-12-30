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
 * The abstract ModuleAbstract is the base for all modules
 * 
 * @package Zepi\Turbo\Module
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Module;

use \Zepi\Turbo\Framework;

/**
 * The abstract ModuleAbstract is the base for all modules
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
abstract class ModuleAbstract
{
    /**
     * @access protected
     * @var Framework
     */
    protected $_framework;
    
    /**
     * @access protected
     * @var string
     */
    protected $_namespace;
    
    /**
     * @access protected
     * @var string
     */
    protected $_directory;
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     * @param string $namespace
     * @param string $directory
     */
    public function __construct(Framework $framework, $namespace, $directory)
    {
        $this->_framework = $framework;
        $this->_namespace = $namespace;
        $this->_directory = $directory;
    }
    
    /**
     * Initializes the module
     * 
     * @access public
     */
    public function initialize()
    {
        
    }

    /**
     * Initializes and return an instance of the given class name.
     * 
     * @access public
     * @param string $className
     * @return mixed
     */
    public function getInstance($className)
    {
        return new $className();
    }
    
    /**
     * This action will be executed on the activation of the module
     * 
     * @access public
     * @abstract
     * @param string $versionNumber
     * @param string $oldVersionNumber
     */
    abstract public function activate($versionNumber, $oldVersionNumber = '');
    
    /**
     * This action will be executed on the deactiviation of the module
     * 
     * @access public
     * @abstract
     */
    abstract public function deactivate();
    
    /**
     * Returns the namespace of the module
     * 
     * @access public
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }
    
    /**
     * Returns the directory of the module
     * 
     * @access public
     * @return string
     */
    public function getDirectory()
    {
        return $this->_directory;
    }
}
