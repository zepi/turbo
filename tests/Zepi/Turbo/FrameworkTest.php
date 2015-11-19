<?php

namespace Tests\Zepi\Turbo;

class FrameworkTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        shell_exec('mkdir ' . TESTS_ROOT_DIR . '/data');
    }
    
    public function tearDown()
    {
        \Zepi\Turbo\Framework::resetFramework();
        shell_exec('rm -r ' . TESTS_ROOT_DIR . '/data');
    }
    
    public function testCanInitiateFramework()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
        
        $this->assertInstanceOf('\\Zepi\\Turbo\\Framework', $framework);
    }
    
    public function testGetRootDirectory()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertEquals($framework->getRootDirectory(), TESTS_ROOT_DIR . '/');
    }
    
    public function testGetDataSourceManager()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertInstanceOf('\\Zepi\\Turbo\\Manager\\DataSourceManager', $framework->getDataSourceManager());
    }
    
    public function testGetModuleManager()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertInstanceOf('\\Zepi\\Turbo\\Manager\\ModuleManager', $framework->getModuleManager());
    }
    
    public function testGetRuntimeManager()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertInstanceOf('\\Zepi\\Turbo\\Manager\\RuntimeManager', $framework->getRuntimeManager());
    }
    
    public function testGetRouteManager()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertInstanceOf('\\Zepi\\Turbo\\Manager\\RouteManager', $framework->getRouteManager());
    }
    
    public function testGetRequestManager()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertInstanceOf('\\Zepi\\Turbo\\Manager\\RequestManager', $framework->getRequestManager());
    }
    
    public function testGetRequest()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertInstanceOf('\\Zepi\\Turbo\\Request\\CliRequest', $framework->getRequest());
    }
    
    public function testGetResponse()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertInstanceOf('\\Zepi\\Turbo\\Response\\Response', $framework->getResponse());
    }
    
    public function testPrepareClassName()
    {
        $preparedClassName = \Zepi\Turbo\Framework::prepareClassName('Zepi\\Turbo\\Framework');
        $this->assertEquals('\\Zepi\\Turbo\\Framework', $preparedClassName);
    }
    
    public function testPrepareNamespace()
    {
        $preparedNamespace = \Zepi\Turbo\Framework::prepareNamespace('Zepi\\Turbo');
        $this->assertEquals('\\Zepi\\Turbo\\', $preparedNamespace);
    }

    public function testGetZepiTurboClassObject()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $this->assertInstanceOf('\\Zepi\\Turbo\\Exception', $framework->getInstance('\\Zepi\\Turbo\\Exception'));
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testGetWrongZepiTurboObjectException()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
        
        $framework->getInstance('\\Zepi\\Turbo\\NotExisting');
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testGetNotActivatedModuleObjectException()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
    
        $framework->getInstance('\\Module\\Test\\NotExisting');
    }
    
    public function testActivateModuleAndGetInstanceOfModuleClass()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
        $framework->getModuleManager()->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $framework->getModuleManager()->activateModule('TestModule');
    
        $this->assertInstanceOf('\\TestModule\\EmptyBackend', $framework->getInstance('\\TestModule\\EmptyBackend'));
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testActivateModuleAndGetInstanceOfNotExistingModuleClass()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
        $framework->getModuleManager()->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $framework->getModuleManager()->activateModule('TestModule');
    
        $framework->getInstance('\\TestModule\\EmptyBackend2');
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testActivateModuleAndGetInstanceOfNotCorrectlyInitiatedModuleClass()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
        $framework->getModuleManager()->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $framework->getModuleManager()->activateModule('TestModule');
    
        $framework->getInstance('\\TestModule\\EmptyBackend3');
    }
    
    public function testFrameworkExecuteWithValidRoute()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
        $framework->getModuleManager()->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $framework->getModuleManager()->activateModule('TestModule');
        
        $framework->getRequest()->setRoute('executionTest');
        $framework->getRouteManager()->addRoute('executionTest', '\\TestModule\\TestEventName');
        
        $events = array(
            '\\Zepi\\Turbo\\Event\\BeforeExecution' => 1,
            '\\Zepi\\Turbo\\Event\\AfterExecution' => 1,
            '\\Zepi\\Turbo\\Event\\FinalizeOutput' => 1,
            '\\Zepi\\Turbo\\Event\\BeforeOutput' => 1,
            '\\Zepi\\Turbo\\Event\\AfterOutput' => 1,
            '\\TestModule\\TestEventName' => 1,
        );
        foreach ($events as $key => $number) {
            $framework->getRuntimeManager()->addEventHandler($key, '\\TestModule\\TestEventHandler');
        }
    
        $framework->execute();
    
        $this->assertEquals($events, \TestModule\TestEventHandler::$executedEvents);
        \TestModule\TestEventHandler::$executedEvents = array();
    }
    
    public function testFrameworkExecuteRouteNotFound()
    {
        $framework = \Zepi\Turbo\Framework::getFrameworkInstance(TESTS_ROOT_DIR . '/');
        $framework->getModuleManager()->registerModuleDirectory(TESTS_ROOT_DIR . '/modules-working/', false);
        $framework->getModuleManager()->activateModule('TestModule');
        
        $framework->getRequest()->setRoute('executionTest2');
        
        $events = array(
            '\\Zepi\\Turbo\\Event\\BeforeExecution' => 1,
            '\\Zepi\\Turbo\\Event\\AfterExecution' => 1,
            '\\Zepi\\Turbo\\Event\\FinalizeOutput' => 1,
            '\\Zepi\\Turbo\\Event\\BeforeOutput' => 1,
            '\\Zepi\\Turbo\\Event\\AfterOutput' => 1,
            '\\Zepi\\Turbo\\Event\\RouteNotFound' => 1
        );
        foreach ($events as $key => $number) {
            $framework->getRuntimeManager()->addEventHandler($key, '\\TestModule\\TestEventHandler');
        }
    
        $framework->execute();
        
        $this->assertEquals($events, \TestModule\TestEventHandler::$executedEvents);
        \TestModule\TestEventHandler::$executedEvents = array();
    }
}
