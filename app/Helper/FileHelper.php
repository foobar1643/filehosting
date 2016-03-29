<?php

namespace Filehosting\Helper;

use \Filehosting\Model\File;

class FileHelper
{
    const FORBIDDEN_EXTENSIONS = ["php", "phtml", "js", "html", "htm"];

    public function fileExists(File $file)
    {
        if($file != null) {
            $fileFolder = $file->getFolder();
            $diskName = $this->getDiskName($file);
            if(file_exists("storage/{$fileFolder}/{$diskName}")) {
                return true;
            }
        }
        return false;
    }

    public function canDelete(File $file)
    {
        if(!isset($_COOKIE['auth']) && $_COOKIE['auth'] == $file->getAuthToken()) {
            return true;
        }
        return false;
    }

    public function unlinkFile(File $file)
    {
        if(!unlink("storage/{$file->getFolder()}/{$this->getDiskName($file)}")) {
            throw new \Exception('Произошла системная ошибка. Попробуйте позже или обратитесь к администратору.');
        }
    }

    public function isFolderAvailable(File $file)
    {
        if(!is_dir("storage/{$file->getFolder()}")) {
            if(!mkdir("storage/{$file->getFolder()}")) {
                throw new FileUploadException(UPLOAD_ERR_CANT_WRITE);
            }
        }
        return true;
    }

    public function getDiskName(File $file)
    {
        $fileExtension = $file->getExtention();
        $normalized = $file->getName();
        if(strlen($normalized) > 20) {
            $normalized = $file->getStrippedName();
            $normalized = substr($normalized, 0, 20);
            $normalized .= "." . $fileExtension;
        }
        if(in_array($fileExtension, self::FORBIDDEN_EXTENSIONS)) {
            $normalized .= ".txt";
        }
        $normalized = substr_replace($normalized, "{$file->getId()}_", 0, 0);
        return $normalized;
    }
}