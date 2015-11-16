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
     * @return integer
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
