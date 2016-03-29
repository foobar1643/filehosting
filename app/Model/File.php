<?php

namespace Filehosting\Model;

class File
{
    private $id;
    private $name;
    private $uploader;
    private $upload_date;
    private $downloads;
    private $auth_token;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getUploader()
    {
        return $this->uploader;
    }

    public function setUploader($uploader)
    {
        $this->uploader = $uploader;
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
    }

    public function getDownloads()
    {
        return $this->downloads;
    }

    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
    }

    public function getAuthToken()
    {
        return $this->auth_token;
    }

    public function setAuthToken($authToken)
    {
        $this->auth_token = $authToken;
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
        for($i = 100; $i < 100000000; $i += 100) {
            if(($this->id / $i) <= 1) return $i;
        }
        return false;
    }
}
