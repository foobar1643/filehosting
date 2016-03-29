<?php

namespace Filehosting\Exception;

class FileUploadException extends \Exception
{

    public function __construct($code)
    {
        parent::__construct($this->parseCode($code), $code);
    }

    private function parseCode($code)
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