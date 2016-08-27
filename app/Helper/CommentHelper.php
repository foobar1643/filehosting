<?php

namespace Filehosting\Helper;

use \Filehosting\Entity\Comment;
use \Filehosting\Entity\TreeNode;

class CommentHelper
{
    private $commentMapper;

    public function __construct(\Filehosting\Database\CommentMapper $mapper)
    {
        $this->commentMapper = $mapper;
    }

    public function addComment(Comment $comment)
    {
        if(is_null($comment->getParentId())) { // root comment
            $maxPath = $this->commentMapper->getRootMaxPath($comment->getFileId());
            $maxPath = intval($maxPath) + 1;
            $comment->setMatPath($this->normalizePath($maxPath));
        } else { // child comment
            $parentComment = $this->commentMapper->getComment($comment->getParentId());
            $comment->setMatPath($parentComment->getMatPath());
            $rawPath = $this->commentMapper->getChildMaxPath($comment->getParentId());
            $splitPath = $this->splitPath($rawPath);
            $maxPath = intval($splitPath[count($splitPath) - 1]);
            $maxPath = is_null($maxPath) ? 0 : intval($maxPath) + 1;
            $comment->addToPath($this->normalizePath($maxPath));
        }
        # SELECT MAX(matpath) FROM comments WHERE NOT (parent_id IS NOT NULL) AND file_id = ?; - FOR ROOT COMMENT ADDITION
        # SELECT MAX(matpath) FROM comments WHERE parent_id = ?; - FOR CHILD COMMENT ADDITION
        $comment->setId($this->commentMapper->addComment($comment));
        return $comment;
    }

    public function getComments($fileId)
    {
        $rawComments = $this->commentMapper->getComments($fileId);
        $commentTrees = $this->makeTrees($rawComments);
        $commentsCount = $this->countTotalComments($commentTrees);
        return ["count" => $commentsCount, "comments" => $commentTrees];
    }

    public function commentExists($commentId)
    {
        $comment = $this->commentMapper->getComment($commentId);
        if($comment) {
            return true;
        }
        return false;
    }

    public function makeTrees($rawComments)
    {
        $trees = [];
        $treeRoot = null;
        foreach($rawComments as $comment) {
            if($comment->getParentId() == null) { // tree root
                $treeRoot = new TreeNode($comment);
                $trees[$comment->getId()] = $treeRoot;
            } else if($comment->getParentId() == $treeRoot->getObject()->getId()) {
                $treeRoot->addChildNode(new TreeNode($comment));
            } else {
                $parentNode = $treeRoot->findChildByObjectId($comment->getParentId());
                $parentNode->addChildNode(new TreeNode($comment));
            }
        }
        return $trees;
    }

    private function splitPath($path)
    {
        return preg_split("/[.]/", $path);
    }

    private function normalizePath($path)
    {
        $split = $this->splitPath($path);
        $newStr = "";
        foreach($split as $elem) {
            $newStr .= ($newStr != "" ? "." : "") . str_pad($elem, 3, "0", STR_PAD_LEFT);
        }
        return $newStr;
    }

    private function countTotalComments(array $comments)
    {
        $size = 0;
        foreach($comments as $id => $treeNode) {
            $size += $treeNode->countDescendants() + 1;
        }
        return $size;
    }
}