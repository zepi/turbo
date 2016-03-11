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
 * The RequestManager parses the request params and creates
 * the Request object for the input data.
 * 
 * @package Zepi\Turbo\Manager
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Manager;

use \Zepi\Turbo\Framework;
use \Zepi\Turbo\Request\CliRequest;
use \Zepi\Turbo\Request\WebRequest;

/**
 * The RequestManager parses the request params and creates
 * the Request object for the input data.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class RequestManager
{
    /**
     * @access protected
     * @var Framework
     */
    protected $_framework;
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Framework $framework
     */
    public function __construct(Framework $framework)
    {
        $this->_framework = $framework;
    }
    
    /**
     * Builds the RequestAbstract object for the given input
     * data.
     * 
     * @access public
     */
    public function buildRequest()
    {
        if (php_sapi_name() === 'cli') {
            return $this->_buildCliRequest();
        } else {
            return $this->_buildWebRequest();
        }
    }
    
    /**
     * Builds the cli request object
     * 
     * @access protected
     * @return \Zepi\Turbo\Request\CliRequest
     */
    protected function _buildCliRequest()
    {
        global $argv;
        
        $args = $argv;
        $params = array();
        $route = '';
        
        foreach ($args as $arg) {
            if ($arg === $_SERVER['PHP_SELF']) {
                continue;
            }
            
            if (strpos($arg, '-') === 0) {
                $arg = ltrim($arg, '-');
                
                $key = $arg;
                $value = true;
                
                if (strpos($arg, '=') !== false) {
                    $key = substr($arg, 0, strpos($arg, '='));
                    $value = substr($arg, strpos($arg, '=') + 1);
                    
                    if (is_numeric($value)) {
                        // Transform the value into the correct data type
                        $value = $value * 1;
                    }
                }
                
                $params[$key] = $value;
            } else {
                if ($route !== '') {
                    $route .= ' ';
                }
                
                $route .= $arg;
            }
        }
        
        $base = $argv[0];
        
        return new CliRequest($route, $params, $base, 'en_US');
    }

    /**
     * Builds the html request object
     * 
     * @access protected
     * @return \Zepi\Turbo\Request\WebRequest
     */
    protected function _buildWebRequest()
    {
        $args = $_REQUEST;
        $params = array();

        $route = $_SERVER['REQUEST_URI'];
        $posQuestionMark = strpos($route, '?');
        if ($posQuestionMark !== false) {
            $route = substr($route, 0, $posQuestionMark);
        }
        
        $posIndex = strpos($route, 'index.php');
        if ($posIndex !== false) {
            $route = substr($route, $posIndex + strlen('index.php'));
        }
        
        // Transform the arguments
        foreach ($args as $key => $value) {
            if (is_numeric($value)) {
                // Transform the value into the correct data type
                $value = $value * 1;
            }
            
            $params[$key] = $value;
        }

        // Generate the full url and extract the base
        $scheme = $this->_getScheme();
        $fullUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $isSsl = false;
        if ($scheme == 'https') {
            $isSsl = true;
        }
        
        $routePosition = strlen($fullUrl);
        if ($route !== '' && $route !== '/') {
            $routePosition = strpos($fullUrl, $route);
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        $requestedUrl = $this->_getRequestedUrl();
        $base = substr($fullUrl, 0, $routePosition);
        $headers = $this->_getHeaders($_SERVER);
        $protocol = $_SERVER['SERVER_PROTOCOL'];
        
        $locale = 'en_US';
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $locale = $this->_getLocale($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        return new WebRequest($method, $requestedUrl, $route, $params, $base, $locale, $isSsl, $headers, $protocol);
    }
    
    /**
     * Returns the scheme of the request
     * 
     * @return string
     */
    protected function _getScheme()
    {
        $scheme = '';
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } else if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
            $scheme = 'https';
        } else {
            if ($_SERVER['SERVER_PORT'] == 443) {
                $scheme = 'https';
            } else {
                $scheme = 'http';
            }
        }
        
        return $scheme;
    }
    
    /**
     * Returns the requested url
     * 
     * @access protected
     * @return string
     */
    protected function _getRequestedUrl()
    {
        $scheme = $this->_getScheme();

        return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Returns an array with all headers of this request
     * 
     * @access protected
     * @param array $params
     * @return array
     */
    protected function _getHeaders($params)
    {
        $headers = array();
        
        foreach ($params as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Returns the best acceptable locale from the language header.
     * 
     * @access protected
     * @param string $acceptLanguageHeader
     * @return string
     */
    protected function _getLocale($acceptLanguageHeader)
    {
        $acceptLanguageHeader = str_replace('-', '_', $acceptLanguageHeader);
        $locales = explode(',', $acceptLanguageHeader);
        
        $acceptableLocales = array();
        foreach ($locales as $locale) {
            $priority = 1;
            if (strpos($locale, ';') !== false) {
                $priority = floatval(substr($locale, strpos($locale, ';')));
                $locale = substr($locale, 0, strpos($locale, ';'));
            }
            
            $acceptableLocales[$priority] = $locale;
        }
        
        krsort($acceptableLocales);
        
        // Get the first locale - it will have the highest priority
        $locale = array_shift($acceptableLocales);
        
        if ($locale == '') {
            $locale = 'en_US';
        } else if (strpos($locale, '_') === false) {
            $locale = $locale . '_' . strtoupper($locale);
        }
        
        return $locale;
    }
}
