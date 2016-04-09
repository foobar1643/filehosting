<?php

namespace Filehosting\Exception;

use \Filehosting\Helper\UploadHelper;

class FileUploadException extends \Exception
{
    public function __construct($code)
    {
        parent::__construct(UploadHelper::parseCode($code), $code);
    }
}