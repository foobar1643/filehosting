<?php

namespace Filehosting\Entity;
use Slim\Http\UploadedFile;

class File
{
    const WHITELISTED_EXTENSIONS = ["jpg", "jpeg", "png", "gif", "webm", "mp3", "mp4"];

    protected $id;
    protected $uploadedFile;
    protected $name;
    protected $size;
    protected $uploader;
    protected $upload_date;
    protected $downloads;
    protected $auth_token;
    protected $isDeleted;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(UploadedFile $uploaded)
    {
        $this->uploadedFile = $uploaded;
        $this->name = $uploaded->getClientFilename();
        $this->size = $uploaded->getSize();
        return $this;
    }

    public function getClientFilename()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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

    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getDatabaseDate()
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp(strtotime($this->upload_date));
        return $dateTime->format(\DateTime::ATOM);
    }

    public function getUploadDate()
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp(strtotime($this->upload_date));
        $dateFormater = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::MEDIUM);
        return $dateFormater->format($dateTime);
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

    public function getExtension()
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

    public function getThumbnailName()
    {
        return "thumb_{$this->getDiskName()}";
    }

    public function getDiskName()
    {
        $fileExtension = $this->getExtension();
        $normalized = $this->name;
        if(strlen($normalized) > 20) {
            $normalized = $this->getStrippedName();
            $normalized = substr($normalized, 0, 20);
            $normalized .= "." . $fileExtension;
        }
        if(!in_array(strtolower($fileExtension), self::WHITELISTED_EXTENSIONS)) {
            $normalized .= ".txt";
        }
        $normalized = substr_replace($normalized, "{$this->id}_", 0, 0);
        return $normalized;
    }

}