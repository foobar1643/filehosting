<?php

namespace Testsuite\Entity;

use PHPUnit\Framework\TestCase;
use Testsuite\Utils\Factory;

class CommentEntityTest extends TestCase
{
    public function testGetDepth()
    {
        $comment = Factory::commentFactory();
        $comment->setMatPath('001.003.005.008');
        $this->assertEquals(4, $comment->getDepth());
    }
}