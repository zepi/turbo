<?php
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
