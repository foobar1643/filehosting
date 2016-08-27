<?php

namespace Testsuite\Validation;

use PHPUnit\Framework\TestCase;

use Slim\Http\UploadedFile;
use Filehosting\Config;
use Filehosting\Entity\File;
use Filehosting\Helper\CommentHelper;
use Filehosting\Database\CommentMapper;
use Filehosting\Database\FileMapper;
use Filehosting\Entity\Comment;
use Filehosting\Validation\Validation;

class ValidationTest extends TestCase
{
    protected $validator;

    protected function setUp()
    {
        $container = new \Slim\Container();
        $container['config'] = function ($container) {
            $configMock = $this->createMock(Config::class);
            $configMock->method('getValue')->willReturn(2500000);
            return $configMock;
        };
        $container['CommentMapper'] = function ($container) {
            $mapperMock = $this->createMock(CommentMapper::class);
            return $mapperMock;
        };
        $container['FileMapper'] = function ($container) {
            $mapperMock = $this->createMock(FileMapper::class);
            $mapperMock->method('getFile')->willReturn(true);
            return $mapperMock;
        };
        $this->validator = new Validation($container);
    }

    public function testSuccessfulFileValidation()
    {
        $uploadedFile = new UploadedFile("/tmp/tempname", "example", "image/png", 1500000, UPLOAD_ERR_OK);
        $this->assertEmpty($this->validator->validateUploadedFile($uploadedFile));
    }

    public function testUnseccessfulFileValidation()
    {
        $file = new UploadedFile("/tmp/tempname", "example", "image/png", 1500000, UPLOAD_ERR_NO_FILE);
        $this->assertNotEmpty($this->validator->validateUploadedFile($file));
    }

    public function testSuccessfulCommentValidation()
    {
        $comment = new Comment();
        $comment->setCommentText("This is a test comment.");
        $this->assertEmpty($this->validator->validateComment($comment));
    }

    public function testUnsuccessfulCommentValidation()
    {
        $comment = new Comment();
        $this->assertNotEmpty($this->validator->validateComment($comment));
    }
}