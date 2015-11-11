<?php
/**
 * The FileObjectBackend saves and loads an object from a file
 * 
 * @package Zepi\Turbo\Backend
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Backend;

use \Zepi\Turbo\Exception;

/**
 * The FileObjectBackend saves and loads an object from a file
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class FileObjectBackend extends ObjectBackendAbstract
{
    /**
     * @access protected
     * @var string
     */
    protected $_path;
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param string $path
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }
    
    /**
     * Saves the object in the data source
     * 
     * @access protected
     * @param string $serializedObject
     * @return boolean
     * 
     * @throws Zepi\Turbo\Exception The file "$path" isn't writable!
     */
    protected function _saveSerializedObject($serializedObject)
    {
        if (file_exists($this->_path) && !is_writable($this->_path)) {
            throw new Exception('The file "' . $this->_path . '" isn\'t writable!');
        }

        return file_put_contents($this->_path, $serializedObject);
    }
    
    /**
     * Loads the object from the data source
     * 
     * @access protected
     * @return string
     * 
     * @throws Zepi\Turbo\Exception The file "$path" isn't readable!
     */
    protected function _loadSerializedObject()
    {
        if (!file_exists($this->_path)) {
            return '';
        }
        
        if (!is_readable($this->_path)) {
            throw new Exception('The file "' . $this->_path . '" isn\'t readable!');
        }
        
        return file_get_contents($this->_path);
    }
}
