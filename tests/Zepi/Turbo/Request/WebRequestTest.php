<?php

namespace Tests\Zepi\Turbo\Response;

class WebRequestTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $_SESSION;
        
        $_SESSION = array();
        
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
    
    public function testGetRouteDelimiter()
    {
        $this->assertEquals('/', $this->request->getRouteDelimiter());
    }
    
    public function testSetSessionData()
    {
        $this->request->setSessionData('keyOne', 'valueOne');
        
        $this->assertTrue(isset($_SESSION['keyOne']));
        $this->assertEquals('valueOne', $_SESSION['keyOne']);
    }
    
    public function testGetSessionData()
    {
        $_SESSION['keyTwo'] = 'valueTwo';
        
        $value = $this->request->getSessionData('keyTwo');
    
        $this->assertEquals('valueTwo', $value);
    }
    
    public function testGetSessionDataWithNotExistingKey()
    {
        $value = $this->request->getSessionData('keyThree');
    
        $this->assertFalse($value);
    }
    
    public function testDeleteSessionData()
    {
        $_SESSION['keyFour'] = 'valueFour';
    
        $this->assertEquals('valueFour', $this->request->getSessionData('keyFour'));
        
        $result = $this->request->deleteSessionData('keyFour');
    
        $this->assertTrue($result);
        $this->assertFalse(isset($_SESSION['keyFour']));
    }
    
    public function testDeleteSessionDataWithNotExistingKey()
    {
        $value = $this->request->deleteSessionData('keyFive');
    
        $this->assertFalse($value);
    }
    
    public function testIsSsl()
    {
        $this->assertTrue($this->request->isSsl());
    }
    
    public function testGetCookieData()
    {
        $_COOKIE['keySix'] = 'abcdef';
        
        $this->assertEquals('abcdef', $this->request->getCookieData('keySix'));
    }
    
    public function testGetCookieDataWithNotExistingKey()
    {
        $this->assertFalse($this->request->getCookieData('keySeven'));
    }
    
    public function testSetAndGetSession()
    {
        $this->session = $this->getMockBuilder('\\TestModule\\TestSession')
                               ->disableOriginalConstructor()
                               ->getMock();
        
        $this->assertFalse($this->request->hasSession());
        $this->assertFalse($this->request->getSession());
        
        $this->request->setSession($this->session);
        
        $this->assertTrue($this->request->hasSession());
        $this->assertEquals($this->session, $this->request->getSession());
    }
    
    public function testSetSessionTwoTimes()
    {
        $this->session = $this->getMockBuilder('\\TestModule\\TestSession')
                               ->disableOriginalConstructor()
                               ->getMock();
                            
        $resultOne = $this->request->setSession($this->session);
        $resultTwo = $this->request->setSession($this->session);
    
        $this->assertTrue($resultOne);
        $this->assertFalse($resultTwo);
        $this->assertEquals($this->session, $this->request->getSession());
    }
    
    public function testRemoveSession()
    {
        $this->session = $this->getMockBuilder('\\TestModule\\TestSession')
                               ->disableOriginalConstructor()
                               ->getMock();
    
        $resultTwo = $this->request->setSession($this->session);
    
        $this->assertEquals($this->session, $this->request->getSession());
        
        $this->request->removeSession();
        
        $this->assertFalse($this->request->hasSession());
    }
}
