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
        $commentHelper = $this->container->get('CommentHelper');
        $commentMapper = $this->container->get('CommentMapper');
        $fileMapper = $this->container->get('FileMapper');
        $file = $fileMapper->getFile($args['id']);
        $postVars = $request->getParsedBody();
        $jsonResponse = ["error" => false, "result" => null, "type" => "comment"];
        if($file == null) {
            $jsonResponse["error"] = 1;
        } else {
            if(!isset($postVars['comment']) || trim($postVars['comment']) == "") {
                $jsonResponse["error"] = 2;
            } else {
                $dateTime = new \DateTime("now");
                $comment->setFileId($file->getId());
                $comment->setAuthor("Anonymous");
                $comment->setDatePosted($dateTime->format(\DateTime::ATOM));
                $comment->setCommentText($postVars['comment']);
                $comment->setId($commentMapper->addComment($comment));
                $comment->setParentPath($commentHelper->normalizePath($comment->getId()));
                $commentMapper->updatePath($comment);
                $jsonResponse["result"] = ["id" => $comment->getId(),
                    "parentId" => null,
                    "author" => $comment->getAuthor(),
                    "date" => $comment->getDatePosted(),
                    "text" => $comment->getCommentText(),
                    "path" => $comment->getParentPath()];
            }
        }
        print(json_encode($jsonResponse));
        return $response;
    }

    public function postReply(Request $request, Response $response, $args)
    {
        $parentComment = new Comment();
        $commentHelper = $this->container->get('CommentHelper');
        $commentMapper = $this->container->get('CommentMapper');
        $fileMapper = $this->container->get('FileMapper');
        $file = $fileMapper->getFile($args['id']);
        $parentComment = $commentMapper->getComment($args['commentId']);
        $postVars = $request->getParsedBody();
        $jsonResponse = ["error" => false, "result" => null, "type" => "reply"];
        if($file == null || $parentComment == null) {
            $jsonResponse["error"] = 1;
        } else {
            if(!isset($postVars['comment']) || trim($postVars['comment']) == "") {
                $jsonResponse["error"] = 2;
            } else {
                $replyComment = new Comment();
                $dateTime = new \DateTime("now");
                $replyComment->setFileId($file->getId());
                $replyComment->setAuthor("Anonymous");
                $replyComment->setDatePosted($dateTime->format(\DateTime::ATOM));
                $replyComment->setParentId($parentComment->getId());
                $replyComment->setCommentText($postVars['comment']);
                $replyComment->setId($commentMapper->addComment($replyComment));
                $replyComment->setParentPath($commentHelper->normalizePath("{$parentComment->getParentPath()}.{$replyComment->getId()}"));
                $commentMapper->updatePath($replyComment);
                $jsonResponse["result"] = ["id" => $replyComment->getId(),
                    "parentId" => $replyComment->getParentId(),
                    "author" => $replyComment->getAuthor(),
                    "date" => $replyComment->getDatePosted(),
                    "text" => $replyComment->getCommentText(),
                    "path" => $replyComment->getParentPath()];
            }
        }
        print(json_encode($jsonResponse));
        return $response;
    }
}