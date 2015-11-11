<?php
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
     * @return \Zepi\Turbo\Request\HtmlRequest
     */
    protected function _buildWebRequest()
    {
        $args = $_REQUEST;
        $params = array();
        $route = $_REQUEST['_r'];
        
        // Remove the slash at the start and at the end of the route
        $route = trim($route, '/');

        // Transform the arguments
        foreach ($args as $key => $value) {
            if (is_numeric($value)) {
                // Transform the value into the correct data type
                $value = $value * 1;
            }
            
            $params[$key] = $value;
        }

        // Get the protocol
        $proto = 'http';
        $isSsl = false;
        if (isset($_SERVER['HTTPS'])) {
            $proto = 'https';
            $isSsl = true;
        }

        // Generate the full url and extract the base
        $fullUrl = $proto . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        $routePosition = strlen($fullUrl);
        if ($route !== '') {
            $routePosition = strpos($fullUrl, $route);
        }

        $base = substr($fullUrl, 0, $routePosition);
        $locale = $this->_getLocale($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        
        header('X-Accepted-Locale: ' . $locale);
        
        return new WebRequest($route, $params, $base, $locale, $isSsl);
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
