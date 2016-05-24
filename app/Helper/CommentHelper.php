<?php

namespace Filehosting\Helper;

use \Filehosting\Entity\Comment;

class CommentHelper
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function addComment(Comment $comment)
    {
        $commentMapper = $this->container->get('CommentMapper');
        $comment->setId($commentMapper->addComment($comment)); // adds comment to the database
        if($comment->getParentId() != NULL) { // if there is a parent comment
            $parentComment = $commentMapper->getComment($comment->getParentId());
            $comment->setParentPath($this->normalizePath("{$parentComment->getParentPath()}.{$comment->getId()}"));
        } else { // if there is no parent comment
            $comment->setParentPath($this->normalizePath($comment->getId()));
        }
        $commentMapper->updatePath($comment); // updates materialized path in the database
        return $comment;
    }

    public function getComments($fileId)
    {
        $commentMapper = $this->container->get('CommentMapper');
        $rawComments = $commentMapper->getComments($fileId);
        $comments = $this->makeTrees($rawComments);
        $commentsCount = $this->countTotalComments($comments);
        return ["count" => $commentsCount, "comments" => $comments];
    }

    public function getMargin($path)
    {
        $split = preg_split("/[.]/", $path);
        return (count($split) - 1) * 25;
    }

    public function splitPath($path)
    {
        return preg_split("/[.]/", $path);
    }

    public function normalizePath($path)
    {
        $split = preg_split("/[.]/", $path);
        $newStr = "";
        foreach($split as $elem) {
            $newStr .= ($newStr != "" ? "." : "") . str_pad($elem, 3, "0", STR_PAD_LEFT);
        }
        return $newStr;
    }

    public function parseComments($comments)
    {
        $result = [];
        foreach($comments as $comment) {
            $result[$comment->getId()] = $comment;
        }
        return $result;
    }

    public function getParent($path, Comment $treeRoot)
    {
        $split = $this->splitPath($path);
        if(count($split) <= 2) {
            return $treeRoot;
        }
        $childNodes = $treeRoot->getChildren();
        $arr = [$childNodes[intval($split[1])]];
        for($i = 1; $i < count($split) - 2; $i++) {
            $children = $arr[$i-1]->getChildren();
            $arr[$i] = $children[intval($split[$i + 1])];
        }
        return $arr[count($arr) - 1];
    }

    public function makeTrees($comments)
    {
        $trees = [];
        foreach($comments as $comment) {
            $split = $this->splitPath($comment->getParentPath());
            if(count($split) == 1) { // tree root
                $trees[intval($split[0])] = $comment;
            } else {
                $parentNode = $this->getParent($comment->getParentPath(), $trees[intval($split[0])]);
                $parentNode->addChildNode($comment);
            }
        }
        return $trees;
    }

    public function countTotalComments($comments)
    {
        $size = 0;
        foreach($comments as $id => $comment) {
            $size += $comment->countDescendants() + 1;
        }
        return $size;
    }
}