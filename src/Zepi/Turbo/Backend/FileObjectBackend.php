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
     * @return integer
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
