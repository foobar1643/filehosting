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

class Factory
{
    private static $commentId = 1;

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
        $comment->setId(Factory::$commentId)
            ->setCommentText('Test comment')
            ->setAuthor('Anonymous');
        Factory::$commentId++;
        return $comment;
    }
}