<?php

namespace Tests\Zepi\Turbo\Backend;

class FileObjectBackendTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_path = tempnam(sys_get_temp_dir(), 'tur');
        $this->_fileObjectBackend = new \Zepi\Turbo\Backend\FileObjectBackend($this->_path);
    }
    
    public function tearDown()
    {
        @unlink($this->_path);
    }
    
    public function testSaveObject()
    {
        $object = new \stdClass();
        $object->test = true;
        $object->time = date('H:i:s');
                
        $this->_fileObjectBackend->saveObject($object);
    
        $content = file_get_contents($this->_path);
        
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

        chmod($this->_path, 0555);
        
        $this->_fileObjectBackend->saveObject($object);
    }
    
    public function testLoadObject()
    {
        $object = new \stdClass();
        $object->test = true;
        $object->time = date('H:i:s');

        file_put_contents($this->_path, serialize($object));

        $loadedObject = $this->_fileObjectBackend->loadObject();
    
        $this->assertEquals($object, $loadedObject);
    }
    
    public function testLoadObjectFromNotExistingFile()
    {
        $loadedObject = $this->_fileObjectBackend->loadObject();
    
        $this->assertFalse($loadedObject);
    }
}
