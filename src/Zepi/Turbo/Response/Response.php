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
 * The Response holds all information which are outputtet at the end
 * of the request.
 * 
 * @package Zepi\Turbo\Response
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Response;

use \Zepi\Turbo\Request\RequestAbstract;
use \Zepi\Turbo\Request\WebRequest;

/**
 * The Response holds all information which are outputtet at the end
 * of the request.
 * 
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */
class Response
{
    /**
     * @access protected
     * @var RequestAbstract
     */
    protected $request;
    
    /**
     * @access protected
     * @var array
     */
    protected $data = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $outputParts = array();
    
    /**
     * @access protected
     * @var string
     */
    protected $output;
    
    /**
     * @var boolean
     */
    protected $outputLocked = false;
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Request\RequestAbstract $request
     */
    public function __construct(RequestAbstract $request)
    {
        $this->request = $request;
    }
    
    /**
     * Return the data for the given key. If the key does 
     * not exists the function will return false.
     * 
     * @access public
     * @param string $key
     * @return mixed
     */
    public function getData($key)
    {
        if (!$this->hasData($key)) {
            return false;
        }
        
        return $this->data[$key];
    }
    
    /**
     * Returns true if the given key is set.
     * 
     * @access public
     * @param string $key
     * @return boolean
     */
    public function hasData($key)
    {
        return (isset($this->data[$key]));
    }
    
    /**
     * Saves the value for the given key in the response object.
     * 
     * @access public
     * @param string $key
     * @param mixed $value
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * Returns the output for the given key. If the key does
     * not exists the function will return false.
     * 
     * @access public
     * @param string $key
     * @return false|string
     */
    public function getOutputPart($key)
    {
        if (!$this->hasOutputPart($key)) {
            return false;
        }
        
        return $this->outputParts[$key];
    }
    
    /**
     * Returns true if the given key exists as output key.
     * 
     * @access public
     * @param string $key
     * @return boolean
     */
    public function hasOutputPart($key)
    {
        return (isset($this->outputParts[$key]));
    }
    
    /**
     * Saves the output for the given key in the Response object.
     * 
     * @access public
     * @param string $key
     * @param string $output
     */
    public function setOutputPart($key, $output)
    {
        $this->outputParts[$key] = $output;
    }
    
    /**
     * Returns all output parts of the Response object.
     * 
     * @access public
     * @return array
     */
    public function getOutputParts()
    {
        return $this->outputParts;
    }
    
    /**
     * Returns the output of the response.
     * 
     * @access public
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * Returns true if the response has an output.
     * 
     * @access public
     * @return boolean
     */
    public function hasOutput()
    {
        return ($this->output != '');
    }
    
    /**
     * Sets the output of the response.
     * If $lock is true the output will be locked and the method
     * will not accept any other output. 
     * 
     * @access public
     * @param string $output
     * @param boolean $lock
     * @return boolean
     */
    public function setOutput($output, $lock = false)
    {
        if ($this->outputLocked) {
            return false;
        }
        
        if ($lock) {
            $this->outputLocked = true;
        }
        
        $this->output = $output;
        return true;
    }
    
    /**
     * Returns true if the output in the response is locked.
     * 
     * @return boolean
     */
    public function isOutputLocked()
    {
        return ($this->outputLocked);
    }
    
    /**
     * Set the Location header to redirect a request
     * 
     * @access public
     * @param string $target
     * @param integer $headerCode
     * @param boolean $withOrigin
     */
    public function redirectTo($target, $headerCode = 301, $withOrigin = false)
    {
        if (!($this->request instanceof WebRequest)) {
            return;
        }
        
        if (strpos($target, 'http://') === false) {
            $target = $this->request->getFullRoute($target);
        }
        
        if ($withOrigin) {
            $target = $this->addOriginToTargetUrl($target);
        }
        
        header("Location: " . $target, true, $headerCode);
    }
    
    /**
     * Sends a header
     * 
     * @access public
     * @param string $message
     * @param integer $code
     */
    public function sendHeader($message, $code = null)
    {
        if (!($this->request instanceof WebRequest)) {
            return;
        }
        
        if ($code !== null) {
            header($message, true, $code);
        } else {
            header($message);
        }
    }
    
    /**
     * Sends the status code for the give code
     * 
     * @access public
     * @param integer $code
     * @param boolean $resetOutput
     */
    public function sendHttpStatus($code, $resetOutput = false)
    {
        if (!($this->request instanceof WebRequest)) {
            return;
        }
        
        $codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );
        
        if (!isset($codes[$code])) {
            return false;
        }
        
        $message = $this->request->getProtocol() . ' ' . $code . ' ' . $codes[$code];
        header($message, true, $code);
        
        if ($resetOutput) {
            $this->setOutput($message);
        }
    }
    
    /**
     * Returns a full url for the given url parts array
     * from the function `parse_url()`.
     * 
     * @access public
     * @param array $urlParts
     * @return string
     */
    public function buildUrl($urlParts)
    {
        return http_build_url($urlParts);
    }
    
    /**
     * Adds the origin and returns the given target url
     * with the origin query parameter.
     * 
     * @param string $target
     * @return void|string
     */
    protected function addOriginToTargetUrl($target)
    {
        $origin = $this->request->getFullRoute();
        $additionalQuery = '_origin=' . base64_encode($origin);
        
        $parts = parse_url($target);
        
        if ($parts === false) {
            return $target;
        }
        
        if (!isset($parts['query'])) {
            $parts['query'] = '';
        } else if ($parts['query'] !== '') {
            $parts['query'] .= '&';
        }
        
        $parts['query'] .= $additionalQuery;
        return $this->buildUrl($parts);
    }
}
