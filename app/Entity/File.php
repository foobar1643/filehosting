<?php

namespace Filehosting\Entity;
use Slim\Http\UploadedFile;

class File extends UploadedFile
{
    const WHITELISTED_EXTENSIONS = ["jpg", "jpeg", "png", "gif", "webm", "mp3", "mp4"];

    protected $id;
    /* $name - extends from parent */
    /* $type - extends from parent */
    /* $size - extends from parent */
    /* $error - extends from parent */
    protected $uploader;
    protected $upload_date;
    protected $downloads;
    protected $auth_token;
    protected $isDeleted;

    public function __construct() { }

    public function fromUploadedFile(UploadedFile $uploaded)
    {
        $this->file = $uploaded->file;
        $this->name = $uploaded->getClientFilename();
        $this->type = $uploaded->getClientMediaType();
        $this->size = $uploaded->getSize();
        $this->error = $uploaded->getError();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
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

    public function getFormattedSize()
    {
        // 1000000000 - GB, 1000000 - MB, 1000 - KB
        if(($this->size / 1000000) > 1000000) {
            return round($this->size / 1000000000, 1, PHP_ROUND_HALF_DOWN) . " GB";
        }
       if(($this->size / 1000) > 1000) {
           return round($this->size / 1000000, 1, PHP_ROUND_HALF_DOWN) . " MB";
       }
       return round($this->size / 1000, 0, PHP_ROUND_HALF_UP) . " KB";
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

    public function getThumbnailName()
    {
        return "thumb_{$this->getDiskName()}";
    }

    public function getLinkToPreview()
    {
        return "/thumbnails/{$this->getThumbnailName()}";
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