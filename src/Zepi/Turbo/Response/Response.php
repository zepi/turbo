<?php
/**
 * The Response holds all information which are outputtet at the end
 * of the request.
 * 
 * @package Zepi\Turbo\Response
 * @author Matthias Zobrist <matthias.zobrist@zepi.net>
 * @copyright Copyright (c) 2015 zepi
 */

namespace Zepi\Turbo\Response;

use Zepi\Turbo\Request\RequestAbstract;

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
    protected $_request;
    
    /**
     * @access protected
     * @var array
     */
    protected $_data = array();
    
    /**
     * @access protected
     * @var array
     */
    protected $_outputParts = array();
    
    /**
     * @access protected
     * @var string
     */
    protected $_output;
    
    /**
     * Constructs the object
     * 
     * @access public
     * @param \Zepi\Turbo\Request\RequestAbstract $request
     */
    public function __construct(RequestAbstract $request)
    {
        $this->_request = $request;
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
        
        return $this->_data[$key];
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
        return (isset($this->_data[$key]));
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
        $this->_data[$key] = $value;
    }
    
    /**
     * Returns the output for the given key. If the key does
     * not exists the function will return false.
     * 
     * @access public
     * @param string $key
     * @return string
     */
    public function getOutputPart($key)
    {
        if (!$this->hasOutputPart($key)) {
            return false;
        }
        
        return $this->_outputParts[$key];
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
        return (isset($this->_outputParts[$key]));
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
        $this->_outputParts[$key] = $output;
    }
    
    /**
     * Returns all output parts of the Response object.
     * 
     * @access public
     * @return array
     */
    public function getOutputParts()
    {
        return $this->_outputParts;
    }
    
    /**
     * Returns the output of the response.
     * 
     * @access public
     * @return string
     */
    public function getOutput()
    {
        return $this->_output;
    }
    
    /**
     * Returns true if the response has an output.
     * 
     * @access public
     * @return boolean
     */
    public function hasOutput()
    {
        return ($this->_output != '');
    }
    
    /**
     * Sets the output of the response.
     * 
     * @access public
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->_output = $output;
    }
    
    /**
     * Set the Location header to redirect a request
     * 
     * @access public
     * @param string $location
     * @param integer $headerCode
     */
    public function redirectTo($location, $headerCode = 301)
    {
        if (strpos($location, 'http://') === false) {
            $location = $this->_request->getFullRoute($location);
        }
        
        header("Location: " . $location, true, $headerCode);
    }
}
