<?php

namespace Testsuite\Entity;

use PHPUnit\Framework\TestCase;
use Filehosting\Entity\File;
use Testsuite\Utils\Factory;

class FileEntityTest extends TestCase
{
    protected $file;

    protected function setUp()
    {
        $this->file = Factory::fileFactory('exampleName.txt')->setId(436);
    }

    public function extensionsProvider()
    {
        return [
            'noExtension' => [Factory::fileFactory('fileWithoutExtension'), ""],
            'multipleExtensions' => [Factory::fileFactory('file.txt.mp3.png'), 'png'],
            'dotExtension' => [Factory::fileFactory('.dotFilename.bin'), "bin"],
            'numberExtension' => [Factory::fileFactory('example.5343'), "5343"]
        ];
    }

    /**
     * @dataProvider extensionsProvider
     */
    public function testGetExtension(File $file, $expected)
    {
        $this->assertEquals($expected, $file->getExtension());
    }

    public function testGetStrippedName()
    {
        $this->assertNotEmpty($this->file->getStrippedName());
        $this->assertNotEquals($this->file->getStrippedName(), Factory::fileFactory()->getStrippedName());
    }

    public function testGetFolder()
    {
        $this->assertNotEmpty($this->file->getFolder());
        $this->assertNotEquals($this->file->getFolder(), Factory::fileFactory()->getFolder());
    }

    public function testGetDiskName()
    {
        $this->assertNotEmpty($this->file->getDiskName());
        $this->assertNotEquals($this->file->getDiskName(), Factory::fileFactory()->getDiskName());
    }

    public function testGetThumbnailName()
    {
        $this->assertNotEmpty($this->file->getThumbnailName());
        $this->assertNotEquals($this->file->getThumbnailName(), Factory::fileFactory()->getThumbnailName());
    }
}