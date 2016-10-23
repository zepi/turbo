<?php

namespace Tests\Zepi\Turbo\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->requestAbstract = $this->getMockBuilder('\\Zepi\\Turbo\\Request\\RequestAbstract')
                                       ->disableOriginalConstructor()
                                       ->getMock();
        
        $this->response = new \Zepi\Turbo\Response\Response($this->requestAbstract);
    }
    
    public function testSetAndGetData()
    {
        $this->response->setData('test-key', 'abc');
        
        $this->assertTrue($this->response->hasData('test-key'));
        $this->assertEquals('abc', $this->response->getData('test-key'));
    }
    
    public function testGetNotExistingData()
    {
        $this->assertFalse($this->response->hasData('test-key'));
        $this->assertFalse($this->response->getData('test-key'));
    }
    
    public function testSetAndGetOutputPart()
    {
        $this->response->setOutputPart('test-key', 'abc');
    
        $this->assertTrue($this->response->hasOutputPart('test-key'));
        $this->assertEquals('abc', $this->response->getOutputPart('test-key'));
    }
    
    public function testGetNotExistingOutputPart()
    {
        $this->assertFalse($this->response->hasOutputPart('test-key'));
        $this->assertFalse($this->response->getOutputPart('test-key'));
    }
    
    public function testGetOutputParts()
    {
        $this->response->setOutputPart('test-key1', 'abc');
        $this->response->setOutputPart('test-key2', 'def');
        $this->response->setOutputPart('test-key3', 'ghi');
        
        $this->assertEquals(
            $this->response->getOutputParts(),
            array(
                'test-key1' => 'abc',
                'test-key2' => 'def',
                'test-key3' => 'ghi'
            )
        );
    }
    
    public function testSetAndGetOutput()
    {
        $this->response->setOutput('test-key abc');
    
        $this->assertTrue($this->response->hasOutput());
        $this->assertEquals('test-key abc', $this->response->getOutput());
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testRedirectToRelative()
    {
        $this->requestAbstract->expects($this->once())
                               ->method('getFullRoute')
                               ->with($this->anything())
                               ->will($this->returnValue('http://location/test/abc/'));
        
        $this->response->redirectTo('/test/abc/');
        
        $headers = xdebug_get_headers();
        
        $this->assertEquals('Location: http://location/test/abc/', $headers[0]);
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testRedirectToAbsolute()
    {
        $this->response->redirectTo('http://turbo.zepi.net/');
    
        $headers = xdebug_get_headers();
    
        $this->assertEquals('Location: http://turbo.zepi.net/', $headers[0]);
    }
}
