<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Filehosting\Helper\PaginationHelper;

class PaginationHelperTest extends TestCase
{
    protected static $pager;

    public static function setUpBeforeClass()
    {
        self::$pager = new PaginationHelper(15, "example.php");
        self::$pager->setTotalRecords(132);
    }

    public function testPageCount()
    {
        $this->assertEquals(9, self::$pager->getPages());
    }

    public function testOffset()
    {
        $this->assertEquals(60, self::$pager->getOffset(5));
    }

    public function testLink()
    {
        $link = self::$pager->getLink(5);
        $this->assertNotEmpty(self::$pager->getLink(5));
        $this->assertNotEquals(self::$pager->getLink(5), self::$pager->getLink(2));
        $this->assertFalse(strpos(" ", self::$pager->getLink(5)));
    }

    public function testPageChecking()
    {
        $this->assertEquals(5, self::$pager->checkPage(5));
    }
}