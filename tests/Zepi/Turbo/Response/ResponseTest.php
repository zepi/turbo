<?php

namespace Tests\Zepi\Turbo\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_requestAbstract = $this->getMockBuilder('\\Zepi\\Turbo\\Request\\RequestAbstract')
                                       ->disableOriginalConstructor()
                                       ->getMock();
        
        $this->_response = new \Zepi\Turbo\Response\Response($this->_requestAbstract);
    }
    
    public function testSetAndGetData()
    {
        $this->_response->setData('test-key', 'abc');
        
        $this->assertTrue($this->_response->hasData('test-key'));
        $this->assertEquals('abc', $this->_response->getData('test-key'));
    }
    
    public function testGetNotExistingData()
    {
        $this->assertFalse($this->_response->hasData('test-key'));
        $this->assertFalse($this->_response->getData('test-key'));
    }
    
    public function testSetAndGetOutputPart()
    {
        $this->_response->setOutputPart('test-key', 'abc');
    
        $this->assertTrue($this->_response->hasOutputPart('test-key'));
        $this->assertEquals('abc', $this->_response->getOutputPart('test-key'));
    }
    
    public function testGetNotExistingOutputPart()
    {
        $this->assertFalse($this->_response->hasOutputPart('test-key'));
        $this->assertFalse($this->_response->getOutputPart('test-key'));
    }
    
    public function testGetOutputParts()
    {
        $this->_response->setOutputPart('test-key1', 'abc');
        $this->_response->setOutputPart('test-key2', 'def');
        $this->_response->setOutputPart('test-key3', 'ghi');
        
        $this->assertEquals(
            $this->_response->getOutputParts(),
            array(
                'test-key1' => 'abc',
                'test-key2' => 'def',
                'test-key3' => 'ghi'
            )
        );
    }
    
    public function testSetAndGetOutput()
    {
        $this->_response->setOutput('test-key abc');
    
        $this->assertTrue($this->_response->hasOutput());
        $this->assertEquals('test-key abc', $this->_response->getOutput());
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testRedirectToRelative()
    {
        $this->_requestAbstract->expects($this->once())
                               ->method('getFullRoute')
                               ->with($this->anything())
                               ->will($this->returnValue('http://location/test/abc/'));
        
        $this->_response->redirectTo('/test/abc/');
        
        $headers = xdebug_get_headers();
        
        $this->assertEquals('Location: http://location/test/abc/', $headers[0]);
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testRedirectToAbsolute()
    {
        $this->_response->redirectTo('http://turbo.zepi.net/');
    
        $headers = xdebug_get_headers();
    
        $this->assertEquals('Location: http://turbo.zepi.net/', $headers[0]);
    }
}
