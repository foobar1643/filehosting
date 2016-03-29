<?php

 namespace Filehosting\Model;

 class Comment
 {
     private $id;
     private $file_id;
     private $author;
     private $date_posted;
     private $comment_text;
     private $parent_path;

     public function getId()
     {
         return $this->id;
     }

     public function setId($id)
     {
         $this->id = $id;
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
 }