<?php

namespace Testsuite\Entity;

use PHPUnit\Framework\TestCase;
use Filehosting\Entity\Comment;
use Testsuite\Utils\Factory;

class CommentEntityTest extends TestCase
{
    protected static $rootComment;
    protected static $deepComment;

    public static function setUpBeforeClass()
    {
        self::$rootComment = Factory::commentFactory();
        for($i = 0; $i < 3; $i++) {
            $comment = Factory::commentFactory();
            $comment->addChildNode(Factory::commentFactory());
            self::$rootComment->addChildNode($comment);
        }
        $children = self::$rootComment->getChildren();
        self::$deepComment = $children[6]->getChildren()[7]->addChildNode(Factory::commentFactory());
    }

    public function testDescendantsCount()
    {
        $this->assertEquals(7, self::$rootComment->countDescendants());
    }

    public function testGetDepth()
    {
        $this->assertEquals(3, self::$deepComment->getDepth());
    }
}