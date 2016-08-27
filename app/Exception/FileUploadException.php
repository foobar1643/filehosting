<?php

namespace Filehosting\Exception;

use \Filehosting\Helper\Utils;

class FileUploadException extends \Exception
{
    public function __construct($code)
    {
        parent::__construct(Utils::parseFileFormErrorCode($code), $code);
    }
}