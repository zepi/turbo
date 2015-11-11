<?php
/**
 * The abstract ObjectBackend to define the base functions for
 * the object backend.
 * 
 * @package Zepi\Turbo\Backend
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Backend;

/**
 * The abstract ObjectBackend to define the base functions for
 * the object backend.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
abstract class ObjectBackendAbstract
{
    /**
     * Saves an object
     * 
     * @access public
     * @param mixed $object
     * @return boolean
     */
    public function saveObject($object)
    {
        return $this->_saveSerializedObject(serialize($object));
    }
    
    /**
     * Saves the object in the data source
     * 
     * @access protected
     * @param string $serializedObject
     * @return boolean
     */
    abstract protected function _saveSerializedObject($serializedObject);
    
    /**
     * Loads an object
     * 
     * @access public
     * @return boolean|mixed
     */
    public function loadObject()
    {
        $serializedObject = $this->_loadSerializedObject();
        $object = unserialize($serializedObject);
        
        if ($object === false) {
            return false;
        }
        
        return $object;
    }
    
    /**
     * Loads the object from the data source
     * 
     * @access protected
     * @return string
     */
    abstract protected function _loadSerializedObject();
}
