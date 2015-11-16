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
 * The FileBackend saves and loads the content in and from a file.
 * 
 * @package Zepi\Turbo\Backend
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Backend;

use \Zepi\Turbo\Exception;

/**
 * The FileBackend saves and loads the content in and from a file.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class FileBackend
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
     * Saves the content to the file
     * 
     * @access public
     * @param string $content
     * @param string $additionalPath
     * @return integer
     * 
     * @throws Zepi\Turbo\Exception The file "$path" isn't writable!
     */
    public function saveToFile($content, $additionalPath = '')
    {
        $path = $this->_realPath($additionalPath, false);
        
        // If the path does not exists create the directory
        $this->_createTargetDirectory($path);
        
        // If the file exists but isn't writeable we need to throw an exception
        if (file_exists($path) && !is_writable($path)) {
            throw new Exception('The file "' . $path . '" isn\'t writable!');
        }
        
        return file_put_contents($path, $content);
    }
    
    /**
     * Creates the target directory
     * 
     * @access protected
     * @param string $path
     * 
     * @throws \Zepi\Turbo\Exception The directory "{directory}" doesn't exists
     */
    protected function _createTargetDirectory($path)
    {
        $directory = dirname($path);
        if (!file_exists($directory)) {
            $result = mkdir($directory, 0755, true);
        
            if (!$result || !file_exists($directory)) {
                throw new Exception('The directory "' . $directory . '" doesn\'t exists!');
            }
        }
    }
    
    /**
     * Loads the content from the file
     * 
     * @access public
     * @param string $additionalPath
     * @return string
     */
    public function loadFromFile($additionalPath = '')
    {
        $path = $this->_realPath($additionalPath);
        
        if ($path === false) {
            return '';
        }
        
        return file_get_contents($path);
    }
    
    /**
     * Deletes a file on the hard disk.
     * 
     * @access public
     * @param string $additionalPath
     * @return string
     */
    public function deleteFile($additionalPath = '')
    {
        $path = $this->_realPath($additionalPath);
        
        if ($path === false) {
            return false;
        }
        
        return unlink($path);
    }
    
    /**
     * Returns the real path for the given additional path and the 
     * file path which was given to the backend in the constructor.
     * 
     * @access public
     * @param string $additionalPath
     * @param boolean $testDirectory
     * @return boolean|mixed
     * 
     * @throws \Zepi\Turbo\Exception The file path "{path}" is not readable and not writeable!
     */
    protected function _realPath($additionalPath, $testDirectory = true)
    {
        if (substr($additionalPath, 0, 1) === '/') {
            $path = $additionalPath;
        } else if ($additionalPath !== '') {
            $path = $this->_path . $additionalPath;
        } else {
            $path = $this->_path;
        }
    
        if ($testDirectory && !file_exists($path)) {
            return false;
        }
        
        if ($testDirectory && !is_readable($path) && !is_writeable($path)) {
            throw new Exception('The file path "' . $path . '" is not readable and not writeable!');
        }
        
        return $path;
    }
    
    /**
     * Returns true if a file or a directory exists and
     * is writable. Otherwise return false.
     * 
     * @access public
     * @param string $additionalPath
     * @return boolean
     */
    public function isWritable($additionalPath = '')
    {
        $path = $this->_realPath($additionalPath);
        
        if ($path === false || !is_writable($path)) {
            return false;
        }
        
        return true;
    }
}
