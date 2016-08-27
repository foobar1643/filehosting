<?php

namespace Testsuite\Utils;

use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\RequestBody;
use Slim\Http\UploadedFile;
use Slim\Http\Uri;
use Filehosting\Entity\Comment;
use Filehosting\Entity\TreeNode;
use Filehosting\Entity\File;

class Factory
{
    private static $fileId = 0;
    private static $commentId = 0;

    public static function requestFactory($envData = [], $uriString = 'https://example.com/en/')
    {
        $env = Environment::mock($envData);
        $uri = Uri::createFromString($uriString);
        $headers = Headers::createFromEnvironment($env);
        $cookies = [];
        $serverParams = $env->all();
        $body = new RequestBody();
        $uploadedFiles = UploadedFile::createFromEnvironment($env);
        $request = new Request('GET', $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);
        return $request;
    }

    public static function commentFactory()
    {
        $comment = new Comment();
        $comment->setId(Factory::$commentId++)
            ->setCommentText('Test comment')
            ->setAuthor('Anonymous');
        return $comment;
    }

    public static function treeNodeFactory()
    {
        return new TreeNode(Factory::commentFactory());
    }

    public static function fileFactory($name = 'example.txt', $size = 250000, $uploader = 'Test')
    {
        $file = new File();
        return $file->setId(Factory::$fileId++)
            ->setName($name)
            ->setSize($size)
            ->setUploader($uploader);
    }
}