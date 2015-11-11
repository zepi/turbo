<?php
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
        return false;
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
