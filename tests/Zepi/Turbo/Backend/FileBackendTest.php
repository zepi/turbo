<?php

namespace Tests\Zepi\Turbo\Backend;

class FileBackendTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->path = tempnam(sys_get_temp_dir(), 'tur');
        $this->fileBackend = new \Zepi\Turbo\Backend\FileBackend($this->path);
    }
    
    public function tearDown()
    {
        @unlink($this->path);
    }
    
    public function testSaveToFile()
    {
        $this->fileBackend->saveToFile('test123');
    
        $content = file_get_contents($this->path);
        
        $this->assertEquals('test123', $content);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testSaveToFileOnExistingFileWhichIsntWriteable()
    {
        chmod($this->path, 0555);
        
        $this->fileBackend->saveToFile('test123');
    }
    
    public function testLoadFromFile()
    {
        file_put_contents($this->path, 'test123');

        $content = $this->fileBackend->loadFromfile();
    
        $this->assertEquals('test123', $content);
    }
    
    public function testLoadFromFileOnANotExistingFile()
    {
        @unlink($this->path);
        $content = $this->fileBackend->loadFromFile();
    
        $this->assertEquals('', $content);
    }
    
    public function testDeleteFile()
    {
        $this->assertTrue(file_exists($this->path));
        
        $result = $this->fileBackend->deleteFile();
    
        $this->assertTrue($result);
        $this->assertFalse(file_exists($this->path));
    }
    
    public function testDeleteNotExistingFile()
    {
        @unlink($this->path);
        $result = $this->fileBackend->deleteFile();
    
        $this->assertFalse($result);
    }
    
    public function testIsWriteable()
    {
        $result = $this->fileBackend->isWritable();
    
        $this->assertTrue($result);
    }
    
    public function testIsNotExistingFileWriteable()
    {
        @unlink($this->path);
        $result = $this->fileBackend->isWritable();
    
        $this->assertFalse($result);
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testLoadFromNotAccessibleFile()
    {
        chmod($this->path, 0000);
    
        $this->fileBackend->loadFromfile();
    }
    
    public function testAllFunctionsWithAdditionalPathRelative()
    {
        $this->path = sys_get_temp_dir() . '/' . uniqid('tur') . '/';
        $this->fileBackend = new \Zepi\Turbo\Backend\FileBackend($this->path);
        
        $additionalPath = uniqid('all');
        $testContent = 'test123';
        
        $resultSave = $this->fileBackend->saveToFile($testContent, $additionalPath);
        $resultLoad = $this->fileBackend->loadFromFile($additionalPath);
        $isWriteable = $this->fileBackend->isWritable($additionalPath);
        $resultDelete = $this->fileBackend->deleteFile($additionalPath);

        $this->assertEquals(strlen($testContent), $resultSave);
        $this->assertEquals($testContent, $resultLoad);
        $this->assertTrue($isWriteable);
        $this->assertTrue($resultDelete);
    }
    
    public function testAllFunctionsWithAdditionalPathAbsolute()
    {
        $this->path = sys_get_temp_dir() . '/' . uniqid('tur') . '/';
        $this->fileBackend = new \Zepi\Turbo\Backend\FileBackend($this->path);
    
        $additionalPath = sys_get_temp_dir() . '/' . uniqid('turall');
        $testContent = 'test123';
    
        $resultSave = $this->fileBackend->saveToFile($testContent, $additionalPath);
        $resultLoad = $this->fileBackend->loadFromFile($additionalPath);
        $isWriteable = $this->fileBackend->isWritable($additionalPath);
        $resultDelete = $this->fileBackend->deleteFile($additionalPath);
    
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
    
        $this->fileBackend->saveToFile('test123', $additionalPath);
    }
}
