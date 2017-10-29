<?php

namespace Filehosting\Helper;

use Filehosting\Entity\Comment;
use Filehosting\Entity\TreeNode;
use Filehosting\Database\CommentMapper;

/**
 * Adds, deletes and selects comments from the database.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class CommentHelper
{
    /**
     * @var \Filehosting\Database\CommentMapper CommentMapper instance.
     */
    private $commentMapper;

    /**
     * Constructor.
     *
     * @param \Filehosting\Database\CommentMapper $mapper A CommentMapper instance.
     */
    public function __construct(CommentMapper $mapper)
    {
        $this->commentMapper = $mapper;
    }

    /**
     * Adds given comment to the database.
     *
     * @todo Add database locks when adding comments.
     *
     * @param \Filehosting\Entity\Comment $comment A comment entity.
     *
     * @return \Filehosting\Entity\Comment
     */
    public function addComment(Comment $comment): Comment
    {
        if (is_null($comment->getParentId())) { // root comment
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
        # SELECT MAX(matpath) FROM comments WHERE NOT (parent_id IS NOT NULL) AND file_id = ?; FOR ROOT COMMENT ADDITION
        # SELECT MAX(matpath) FROM comments WHERE parent_id = ?; - FOR CHILD COMMENT ADDITION
        $comment->setId($this->commentMapper->addComment($comment));
        return $comment;
    }

    /**
     * Gets comments for a given file ID.
     *
     * @param int $fileId File ID in the database.
     *
     * @return array
     */
    public function getComments(int $fileId): array
    {
        $rawComments = $this->commentMapper->getComments($fileId);
        $commentTrees = $this->makeTrees($rawComments);
        $commentsCount = $this->countTotalComments($commentTrees);
        return ["count" => $commentsCount, "comments" => $commentTrees];
    }

    /**
     * Checks if comment with a given ID exists.
     *
     * @param int $commentId Comment ID in the database.
     *
     * @return bool
     */
    public function commentExists(int $commentId): bool
    {
        $comment = $this->commentMapper->getComment($commentId);

        return (bool)$comment;
    }

    /**
     * Makes a Tree data structure out of array of comments.
     *
     * @param array $rawComments Array of comments.
     *
     * @return array
     */
    public function makeTrees(array $rawComments): array
    {
        $trees = [];
        $treeRoot = null;
        foreach ($rawComments as $comment) {
            if ($comment->getParentId() == null) { // tree root
                $treeRoot = new TreeNode($comment);
                $trees[$comment->getId()] = $treeRoot;
            } elseif ($comment->getParentId() == $treeRoot->getObject()->getId()) {
                $treeRoot->addChildNode(new TreeNode($comment));
            } else {
                $parentNode = $treeRoot->findChildByObjectId($comment->getParentId());
                $parentNode->addChildNode(new TreeNode($comment));
            }
        }
        return $trees;
    }

    /**
     * Splits a given matpath to an array.
     *
     * @param string $path Matpath of the comment.
     *
     * @return array
     */
    private function splitPath(string $path): array
    {
        return preg_split("/[.]/", $path);
    }

    /**
     * Normalizes given matpath, adding leading zeroes to numbers.
     *
     * @todo Refactor this code.
     *
     * @param string $path Matpath of the comment.
     *
     * @return string
     */
    private function normalizePath(string $path): string
    {
        $split = $this->splitPath($path);
        $newStr = "";
        foreach ($split as $elem) {
            $newStr .= ($newStr != "" ? "." : "") . str_pad($elem, 3, "0", STR_PAD_LEFT);
        }
        return $newStr;
    }

    /**
     * Counts total number of comments that is present in a tree sturcture.
     *
     * @param array $comments Array of Tree stuctured comments.
     *
     * @return int
     */
    private function countTotalComments(array $comments): int
    {
        $size = 0;
        foreach ($comments as $id => $treeNode) {
            $size += $treeNode->countDescendants() + 1;
        }
        return $size;
    }
}
