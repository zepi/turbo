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
     * @return boolean
     * 
     * @throws Zepi\Turbo\Exception The directory "$directory" doesn't exists!
     * @throws Zepi\Turbo\Exception The file "$path" isn't writable!
     */
    public function saveToFile($content, $additionalPath = '')
    {
        $path = $this->_path;
        if ($additionalPath !== '') {
            $path .= $additionalPath;
        }
        
        $directory = dirname($path);
        if (!file_exists($directory)) {
            $result = mkdir($directory, 0755, true);
            
            if (!$result || !file_exists($directory)) {
                throw new Exception('The directory "' . $directory . '" doesn\'t exists!');
            }
        }
        
        if (file_exists($path) && !is_writable($path)) {
            throw new Exception('The file "' . $path . '" isn\'t writable!');
        }
        
        return file_put_contents($path, $content);
    }
    
    /**
     * Loads the content from the file
     * 
     * @access public
     * @param string $additionalPath
     * @return string
     * 
     * @throws Zepi\Turbo\Exception The file "$path" isn't readable!
     */
    public function loadFromFile($additionalPath = '')
    {
        if (substr($additionalPath, 0, 1) === '/') {
            $path = $additionalPath;
        } else if ($additionalPath !== '') {
            $path = $this->_path . $additionalPath;
        } else {
            $path = $this->_path;
        }
        
        if (!file_exists($path)) {
            return '';
        }
        
        if (!is_readable($path)) {
            throw new Exception('The file "' . $path . '" isn\'t readable!');
        }
        
        return file_get_contents($path);
    }
    
    /**
     * Deletes a file on the hard disk.
     * 
     * @access public
     * @param string $additionalPath
     * @return string
     * 
     * @throws Zepi\Turbo\Exception The file "$path" isn't writable!
     */
    public function deleteFile($additionalPath = '')
    {
        // Determine the full path
        if (substr($additionalPath, 0, 1) === '/') {
            $path = $additionalPath;
        } else if ($additionalPath !== '') {
            $path = $this->_path . $additionalPath;
        } else {
            $path = $this->_path;
        }
        
        // If the file doesn't exists, we haven't to do anything...
        if (!file_exists($path)) {
            return true;
        }
        
        if (!is_writable($path)) {
            throw new Exception('The file "' . $path . '" isn\'t writable!');
        }
        
        return unlink($path);
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
        // Determine the full path
        if (substr($additionalPath, 0, 1) === '/') {
            $path = $additionalPath;
        } else if ($additionalPath !== '') {
            $path = $this->_path . $additionalPath;
        } else {
            $path = $this->_path;
        }
        
        if (!file_exists($path) || !is_writable($path)) {
            return false;
        }
        
        return true;
    }
}
