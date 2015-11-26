<?php

namespace Tests\Zepi\Turbo\Backend;

class FileBackendTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_path = tempnam(sys_get_temp_dir(), 'tur');
        $this->_fileBackend = new \Zepi\Turbo\Backend\FileBackend($this->_path);
    }
    
    public function tearDown()
    {
        @unlink($this->_path);
    }
    
    public function testSaveToFile()
    {
        $this->_fileBackend->saveToFile('test123');
    
        $content = file_get_contents($this->_path);
        
        $this->assertEquals('test123', $content);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testSaveToFileOnExistingFileWhichIsntWriteable()
    {
        chmod($this->_path, 0555);
        
        $this->_fileBackend->saveToFile('test123');
    }
    
    public function testLoadFromFile()
    {
        file_put_contents($this->_path, 'test123');

        $content = $this->_fileBackend->loadFromfile();
    
        $this->assertEquals('test123', $content);
    }
    
    public function testLoadFromFileOnANotExistingFile()
    {
        @unlink($this->_path);
        $content = $this->_fileBackend->loadFromFile();
    
        $this->assertEquals('', $content);
    }
    
    public function testDeleteFile()
    {
        $this->assertTrue(file_exists($this->_path));
        
        $result = $this->_fileBackend->deleteFile();
    
        $this->assertTrue($result);
        $this->assertFalse(file_exists($this->_path));
    }
    
    public function testDeleteNotExistingFile()
    {
        @unlink($this->_path);
        $result = $this->_fileBackend->deleteFile();
    
        $this->assertFalse($result);
    }
    
    public function testIsWriteable()
    {
        $result = $this->_fileBackend->isWritable();
    
        $this->assertTrue($result);
    }
    
    public function testIsNotExistingFileWriteable()
    {
        @unlink($this->_path);
        $result = $this->_fileBackend->isWritable();
    
        $this->assertFalse($result);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testLoadFromNotAccessibleFile()
    {
        chmod($this->_path, 0000);
    
        $this->_fileBackend->loadFromfile();
    }
    
    public function testAllFunctionsWithAdditionalPathRelative()
    {
        $this->_path = sys_get_temp_dir() . '/' . uniqid('tur') . '/';
        $this->_fileBackend = new \Zepi\Turbo\Backend\FileBackend($this->_path);
        
        $additionalPath = uniqid('all');
        $testContent = 'test123';
        
        $resultSave = $this->_fileBackend->saveToFile($testContent, $additionalPath);
        $resultLoad = $this->_fileBackend->loadFromFile($additionalPath);
        $isWriteable = $this->_fileBackend->isWritable($additionalPath);
        $resultDelete = $this->_fileBackend->deleteFile($additionalPath);

        $this->assertEquals(strlen($testContent), $resultSave);
        $this->assertEquals($testContent, $resultLoad);
        $this->assertTrue($isWriteable);
        $this->assertTrue($resultDelete);
    }
    
    public function testAllFunctionsWithAdditionalPathAbsolute()
    {
        $this->_path = sys_get_temp_dir() . '/' . uniqid('tur') . '/';
        $this->_fileBackend = new \Zepi\Turbo\Backend\FileBackend($this->_path);
    
        $additionalPath = sys_get_temp_dir() . '/' . uniqid('turall');
        $testContent = 'test123';
    
        $resultSave = $this->_fileBackend->saveToFile($testContent, $additionalPath);
        $resultLoad = $this->_fileBackend->loadFromFile($additionalPath);
        $isWriteable = $this->_fileBackend->isWritable($additionalPath);
        $resultDelete = $this->_fileBackend->deleteFile($additionalPath);
    
        $this->assertEquals(strlen($testContent), $resultSave);
        $this->assertEquals($testContent, $resultLoad);
        $this->assertTrue($isWriteable);
        $this->assertTrue($resultDelete);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testSaveToFileAndCreateDirectoryWithoutAccess()
    {
        $additionalPath = sys_get_temp_dir() . '/' . uniqid('turall');
        mkdir($additionalPath, 0000);
        $additionalPath .= '/' . uniqid('exc') . '/' . uniqid();
    
        $this->_fileBackend->saveToFile('test123', $additionalPath);
    }
}
