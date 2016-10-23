<?php

namespace Tests\Zepi\Turbo\Backend;

class FileObjectBackendTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->path = tempnam(sys_get_temp_dir(), 'tur');
        $this->fileObjectBackend = new \Zepi\Turbo\Backend\FileObjectBackend($this->path);
    }
    
    public function tearDown()
    {
        @unlink($this->path);
    }
    
    public function testSaveObject()
    {
        $object = new \stdClass();
        $object->test = true;
        $object->time = date('H:i:s');
                
        $this->fileObjectBackend->saveObject($object);
    
        $content = file_get_contents($this->path);
        
        $this->assertEquals($object, unserialize($content));
    }
    
    /**
     * @expectedException \Zepi\Turbo\Exception
     */
    public function testSaveObjectOnExistingFileWhichIsntWriteable()
    {
        $object = new \stdClass();
        $object->test = true;
        $object->time = date('H:i:s');

        chmod($this->path, 0555);
        
        $this->fileObjectBackend->saveObject($object);
    }
    
    public function testLoadObject()
    {
        $object = new \stdClass();
        $object->test = true;
        $object->time = date('H:i:s');

        file_put_contents($this->path, serialize($object));

        $loadedObject = $this->fileObjectBackend->loadObject();
    
        $this->assertEquals($object, $loadedObject);
    }
    
    public function testLoadObjectFromNotExistingFile()
    {
        $loadedObject = $this->fileObjectBackend->loadObject();
    
        $this->assertFalse($loadedObject);
    }
}
