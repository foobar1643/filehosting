<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\Comment;
use \Filehosting\Helper\CommentHelper;

class CommentController
{
    private $validator;
    private $container;
    private $commentHelper;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
        $this->validator = $c->get('Validation');
        $this->commentHelper = $c->get('CommentHelper');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $getVars = $request->getQueryParams();
        $postVars = $request->getParsedBody();
        $comment = $this->parsePostRequest($postVars, $args['id']);
        $errors = $this->validator->validateComment($comment);
        if(!$errors) {
            $comment = $this->commentHelper->addComment($comment);
        }
        return ["errors" => $errors, "comment" => $comment];
    }

    public function parsePostRequest($postVars, $fileId)
    {
        $comment = new Comment();
        $dateTime = new \DateTime("now");
        $comment->setFileId($fileId)
            ->setAuthor("Anonymous")
            ->setDatePosted($dateTime->format(\DateTime::ATOM))
            ->setCommentText(isset($postVars['comment']) ? strval($postVars['comment']) : '')
            ->setParentId((isset($postVars['parentComment']) ? $postVars['parentComment'] : NULL));
        return $comment;
    }
}