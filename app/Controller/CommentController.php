<?php

namespace Filehosting\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Entity\Comment;
use Slim\Container;

/**
 * Callable, provides a way to add comments to the database.
 *
 * @package Filehosting\Controller
 * @author foobar1643 <foobar76239@gmail.com>
 */
class CommentController
{
    /**
     * @var \Filehosting\Validation\Validation Validation object instance.
     */
    private $validator;

    /**
     * @var \Filehosting\Helper\CommentHelper CommentHelper instance.
     */
    private $commentHelper;

    /**
     * Constructor.
     *
     * @todo Remove unused container class field.
     *
     * @param \Slim\Container $c DI container.
     */
    public function __construct(Container $c)
    {
        $this->validator = $c->get('Validation');
        $this->commentHelper = $c->get('CommentHelper');
    }

    /**
     * A method that allows to use this class as a callable.
     *
     * @param Request $request Slim Framework request instance.
     * @param Response $response Slim Framework response instance.
     * @param array $args Array with additional arguments.
     *
     * @return array
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $postVars = $request->getParsedBody();
        $comment = $this->parsePostRequest($postVars, $args['id']);
        $errors = $this->validator->validateComment($comment);
        if (!$errors) {
            $comment = $this->commentHelper->addComment($comment);
        }
        return ["errors" => $errors, "comment" => $comment];
    }

    /**
     * Parses POST request and returns a Comment entity.
     *
     * @todo Make this method private.
     *
     * @param array $postVars Array with variables from POST request.
     * @param int $fileId ID of the file in the database.
     *
     * @return Comment
     */
    public function parsePostRequest(array $postVars, $fileId)
    {
        $comment = new Comment();
        $dateTime = new \DateTime("now");
        return $comment->setFileId($fileId)
            ->setAuthor("Anonymous")
            ->setDatePosted($dateTime->format(\DateTime::ATOM))
            ->setCommentText(isset($postVars['comment']) ? strval($postVars['comment']) : '')
            ->setParentId((isset($postVars['parentComment']) ? $postVars['parentComment'] : null));
    }
}
