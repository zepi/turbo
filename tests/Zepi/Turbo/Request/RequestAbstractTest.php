<?php

namespace Tests\Zepi\Turbo\Response;

class RequestAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = new \Zepi\Turbo\Request\WebRequest(
            'GET',
            'http://test.local/test/abc/', 
            '/test/abc', 
            array('test' => 'abc'), 
            'http://localhost/', 
            'de_DE', 
            'linux',
            true, 
            array('header' => 'header-value'),
            'HTTP/1.1',
            array('abc' => 'test')
        );
    }
    
    public function testGetLocale()
    {
        $this->assertEquals('de_DE', $this->request->getLocale());
    }
    
    public function testHasAndGetParam()
    {
        $this->assertTrue($this->request->hasParam('test'));
        $this->assertEquals('abc', $this->request->getParam('test'));
        $this->assertEquals(array('test' => 'abc'), $this->request->getParams());
    }
    
    public function testGetNotExistingParam()
    {
        $this->assertFalse($this->request->getParam('test2'));
    }
    
    public function testAddAndGetRouteParams()
    {
        $this->request->addRouteParam('keyOne');
        $this->request->addRouteParam('keyTwo');
        
        $this->request->addRouteParam('keyThree', 'three');
        
        $this->assertEquals('keyOne', $this->request->getRouteParam(0));
        $this->assertEquals('keyTwo', $this->request->getRouteParam(1));
        
        $this->assertEquals('keyThree', $this->request->getRouteParam(2));
        $this->assertEquals('keyThree', $this->request->getRouteParam('three'));
    }
    
    public function testSetRouteParams()
    {
        $this->request->setRouteParams(array('keyThree', 'keyFour', 'keyFive', 'five' => 'keyFive'));
        
        $this->assertEquals('keyThree', $this->request->getRouteParam(0));
        $this->assertEquals('keyFour', $this->request->getRouteParam(1));

        $this->assertEquals('keyFive', $this->request->getRouteParam(2));
        $this->assertEquals('keyFive', $this->request->getRouteParam('five'));
    }
    
    public function testSetRouteParamWithoutAnArray()
    {
        $result = $this->request->setRouteParams(false);
        
        $this->assertFalse($result);
    }
    
    public function testGetNotExistingRouteParam()
    {
        $this->assertFalse($this->request->getRouteParam(9));
        $this->assertFalse($this->request->getRouteParam('keyTen'));
    }
    
    public function testGetFullRoute()
    {
        $route = $this->request->getFullRoute();
    
        $this->assertEquals('http://localhost/test/abc/', $route);
    }
    
    public function testGetFullRouteWithRoutePart()
    {
        $route = $this->request->getFullRoute('home');
    
        $this->assertEquals('http://localhost/home/', $route);
    }
}
