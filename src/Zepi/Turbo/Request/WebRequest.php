<?php
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
    /**
     * @access protected
     * @var boolean
     */
    protected $_isSsl = false;
    
    /**
     * @access protected
     * @var \Zepi\Turbo\FrameworkInterface\SessionInterface
     */
    protected $_session = null;
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param string $route
     * @param array params
     * @param string $base
     * @param string $locale
     * @param boolean $isSsl
     * @param array $data
     */
    public function __construct($route, $params, $base, $locale, $isSsl, $data = array())
    {
        parent::__construct($route, $params, $base, $locale, $data);
        
        $this->_isSsl = $isSsl;
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
     * @param string $value
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
        return ($this->_isSsl);
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
        if (!is_object($session) || $this->_session !== null) {
            return false;
        }
        
        $this->_session = $session;
        
        return true;
    }
    
    /**
     * Returns true if a session for the given name
     * exists. Otherwise returns false.
     * 
     * @access public
     * @param string $name
     * @return boolean
     */
    public function hasSession()
    {
        if ($this->_session === null) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Returns the session
     * 
     * @access public
     * @return \Zepi\Turbo\FrameworkInterface\SessionInterface
     */
    public function getSession()
    {
        if ($this->_session === false) {
            return false;
        }
        
        return $this->_session;
    }
    
    /**
     * Removes the session
     * 
     * @access public
     */
    public function removeSession()
    {
        $this->_session = null;
    }
}
