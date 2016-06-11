<?php

namespace Filehosting\Helper;

class UploadHelper
{
    public static function parseCode($code)
    {
        switch($code) {
            case UPLOAD_ERR_INI_SIZE:
                return _("File size is exceeding the maximum.");
                break;
            case UPLOAD_ERR_FORM_SIZE:
                return _("File size is exceeding the maximum.");
                break;
            case UPLOAD_ERR_PARTIAL:
                return _("Error while uploading file.");
                break;
            case UPLOAD_ERR_NO_FILE:
                return _("Attach a file and try again.");
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return _("Can't access temporary folder. Contact the server administrator or try again.");
                break;
            case UPLOAD_ERR_CANT_WRITE:
                return _("An error occurred while writing the file. Contact the server administrator or try again.");
                break;
            default:
                return _("Unknown error with code: {$code}.");
                break;
        }
    }
}