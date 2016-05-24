<?php

namespace Filehosting\Entity;

class File
{
    const WHITELISTED_EXTENSIONS = ["jpg", "jpeg", "png", "gif", "webm", "mp3", "mp4"];

    private $id;
    private $name;
    private $originalName;
    private $uploader;
    private $upload_date;
    private $downloads;
    private $auth_token;
    private $isDeleted;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getOriginalName()
    {
        return $this->originalName;
    }

    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getUploader()
    {
        return $this->uploader;
    }

    public function setUploader($uploader)
    {
        $this->uploader = $uploader;
        return $this;
    }

    public function getUploadDate()
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp(strtotime($this->upload_date));
        return $dateTime->format("m/d/Y, g:i A");
    }

    public function setUploadDate($uploadDate)
    {
        $this->upload_date = $uploadDate;
        return $this;
    }

    public function getDownloads()
    {
        return $this->downloads;
    }

    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
        return $this;
    }

    public function getAuthToken()
    {
        return $this->auth_token;
    }

    public function setAuthToken($authToken)
    {
        $this->auth_token = $authToken;
        return $this;
    }

    public function getDeleted()
    {
        return $this->isDeleted;
    }

    public function setDeleted($deleted)
    {
        $this->isDeleted = $deleted;
        return $this;
    }

    public function getExtention()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function getStrippedName()
    {
        return pathinfo($this->name, PATHINFO_FILENAME);
    }

    public function getFolder()
    {
        $div = floor($this->id / 100);
    	if($div) return $div * 100;
    	return 100;
    }

    public function getDownloadLink()
    {
        $encodedName = urlencode($this->name);
        return "/file/get/{$this->id}/{$encodedName}";
    }

    public function getLinkToPreview()
    {
        return "/thumbnails/thumb_{$this->getDiskName()}";
    }

    public function getDiskName()
    {
        $fileExtension = $this->getExtention();
        $normalized = $this->name;
        if(strlen($normalized) > 20) {
            $normalized = $this->getStrippedName();
            $normalized = substr($normalized, 0, 20);
            $normalized .= "." . $fileExtension;
        }
        if(!in_array($fileExtension, self::WHITELISTED_EXTENSIONS)) {
            $normalized .= ".txt";
        }
        $normalized = substr_replace($normalized, "{$this->id}_", 0, 0);
        return $normalized;
    }
}
