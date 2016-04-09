<?php

namespace Filehosting\Helper;

use \Filehosting\TreeNode;
use \Filehosting\Model\Comment;

class CommentHelper
{
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

    function parseComments($comments)
    {
        $result = [];
        foreach($comments as $comment) {
            $result[$comment->getId()] = $comment;
        }
        return $result;
    }

    function getParent($path, Comment $treeRoot)
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

    function makeTrees($comments)
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
}