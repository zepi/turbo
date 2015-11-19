<?php

namespace Tests\Zepi\Turbo\Manager;

class RouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_framework = $this->getMockBuilder('\\Zepi\\Turbo\\Framework')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $this->_fileObjectBackend = $this->getMockBuilder('\\Zepi\\Turbo\\Backend\\FileObjectBackend')
                                         ->setConstructorArgs(array(TESTS_ROOT_DIR . '/modules-working/'))
                                         ->getMock();
        
        $this->_moduleManager = $this->getMockBuilder('\\Zepi\\Turbo\\Manager\\ModuleManager')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        
        $this->_request = $this->getMockBuilder('\\Zepi\\Turbo\\Request\\CliRequest')
                               ->setConstructorArgs(array('test route', array(), '/', 'en'))
                               ->getMock();

        $this->_request->expects($this->any())
                       ->method('getRouteDelimiter')
                       ->will($this->returnValue(' '));
        
        $this->_fileObjectBackend->expects($this->once())
                                 ->method('loadObject');
        
        $this->_routeManager = new \Zepi\Turbo\Manager\RouteManager($this->_framework, $this->_fileObjectBackend);
        $this->_routeManager->initializeRoutingTable();
    }
    
    public function testAddRoute()
    {
        $this->_request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->_fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
        
        $this->_routeManager->addRoute('test|route', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->_routeManager->getEventNameForRoute($this->_request));
    }
    
    public function testAddRouteTwoTimes()
    {
        $this->_request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->_fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->_routeManager->addRoute('test|route', '\\Test\\Handler');
        $this->_routeManager->addRoute('test|route', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->_routeManager->getEventNameForRoute($this->_request));
    }
    
    public function testAddAndRemoveRoute()
    {
        $this->_request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->_fileObjectBackend->expects($this->exactly(2))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->_routeManager->addRoute('test|route', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->_routeManager->getEventNameForRoute($this->_request));
        
        $this->_routeManager->removeRoute('test|route', '\\Test\\Handler');
        
        $this->assertFalse($this->_routeManager->getEventNameForRoute($this->_request));
    }
    
    public function testRemoveRouteTwoTimes()
    {
        $this->_request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->_fileObjectBackend->expects($this->exactly(2))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->_routeManager->addRoute('test|route', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->_routeManager->getEventNameForRoute($this->_request));
    
        $this->_routeManager->removeRoute('test|route', '\\Test\\Handler');
        $this->_routeManager->removeRoute('test|route', '\\Test\\Handler');
    
        $this->assertFalse($this->_routeManager->getEventNameForRoute($this->_request));
    }
    
    public function testRemoveNotAddedRoute()
    {
        $this->_request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->_fileObjectBackend->expects($this->never())
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->_routeManager->removeRoute('test|route', '\\Test\\Handler');
    
        $this->assertFalse($this->_routeManager->getEventNameForRoute($this->_request));
    }
    
    public function testClearCache()
    {
        $this->_fileObjectBackend->expects($this->never())
                                 ->method('saveObject')
                                 ->with($this->anything());
        
        $this->_moduleManager->expects($this->once())
                             ->method('reactivateModules');
        
        $this->_framework->expects($this->once())
                         ->method('getModuleManager')
                         ->will($this->returnValue($this->_moduleManager));
    
        $this->_routeManager->clearCache();
    }
    
    public function testCompletlyDifferentRoute()
    {
        $this->_request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->_fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->_routeManager->addRoute('abc|def|ghi', '\\Test\\Handler');
    
        $this->assertFalse($this->_routeManager->getEventNameForRoute($this->_request));
    }
    
    public function testEqualNumberOfPartsButNotEqualRoute()
    {
        $this->_request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->_fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->_routeManager->addRoute('abc|def', '\\Test\\Handler');
    
        $this->assertFalse($this->_routeManager->getEventNameForRoute($this->_request));
    }
    
    public function testRouteWithRouteParameters()
    {
        $this->_request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test 1 asdf'));
        
        $this->_request->expects($this->once())
                       ->method('setRouteParams')
                       ->with(array(1, 'asdf'));
        
        $this->_fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->_routeManager->addRoute('test|[d]|[s]', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->_routeManager->getEventNameForRoute($this->_request));
    }
}
