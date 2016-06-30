<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Filehosting\Helper\IdHelper;
use Filehosting\Helper\PathingHelper;

class IdHelperTest extends TestCase
{
    protected static $idHelper;

    public static function setUpBeforeClass()
    {
        self::$idHelper = new IdHelper(new \getID3(), new PathingHelper(__DIR__));
    }

    public function testPreviewableType()
    {
        $this->assertTrue(self::$idHelper->isPreviewable(['mime_type' => "image/jpeg"]));
        $this->assertFalse(self::$idHelper->isPreviewable(['mime_type' => "application/json"]));
    }

    public function testAudioPlayableType()
    {
        $this->assertTrue(self::$idHelper->isAudio(['mime_type' => "audio/mpeg"]));
        $this->assertFalse(self::$idHelper->isAudio(['mime_type' => "application/json"]));
    }

    public function testVideoPlayableType()
    {
        $this->assertTrue(self::$idHelper->isVideo(['mime_type' => "video/webm"]));
        $this->assertFalse(self::$idHelper->isVideo(['mime_type' => "application/json"]));
    }
}
