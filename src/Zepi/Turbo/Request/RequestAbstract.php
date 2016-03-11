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
 * The abstract RequestAbstract is the base for all request types
 * 
 * @package Zepi\Turbo\Request
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Request;

/**
 * The abstract RequestAbstract is the base for all request types
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
abstract class RequestAbstract
{
    /**
     * @access protected
     * @var string
     */
    protected $_route;
    
    /**
     * @access protected
     * @var array
     */
    protected $_params = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $_routeParams = array();
    
    /**
     * @access protected
     * @var string
     */
    protected $_base;
    
    /**
     * @access protected
     * @var string
     */
    protected $_locale;
    
    /**
     * @access protected
     * @var array
     */
    protected $_data = array();
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param string $route
     * @param array params
     * @param string $base
     * @param string $locale
     * @param array $data
     */
    public function __construct($route, $params, $base, $locale, $data = array())
    {
        $this->_route = $route;
        $this->_locale = $locale;
        
        if (is_array($params)) {
            $this->_params = $params; 
        }
        
        if (substr($base, -1) === '/') {
            $base = substr($base, 0, -1);
        }
        $this->_base = $base;
        
        if (is_array($data)) {
            $this->_data = $data;
        }
    }
    
    /**
     * Returns the route of the request.
     * 
     * @access public
     * @return string
     */
    public function getRoute()
    {
        return $this->_route;
    }
    
    /**
     * Sets the route of the request
     * 
     * @access public
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->_route = $route;
    }
    
    /**
     * Returns true if the given param key exists.
     * 
     * @access public
     * @param string $key
     * @return string
     */
    public function hasParam($key)
    {
        return (isset($this->_params[$key]));
    }
    
    /**
     * Returns the value for the given param key. If the
     * key does not exists the function will return false.
     * 
     * @access public
     * @param string $key
     * @return mixed
     */
    public function getParam($key)
    {
        if (!$this->hasParam($key)) {
            return false;
        }
        
        return $this->_params[$key];
    }
    
    /**
     * Returns all params of the request
     * 
     * @access public
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Adds an parameter which is detected in the route.
     * 
     * @access public
     * @param mixed $param
     */
    public function addRouteParam($param)
    {
        $this->_routeParams[] = $param;
    }
    
    /**
     * Sets the array of route params for the request
     * 
     * @access public
     * @param array $params
     * @return boolean
     */
    public function setRouteParams($params)
    {
        if (!is_array($params)) {
            return false;
        }
        
        $this->_routeParams = $params;
        
        return true;
    }
    
    /**
     * Returns the route param for the given index.
     * 
     * @access public
     * @param integer $index
     * @return string|boolean
     */
    public function getRouteParam($index)
    {
        if (!isset($this->_routeParams[$index])) {
            return false;
        }
        
        return $this->_routeParams[$index];
    }
    
    /**
     * Returns the delimitier, which is used to split the route
     * into parts.
     * 
     * @access public
     * @return string
     */
    abstract public function getRouteDelimiter();
    
    /**
     * Returns the correct url for the given url part
     * 
     * @access public
     * @param string $routePart
     * @return string
     */
    public function getFullRoute($routePart = '')
    {
        if ($routePart == '') {
            $routePart = $this->_route;
        }
        
        $delimiter = $this->getRouteDelimiter();
        if (substr($routePart, 0, strlen($delimiter)) !== $delimiter) {
            $routePart = $delimiter . $routePart;
        }
        
        $posPoint = strrpos($routePart, '.');
        if (substr($routePart, -1) !== '/' && ($posPoint === false || $posPoint < strrpos($routePart, '/'))) {
            $routePart .= '/';
        }

        return $this->_base . $routePart;
    }
    
    /**
     * Returns the locale of the request
     * 
     * @access public
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }
}
