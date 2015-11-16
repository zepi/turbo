<?php

namespace Tests\Zepi\Turbo\Manager;

class ModuleManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_framework = $this->getMockBuilder('\\Zepi\\Turbo\\Framework')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $this->_fileObjectBackend = $this->getMockBuilder('\\Zepi\\Turbo\\Backend\\FileObjectBackend')
                                         ->setConstructorArgs(array(TESTS_ROOT_DIR . '/modules-working/'))
                                         ->getMock();
        
        $this->_fileObjectBackend->expects($this->once())
                                 ->method('loadObject');
        
        $this->_moduleManager = new \Zepi\Turbo\Manager\ModuleManager($this->_framework, $this->_fileObjectBackend);
        $this->_moduleManager->initializeModuleSystem();
    }
    
    //
    public function testInitializeManagerTwoTimes()
    {
        $this->_fileObjectBackend = $this->getMockBuilder('\\Zepi\\Turbo\\Backend\\FileObjectBackend')
                                         ->setConstructorArgs(array(TESTS_ROOT_DIR . '/modules-working/'))
                                         ->getMock();
        
        $this->_fileObjectBackend->expects($this->exactly(2))
                                 ->method('loadObject')
                                 ->willReturn(unserialize('a:1:{s:12:"\TestModule\";a:2:{s:7:"version";s:3:"1.0";s:4:"path";s:47:"/var/www/turbo/tests/modules-working/TestModule";}}'));
        
        $this->_moduleManager = new \Zepi\Turbo\Manager\ModuleManager($this->_framework, $this->_fileObjectBackend);
        $this->_moduleManager->initializeModuleSystem();
        
        $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
    
        $this->_moduleManager->initializeModuleSystem();
    }
    
    public function testActivateRegisterAModuleDirectoryTwoTimes()
    {
        $resultOne = $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $resultTwo = $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
    
        $this->assertTrue($resultOne);
        $this->assertFalse($resultTwo);
    }
    
    public function testActivateModule()
    {
        $this->_fileObjectBackend->expects($this->once())
                                 ->method('saveObject')
                                 ->with($this->anything());
        
        $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $this->_moduleManager->activateModule('TestModule');
        $modules = $this->_moduleManager->getModules();
        
        $this->assertEquals(count($modules), 1);
        $this->assertInstanceOf('\\TestModule\\Module', $modules['\\TestModule\\']);
    }
    
    public function testActivateModuleTwoTimes()
    {
        $this->_fileObjectBackend->expects($this->once())
                                 ->method('saveObject')
                                 ->with($this->anything());
    
        $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $resultOne = $this->_moduleManager->activateModule('TestModule');
        $resultTwo = $this->_moduleManager->activateModule('TestModule');
    
        $this->assertEquals($resultOne, $resultTwo);
        $this->assertTrue($resultOne);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testActivateNotExistingModule()
    {
        $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $resultTwo = $this->_moduleManager->activateModule('TestModule2');
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testActivateModuleWithoutModuleClass()
    {
        $resultOne = $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-not-working/');
        $this->_moduleManager->activateModule('WrongModule2');
    }
    
    public function testDeactivateModule()
    {
        $this->_fileObjectBackend->expects($this->exactly(2))
                                 ->method('saveObject')
                                 ->with($this->anything());
        
        $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $this->_moduleManager->activateModule('TestModule');
    
        $this->_moduleManager->deactivateModule('TestModule');

        $modules = $this->_moduleManager->getModules();

        $this->assertEquals(count($modules), 0);
    }
    
    public function testDeactivateNotActivatedModule()
    {
        $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
    
        $result = $this->_moduleManager->deactivateModule('TestModule');
    
        $this->assertFalse($result);
    }
    
    public function testGetModules()
    {
        $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $this->_moduleManager->activateModule('TestModule');
        $modules = $this->_moduleManager->getModules();

        $this->assertEquals(count($modules), 1);
        $this->assertInstanceOf('\\TestModule\\Module', $modules['\\TestModule\\']);
    }
    
    public function testGetModule()
    {
        $resultOne = $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $this->_moduleManager->activateModule('TestModule');
    
        $this->assertInstanceOf('\\TestModule\\Module', $this->_moduleManager->getModule('\\TestModule\\'));
    }
    
    public function testGetWrongModule()
    {
        $resultOne = $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/');
        $this->_moduleManager->activateModule('TestModule');
    
        $this->assertFalse($this->_moduleManager->getModule('\\TestModule2\\'));
    }

    public function testGetModuleProperties()
    {
        $properties = $this->_moduleManager->getModuleProperties(TESTS_ROOT_DIR . '/modules-working/TestModule/');
        
        $this->assertArrayHasKey('module', $properties);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testGetModulePropertiesWithIncompleteModule()
    {
        $properties = $this->_moduleManager->getModuleProperties(TESTS_ROOT_DIR . '/modules-not-working/WrongModule/');
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testNotCorrectModuleIniWillThrowException()
    {
        $this->_moduleManager->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-not-working/');
    
        $this->_moduleManager->activateModule('WrongModule3');
    }
}
