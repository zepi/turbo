<?php

namespace Tests\Zepi\Turbo\Response;

class WebRequestTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $_SESSION;
        
        $_SESSION = array();
        
        $this->_request = new \Zepi\Turbo\Request\WebRequest(
            'GET',
            'http://test.local/test/abc/', 
            '/test/abc', 
            array('test' => 'abc'), 
            'http://localhost/', 
            'de_DE', 
            true, 
            array('header' => 'header-value'),
            'HTTP/1.1',
            array('abc' => 'test')
        );
    }
    
    public function testGetRouteDelimiter()
    {
        $this->assertEquals('/', $this->_request->getRouteDelimiter());
    }
    
    public function testSetSessionData()
    {
        $this->_request->setSessionData('keyOne', 'valueOne');
        
        $this->assertTrue(isset($_SESSION['keyOne']));
        $this->assertEquals('valueOne', $_SESSION['keyOne']);
    }
    
    public function testGetSessionData()
    {
        $_SESSION['keyTwo'] = 'valueTwo';
        
        $value = $this->_request->getSessionData('keyTwo');
    
        $this->assertEquals('valueTwo', $value);
    }
    
    public function testGetSessionDataWithNotExistingKey()
    {
        $value = $this->_request->getSessionData('keyThree');
    
        $this->assertFalse($value);
    }
    
    public function testDeleteSessionData()
    {
        $_SESSION['keyFour'] = 'valueFour';
    
        $this->assertEquals('valueFour', $this->_request->getSessionData('keyFour'));
        
        $result = $this->_request->deleteSessionData('keyFour');
    
        $this->assertTrue($result);
        $this->assertFalse(isset($_SESSION['keyFour']));
    }
    
    public function testDeleteSessionDataWithNotExistingKey()
    {
        $value = $this->_request->deleteSessionData('keyFive');
    
        $this->assertFalse($value);
    }
    
    public function testIsSsl()
    {
        $this->assertTrue($this->_request->isSsl());
    }
    
    public function testGetCookieData()
    {
        $_COOKIE['keySix'] = 'abcdef';
        
        $this->assertEquals('abcdef', $this->_request->getCookieData('keySix'));
    }
    
    public function testGetCookieDataWithNotExistingKey()
    {
        $this->assertFalse($this->_request->getCookieData('keySeven'));
    }
    
    public function testSetAndGetSession()
    {
        $this->_session = $this->getMockBuilder('\\TestModule\\TestSession')
                               ->disableOriginalConstructor()
                               ->getMock();
        
        $this->assertFalse($this->_request->hasSession());
        $this->assertFalse($this->_request->getSession());
        
        $this->_request->setSession($this->_session);
        
        $this->assertTrue($this->_request->hasSession());
        $this->assertEquals($this->_session, $this->_request->getSession());
    }
    
    public function testSetSessionTwoTimes()
    {
        $this->_session = $this->getMockBuilder('\\TestModule\\TestSession')
                               ->disableOriginalConstructor()
                               ->getMock();
                            
        $resultOne = $this->_request->setSession($this->_session);
        $resultTwo = $this->_request->setSession($this->_session);
    
        $this->assertTrue($resultOne);
        $this->assertFalse($resultTwo);
        $this->assertEquals($this->_session, $this->_request->getSession());
    }
    
    public function testRemoveSession()
    {
        $this->_session = $this->getMockBuilder('\\TestModule\\TestSession')
                               ->disableOriginalConstructor()
                               ->getMock();
    
        $resultTwo = $this->_request->setSession($this->_session);
    
        $this->assertEquals($this->_session, $this->_request->getSession());
        
        $this->_request->removeSession();
        
        $this->assertFalse($this->_request->hasSession());
    }
}
