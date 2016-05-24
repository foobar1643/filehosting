<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\Comment;
use \Filehosting\Helper\CommentHelper;

class CommentController
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $validator = $this->container->get('Validation');
        $commentHelper = $this->container->get('CommentHelper');
        $getVars = $request->getQueryParams();
        $postVars = $request->getParsedBody();
        $errors = $validator->validateCommentForm($args['id'], $postVars);
        if(!$errors) { // there is no errors
            $dateTime = new \DateTime("now");
            $comment = new Comment();
            $comment->setFileId($args['id'])
                ->setAuthor("Anonymous")
                ->setDatePosted($dateTime->format(\DateTime::ATOM))
                ->setCommentText($postVars['comment'])
                ->setParentId((isset($postVars['parentComment']) ? $postVars['parentComment'] : NULL));
            $comment = $commentHelper->addComment($comment);
            if(isset($getVars['ajax']) && $getVars['ajax'] == "true") {
                $jsonResponse = ["errors" => false, "comment" => $comment->jsonSerialize()];
                print(json_encode($jsonResponse));
                return $response->withHeader('Content-Type', "application/json");
            } else {
                return $response->withHeader('Location', "/file/" . $args['id']);
            }
        } else {
            if(isset($getVars['ajax']) && $getVars['ajax'] == "true") {
                $jsonResponse = [
                    "errors" => $errors,
                    "parentId" => isset($postVars['parentComment']) ? $postVars['parentComment'] : NULL,
                    "comment" => isset($postVars['comment']) ? $postVars['comment'] : NULL
                ];
                print(json_encode($jsonResponse));
                return $response->withHeader('Content-Type', "application/json");
            } else {
                $fileController = new FileController($this->container);
                $args['commentErrors'] = $errors;
                $args['replyTo'] = isset($postVars['parentComment']) ? $postVars['parentComment'] : NULL;
                return $fileController->viewFile($request, $response, $args);
            }
        }
    }
}