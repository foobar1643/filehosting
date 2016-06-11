<?php

 namespace Filehosting\Entity;

 class Comment implements \JsonSerializable
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
         return $this;
     }

     public function getParentId()
     {
         return intval($this->parent_id);
     }

     public function setParentId($parentId)
     {
         $this->parent_id = $parentId;
         return $this;
     }

     public function getFileId()
     {
         return $this->file_id;
     }

     public function setFileId($fileId)
     {
         $this->file_id = $fileId;
         return $this;
     }

     public function getAuthor()
     {
         return $this->author;
     }

     public function setAuthor($author)
     {
         $this->author = $author;
         return $this;
     }

     public function getDatePosted()
     {
         $dateTime = new \DateTime();
         $dateTime->setTimestamp(strtotime($this->date_posted));
         $dateFormater = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::MEDIUM);
         return $dateFormater->format($dateTime);
     }

     public function setDatePosted($datePosted)
     {
         $this->date_posted = $datePosted;
         return $this;
     }

     public function getCommentText()
     {
         return $this->comment_text;
     }

     public function setCommentText($commentText)
     {
         $this->comment_text = $commentText;
         return $this;
     }

     public function getParentPath()
     {
         return $this->parent_path;
     }

     public function setParentPath($parentPath)
     {
         $this->parent_path = $parentPath;
         return $this;
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

     public function countDescendants()
     {
         $size = 0;
         foreach($this->childNodes as $id => $comment) {
             $size++;
             if(count($comment->getChildren()) > 0) {
                 $size = $size + $comment->countDescendants();
             }
         }
         return $size;
     }

     public function getDepth()
     {
         $path = preg_split("/[.]/", $this->parent_path);
         return count($path);
     }

     public function jsonSerialize()
     {
         return [
             'id' => $this->id,
             'parentId' => $this->parent_id,
             'author' => $this->author,
             'date' => $this->getDatePosted(),
             'text' => $this->comment_text,
             'path' => $this->parent_path
         ];
     }
 }