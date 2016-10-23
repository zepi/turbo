<?php

namespace Tests\Zepi\Turbo\Manager;

class ModuleManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->framework = $this->getMockBuilder('\\Zepi\\Turbo\\Framework')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $this->fileObjectBackend = $this->getMockBuilder('\\Zepi\\Turbo\\Backend\\FileObjectBackend')
                                         ->setConstructorArgs(array(TESTS_ROOT_DIR . '/modules-working/'))
                                         ->getMock();
        
        $this->fileObjectBackend->expects($this->once())
                                 ->method('loadObject');
        
        $this->moduleManager = new \Zepi\Turbo\Manager\ModuleManager($this->framework, $this->fileObjectBackend);
        $this->moduleManager->initializeModuleSystem();
    }
    
    public function testInitializeManagerTwoTimes()
    {
        $this->fileObjectBackend = $this->getMockBuilder('\\Zepi\\Turbo\\Backend\\FileObjectBackend')
                                         ->setConstructorArgs(array(TESTS_ROOT_DIR . '/modules-working/'))
                                         ->getMock();
        
        $path = TESTS_ROOT_DIR . '/modules-working/TestModule';
        $length = strlen($path);
        $this->fileObjectBackend->expects($this->exactly(2))
                                 ->method('loadObject')
                                 ->willReturn(unserialize('a:1:{s:12:"\TestModule\";a:2:{s:7:"version";s:3:"1.0";s:4:"path";s:' . $length . ':"' . $path . '";}}'));
        
        $this->moduleManager = new \Zepi\Turbo\Manager\ModuleManager($this->framework, $this->fileObjectBackend);
        $this->moduleManager->initializeModuleSystem();
        
        $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
    
        $this->moduleManager->initializeModuleSystem();
    }
    
    public function testActivateRegisterAModuleDirectoryTwoTimes()
    {
        $resultOne = $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $resultTwo = $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
    
        $this->assertTrue($resultOne);
        $this->assertFalse($resultTwo);
    }
    
    public function testActivateModule()
    {
        $this->fileObjectBackend->expects($this->once())
                                 ->method('saveObject')
                                 ->with($this->anything());
        
        $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $this->moduleManager->activateModule('TestModule');
        $modules = $this->moduleManager->getModules();
        
        $this->assertEquals(count($modules), 1);
        $this->assertInstanceOf('\\TestModule\\Module', $modules['\\TestModule\\']);
    }
    
    public function testActivateModuleTwoTimes()
    {
        $this->fileObjectBackend->expects($this->once())
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $resultOne = $this->moduleManager->activateModule('TestModule');
        $resultTwo = $this->moduleManager->activateModule('TestModule');
    
        $this->assertEquals($resultOne, $resultTwo);
        $this->assertTrue($resultOne);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testActivateNotExistingModule()
    {
        $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $resultTwo = $this->moduleManager->activateModule('TestModule2');
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testActivateModuleWithoutModuleClass()
    {
        $resultOne = $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-not-working/', false);
        $this->moduleManager->activateModule('WrongModule2');
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testActivateModuleInTestsDirectory()
    {
        $resultOne = $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $this->moduleManager->activateModule('TestModule');
    }
    
    public function testDeactivateModule()
    {
        $this->fileObjectBackend->expects($this->exactly(2))
                                 ->method('saveObject')
                                 ->with($this->anything());
        
        $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $this->moduleManager->activateModule('TestModule');
    
        $this->moduleManager->deactivateModule('TestModule');

        $modules = $this->moduleManager->getModules();

        $this->assertEquals(count($modules), 0);
    }
    
    public function testDeactivateNotActivatedModule()
    {
        $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
    
        $result = $this->moduleManager->deactivateModule('TestModule');
    
        $this->assertFalse($result);
    }
    
    public function testGetModules()
    {
        $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $this->moduleManager->activateModule('TestModule');
        $modules = $this->moduleManager->getModules();

        $this->assertEquals(count($modules), 1);
        $this->assertInstanceOf('\\TestModule\\Module', $modules['\\TestModule\\']);
    }
    
    public function testGetModule()
    {
        $resultOne = $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $this->moduleManager->activateModule('TestModule');
    
        $this->assertInstanceOf('\\TestModule\\Module', $this->moduleManager->getModule('\\TestModule\\'));
    }
    
    public function testGetWrongModule()
    {
        $resultOne = $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $this->moduleManager->activateModule('TestModule');
    
        $this->assertFalse($this->moduleManager->getModule('\\TestModule2\\'));
    }

    public function testGetModuleProperties()
    {
        $properties = $this->moduleManager->getModuleProperties(TESTS_ROOT_DIR . '/modules-working/TestModule/');
        
        $this->assertObjectHasAttribute('module', $properties);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testGetModulePropertiesWithIncompleteModule()
    {
        $properties = $this->moduleManager->getModuleProperties(TESTS_ROOT_DIR . '/modules-not-working/WrongModule/');
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testNotCorrectModuleIniWillThrowException()
    {
        $this->moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-not-working/', false);
    
        $this->moduleManager->activateModule('WrongModule3');
    }
}
