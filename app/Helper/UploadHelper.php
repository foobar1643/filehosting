<?php

namespace Filehosting\Helper;

class UploadHelper
{
    private $maxFileSize;

    public function __construct($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;
    }

    public function validateUpload($upload) {
        $errors = null;
        if(!isset($upload['filename'])) {
            $errors['noFile'] = "Прикрепите файл и попробуйте еще раз.";
        }
        if(isset($upload['filename']) && $upload['filename']['size'] > $this->maxFileSize * 1000000) {
            $errors['sizeLimit'] = "Размер файла превышает допустимый лимит.";
        }
        if(isset($upload['filename']) && $upload['filename']['error'] != UPLOAD_ERR_OK) {
            $errors['form'] = $this::parseCode($upload['filename']['error']);
        }
        return $errors;
    }

    public static function parseCode($code)
    {
        switch($code) {
            case UPLOAD_ERR_INI_SIZE:
                return "Размер файла превышает максимально допустимый.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                return "Размер файла превышает максимально допустимый.";
                break;
            case UPLOAD_ERR_PARTIAL:
                return "Ошибка при загрузке файла.";
                break;
            case UPLOAD_ERR_NO_FILE:
                return "Заполните все поля и попробуйте еще раз.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return "На сервере отсутствует папка для временных файлов. Обратитесь к администратору или попробуйте еще раз.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                return "Ошибка при записи файла. Обратитесь к администратору или попробуйте еще раз.";
                break;
            default:
                return "Неизвестная ошибка с кодом: {$code}.";
                break;
        }
    }
}