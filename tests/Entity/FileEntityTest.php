<?php

namespace Testsuite\Entity;

use PHPUnit\Framework\TestCase;
use Filehosting\Entity\File;

class FileEntityTest extends TestCase
{
    protected static $file;

    public static function setUpBeforeClass()
    {
        self::$file = new File();
        self::$file->setId(368)
            ->setName('example.png')
            ->setSize(2500000)
            ->setUploader('Test');
    }

    public function testGetExtension()
    {
        $this->assertEquals('png', self::$file->getExtention());
    }

    public function testGetStrippedName()
    {
        $this->assertEquals('example', self::$file->getStrippedName());
    }

    public function testGetFolder()
    {
        $this->assertEquals(300, self::$file->getFolder());
    }

    public function testGetDownloadLink()
    {
        $name = urlencode(self::$file->getClientFilename());
        $this->assertEquals("/file/get/" . self::$file->getId() ."/{$name}", self::$file->getDownloadLink());
    }

    public function testGetDiskName()
    {
        $diskName = self::$file->getDiskName();
        $this->assertEquals(self::$file->getId() . "_" . self::$file->getClientFilename(), $diskName);
        return $diskName;
    }

    /**
     * @depends testGetDiskName
     */
    public function testGetThumbnailName($diskName)
    {
        $this->assertEquals("thumb_{$diskName}", self::$file->getThumbnailName());
    }

}