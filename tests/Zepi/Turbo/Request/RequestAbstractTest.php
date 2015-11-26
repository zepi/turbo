<?php

namespace Tests\Zepi\Turbo\Response;

class RequestAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_request = new \Zepi\Turbo\Request\WebRequest('/test/abc', array('test' => 'abc'), 'http://localhost/', 'de_DE', true, array('abc' => 'test'));
    }
    
    public function testGetLocale()
    {
        $this->assertEquals('de_DE', $this->_request->getLocale());
    }
    
    public function testHasAndGetParam()
    {
        $this->assertTrue($this->_request->hasParam('test'));
        $this->assertEquals('abc', $this->_request->getParam('test'));
        $this->assertEquals(array('test' => 'abc'), $this->_request->getParams());
    }
    
    public function testGetNotExistingParam()
    {
        $this->assertFalse($this->_request->getParam('test2'));
    }
    
    public function testAddAndGetRouteParams()
    {
        $this->_request->addRouteParam('keyOne');
        $this->_request->addRouteParam('keyTwo');
        
        $this->assertEquals('keyOne', $this->_request->getRouteParam(0));
        $this->assertEquals('keyTwo', $this->_request->getRouteParam(1));
    }
    
    public function testSetRouteParams()
    {
        $this->_request->setRouteParams(array('keyThree', 'keyFour'));
        
        $this->assertEquals('keyThree', $this->_request->getRouteParam(0));
        $this->assertEquals('keyFour', $this->_request->getRouteParam(1));
    }
    
    public function testSetRouteParamWithoutAnArray()
    {
        $result = $this->_request->setRouteParams(false);
        
        $this->assertFalse($result);
    }
    
    public function testGetNotExistingRouteParam()
    {
        $this->assertFalse($this->_request->getRouteParam(9));
    }
    
    public function testGetFullRoute()
    {
        $route = $this->_request->getFullRoute();
    
        $this->assertEquals('http://localhost/test/abc', $route);
    }
    
    public function testGetFullRouteWithRoutePart()
    {
        $route = $this->_request->getFullRoute('home');
    
        $this->assertEquals('http://localhost/home', $route);
    }
}
