<?php

 namespace Filehosting\Entity;

 class Comment implements \JsonSerializable, TreeNodeSearchable
 {
     private $id;
     private $file_id;
     private $parent_id;
     private $author;
     private $date_posted;
     private $comment_text;
     private $matpath;

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
         return $this->parent_id;
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

     public function getDatabaseDate()
     {
         $dateTime = new \DateTime();
         $dateTime->setTimestamp(strtotime($this->date_posted));
         return $dateTime->format(\DateTime::ATOM);
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

     public function getMatPath() // getParentPath
     {
         return $this->matpath;
     }

     public function addToPath($addition) // substr_replace($this->matPath, "{$addition}.", 0, 0)  - prepend to path
     {
         $this->matpath .= !is_null($this->matpath) ? ".{$addition}" : $addition;
         return $this->matpath;
     }

     public function setMatPath($matPath) // setParentPath
     {
         $this->matpath = $matPath;
         return $this;
     }

     public function getDepth()
     {
         $path = preg_split("/[.]/", $this->matpath);
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
             'path' => $this->matpath
         ];
     }
 }