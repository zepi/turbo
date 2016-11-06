<?php

namespace Tests\Zepi\Turbo\Manager;

class RouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->framework = $this->getMockBuilder('\\Zepi\\Turbo\\Framework')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $this->fileObjectBackend = $this->getMockBuilder('\\Zepi\\Turbo\\Backend\\FileObjectBackend')
                                         ->setConstructorArgs(array(TESTS_ROOT_DIR . '/modules-working/'))
                                         ->getMock();
        
        $this->moduleManager = $this->getMockBuilder('\\Zepi\\Turbo\\Manager\\ModuleManager')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        
        $this->request = $this->getMockBuilder('\\Zepi\\Turbo\\Request\\CliRequest')
                               ->setConstructorArgs(array('test route', array(), '/', 'en', 'linux'))
                               ->getMock();

        $this->request->expects($this->any())
                       ->method('getRouteDelimiter')
                       ->will($this->returnValue(' '));
        
        $this->fileObjectBackend->expects($this->once())
                                 ->method('loadObject');
        
        $this->routeManager = new \Zepi\Turbo\Manager\RouteManager($this->framework, $this->fileObjectBackend);
        $this->routeManager->initializeRoutingTable();
    }
    
    public function testAddRoute()
    {
        $this->request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
        
        $this->routeManager->addRoute('test|route', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->routeManager->getEventNameForRoute($this->request));
    }
    
    public function testAddRouteTwoTimes()
    {
        $this->request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->routeManager->addRoute('test|route', '\\Test\\Handler');
        $this->routeManager->addRoute('test|route', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->routeManager->getEventNameForRoute($this->request));
    }
    
    public function testAddAndRemoveRoute()
    {
        $this->request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->fileObjectBackend->expects($this->exactly(2))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->routeManager->addRoute('test|route', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->routeManager->getEventNameForRoute($this->request));
        
        $this->routeManager->removeRoute('test|route');
        
        $this->assertFalse($this->routeManager->getEventNameForRoute($this->request));
    }
    
    public function testRemoveRouteTwoTimes()
    {
        $this->request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->fileObjectBackend->expects($this->exactly(2))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->routeManager->addRoute('test|route', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->routeManager->getEventNameForRoute($this->request));
    
        $this->routeManager->removeRoute('test|route');
        $this->routeManager->removeRoute('test|route');
    
        $this->assertFalse($this->routeManager->getEventNameForRoute($this->request));
    }
    
    public function testRemoveNotAddedRoute()
    {
        $this->request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->fileObjectBackend->expects($this->never())
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->routeManager->removeRoute('test|route', '\\Test\\Handler');
    
        $this->assertFalse($this->routeManager->getEventNameForRoute($this->request));
    }
    
    public function testClearCache()
    {
        $this->fileObjectBackend->expects($this->never())
                                 ->method('saveObject')
                                 ->with($this->anything());
        
        $this->moduleManager->expects($this->once())
                             ->method('reactivateModules');
        
        $this->framework->expects($this->once())
                         ->method('getModuleManager')
                         ->will($this->returnValue($this->moduleManager));
    
        $this->routeManager->clearCache();
    }
    
    public function testCompletlyDifferentRoute()
    {
        $this->request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->routeManager->addRoute('abc|def|ghi', '\\Test\\Handler');
    
        $this->assertFalse($this->routeManager->getEventNameForRoute($this->request));
    }
    
    public function testEqualNumberOfPartsButNotEqualRoute()
    {
        $this->request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test route'));
        
        $this->fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->routeManager->addRoute('abc|def', '\\Test\\Handler');
    
        $this->assertFalse($this->routeManager->getEventNameForRoute($this->request));
    }
    
    public function testRouteWithRouteParameters()
    {
        $this->request->expects($this->any())
                       ->method('getRoute')
                       ->will($this->returnValue('test 1 asdf'));
        
        $this->request->expects($this->once())
                       ->method('setRouteParams')
                       ->with(array(1, 'asdf'));
        
        $this->fileObjectBackend->expects($this->exactly(1))
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->routeManager->addRoute('test|[d]|[s]', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->routeManager->getEventNameForRoute($this->request));
    }
    
    public function testRouteWithNamedRouteParameters()
    {
        $this->request->expects($this->any())
            ->method('getRoute')
            ->will($this->returnValue('test 2 asdf'));
    
        $this->request->expects($this->once())
            ->method('setRouteParams')
            ->with(array(2, 'asdf', 'id' => 2, 'name' => 'asdf'));
    
        $this->fileObjectBackend->expects($this->exactly(1))
            ->method('saveObject')
            ->with($this->anything());
    
        $this->routeManager->addRoute('test|[d:id]|[s:name]', '\\Test\\Handler');
    
        $this->assertEquals('\\Test\\Handler', $this->routeManager->getEventNameForRoute($this->request));
    }
}
