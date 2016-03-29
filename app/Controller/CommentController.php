<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Model\Comment;
use \Filehosting\Helper\CommentHelper;

class CommentController
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function postComment(Request $request, Response $response, $args)
    {
        $comment = new Comment();
        $commentHelper = new CommentHelper();
        $commentMapper = $this->container->get('CommentMapper');
        $fileMapper = $this->container->get('FileMapper');
        $file = $fileMapper->getFile($args['id']);
        $postVars = $request->getParsedBody();
        if($file == null) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
        if(!isset($postVars['comment']) || trim($postVars['comment']) == "") {
            $responseHeader = $response->withHeader('Location', "/file/{$file->getId()}?error=comment");
            return $responseHeader;
        }
        $date = new \DateTime("now");
        $comment->setFileId($file->getId());
        $comment->setCommentText($postVars['comment']);
        $comment->setId($commentMapper->addComment($comment));
        $comment->setParentPath($commentHelper->normalizePath($comment->getId()));
        $commentMapper->updatePath($comment);
        $responseHeader = $response->withHeader('Location', "/file/{$file->getId()}");
        return $responseHeader;
    }

    public function postReply(Request $request, Response $response, $args)
    {
        $parentComment = new Comment();
        $commentHelper = new CommentHelper();
        $commentMapper = $this->container->get('CommentMapper');
        $fileMapper = $this->container->get('FileMapper');
        $file = $fileMapper->getFile($args['id']);
        $parentComment = $commentMapper->getComment($args['commentId']);
        $postVars = $request->getParsedBody();
        if($file == null || $parentComment == null) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
        if(!isset($postVars['comment']) || trim($postVars['comment']) == "") {
            $responseHeader = $response->withHeader('Location', "/file/{$file->getId()}?error=comment");
            return $responseHeader;
        }
        $replyComment = new Comment();
        $date = new \DateTime("now");
        $replyComment->setFileId($file->getId());
        $replyComment->setAuthor("Anonymous");
        $replyComment->setDatePosted($date->format(\DateTime::ATOM));
        $replyComment->setCommentText($postVars['comment']);
        $replyComment->setId($commentMapper->addComment($replyComment));
        $replyComment->setParentPath($commentHelper->normalizePath("{$parentComment->getParentPath()}.{$replyComment->getId()}"));
        $commentMapper->updatePath($replyComment);
        $responseHeader = $response->withHeader('Location', "/file/{$file->getId()}");
        return $responseHeader;
    }
}