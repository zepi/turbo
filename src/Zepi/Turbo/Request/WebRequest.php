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
 * The WebRequest representates a web framework request.
 * 
 * @package Zepi\Turbo\Request
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Request;

use \Zepi\Turbo\FrameworkInterface\SessionInterface;

/**
 * The WebRequest representates a web framework request.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class WebRequest extends RequestAbstract
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    
    /**
     * @access protected
     * @var string
     */
    protected $method;
    
    /**
     * @access protected
     * @var string
     */
    protected $requestedUrl;
    
    /**
     * @access protected
     * @var array
     */
    protected $headers;
    
    /**
     * @access protected
     * @var string
     */
    protected $protocol;
    
    /**
     * @access protected
     * @var boolean
     */
    protected $isSsl = false;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\FrameworkInterface\SessionInterface
     */
    protected $session = null;
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param string $method
     * @param string $requestedUrl
     * @param string $route
     * @param array params
     * @param string $base
     * @param string $locale
     * @param string $operatingSystem
     * @param boolean $isSsl
     * @param array $headers
     * @param string $protocol
     * @param array $data
     */
    public function __construct($method, $requestedUrl, $route, $params, $base, $locale, $operatingSystem, $isSsl, $headers, $protocol, $data = array())
    {
        parent::__construct($route, $params, $base, $locale, $operatingSystem, $data);
        
        $this->method = $method;
        $this->requestedUrl = $requestedUrl;
        $this->isSsl = $isSsl;
        $this->headers = $headers;
        $this->protocol = $protocol;
    }
    
    /**
     * Returns the method of the request
     * 
     * @access public
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Returns the requested url
     * 
     * @access public
     * @return string
     */
    public function getRequestedUrl()
    {
        return $this->requestedUrl;
    }
    
    /**
     * Returns the delimitier, which is used to split the route
     * into parts.
     * The delimiter for the html request is the slash (/).
     * 
     * @access public
     * @return string
     */
    public function getRouteDelimiter()
    {
        return '/';
    }
    
    /**
     * Saves the given value for the given key in the session data
     * 
     * @access public
     * @param string $key
     * @param mixed $value
     */
    public function setSessionData($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Returns the session value of the given key.
     * 
     * @access public
     * @param string $key
     * @return mixed
     */
    public function getSessionData($key = '')
    {
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        return $_SESSION[$key];
    }
    
    /**
     * Deletes the value for the given key
     * 
     * @access public
     * @param string $key
     * @return boolean
     */
    public function deleteSessionData($key)
    {
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        unset($_SESSION[$key]);
        return true;
    }
    
    /**
     * Removes all session data
     * 
     * @access public
     */
    public function clearSessionData()
    {
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Returns the cookie value of the given key.
     * 
     * @access public
     * @param string $key
     * @return mixed
     */
    public function getCookieData($key = '')
    {
        if (!isset($_COOKIE[$key])) {
            return false;
        }
        
        return $_COOKIE[$key];
    }
    
    /**
     * Returns true if this request was made over a 
     * secure connection trough ssl.
     * 
     * @access public
     * @return boolean
     */
    public function isSsl()
    {
        return ($this->isSsl);
    }
    
    /**
     * Adds a session object to the request
     * 
     * @access public
     * @param \Zepi\Turbo\FrameworkInterface\SessionInterface $session
     * @return boolean
     */
    public function setSession(SessionInterface $session)
    {
        if (!is_object($session) || $this->session !== null) {
            return false;
        }
        
        $this->session = $session;
        
        return true;
    }
    
    /**
     * Returns true if a session for the given name
     * exists. Otherwise returns false.
     * 
     * @access public
     * @return boolean
     */
    public function hasSession()
    {
        if ($this->session === null) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Returns the session
     * 
     * @access public
     * @return false|\Zepi\Turbo\FrameworkInterface\SessionInterface
     */
    public function getSession()
    {
        if ($this->session === null) {
            return false;
        }
        
        return $this->session;
    }
    
    /**
     * Removes the session
     * 
     * @access public
     */
    public function removeSession()
    {
        $this->session = null;
        $this->clearSessionData();
    }
    
    /**
     * Returns the value for the given header key
     * 
     * @access public
     * @param string $key
     * @return false|mixed
     */
    public function getHeader($key)
    {
        if (!isset($this->headers[$key])) {
            return false;
        }
        
        return $this->headers[$key];
    }
    
    /**
     * Returns an array with all headers
     * 
     * @access public
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Returns the protocol of the request
     * 
     * @access public
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
}
