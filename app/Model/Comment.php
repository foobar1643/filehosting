<?php

 namespace Filehosting\Model;

 class Comment
 {
     private $id;
     private $file_id;
     private $parent_id;
     private $author;
     private $date_posted;
     private $comment_text;
     private $parent_path;

     private $childNodes = [];

     public function getId()
     {
         return $this->id;
     }

     public function setId($id)
     {
         $this->id = $id;
     }

     public function getParentId()
     {
         return intval($this->parent_id);
     }

     public function setParentId($parentId)
     {
         $this->parent_id = $parentId;
     }

     public function getFileId()
     {
         return $this->file_id;
     }

     public function setFileId($fileId)
     {
         $this->file_id = $fileId;
     }

     public function getAuthor()
     {
         return $this->author;
     }

     public function setAuthor($author)
     {
         $this->author = $author;
     }

     public function getDatePosted()
     {
         $dateTime = new \DateTime();
         $dateTime->setTimestamp(strtotime($this->date_posted));
         return $dateTime->format("m/d/Y, g:i A");
     }

     public function setDatePosted($datePosted)
     {
         $this->date_posted = $datePosted;
     }

     public function getCommentText()
     {
         return $this->comment_text;
     }

     public function setCommentText($commentText)
     {
         $this->comment_text = $commentText;
     }

     public function getParentPath()
     {
         return $this->parent_path;
     }

     public function setParentPath($parentPath)
     {
         $this->parent_path = $parentPath;
     }

     public function addChildNode(Comment $child)
     {
         $this->childNodes[$child->id] = $child;
         return $child;
     }

     public function getChildren()
     {
         return $this->childNodes;
     }

     public function countChildNodes()
     {
         return count($this->childNodes);
     }
 }