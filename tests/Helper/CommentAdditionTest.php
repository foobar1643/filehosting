<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Filehosting\Helper\CommentHelper;
use Filehosting\Database\CommentMapper;
use Filehosting\Entity\Comment;

class CommentAdditionTest extends TestCase
{
    protected $commentHelper;

    protected function setUp()
    {
        $commentMock = new Comment();
        $commentMock->setParentPath("001");
        $mapperMock = $this->createMock(CommentMapper::class);
        $mapperMock->method('addComment')->willReturn(1);
        $mapperMock->method('getComment')->willReturn($commentMock);
        $mapperMock->method('updatePath')->willReturn(true);
        $this->commentHelper = new CommentHelper($mapperMock);
    }

    public function testRootCommentAddition()
    {
        $actual = new Comment();
        $expected = new Comment();
        $expected->setId(1);
        $this->assertEquals($expected, $this->commentHelper->addComment($actual));
        return $expected;
    }

    /**
     * @depends testRootCommentAddition
     */
    public function testChildCommentAddition(Comment $rootComment)
    {
        $actual = new Comment();
        $actual->setParentId($rootComment->getId());
        $expected = new Comment();
        $expected->setId(1)->setParentId(1);
        $expected->addToPath('001');
        $this->assertEquals($expected, $this->commentHelper->addComment($actual));
    }
}