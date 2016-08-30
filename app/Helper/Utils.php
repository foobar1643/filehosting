<?php

namespace Filehosting\Helper;

/**
 * General utility class.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class Utils
{
    /**
     * Generates a random string with a given length. This is not cryptographically secure.
     *
     * @param int $length A length of a random string.
     *
     * @return string
     */
    public static function generateToken($length)
    {
        $result = null;
        $source = str_split('abcdefghijklmnopqrstuvwxyz'
          .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
          .'0123456789');
        for($i = 0; $i < $length; $i++) {
            $result .= $source[mt_rand(0, count($source) - 1)];
        }
        return $result;
    }

    /**
     * Formats a given size to readable string.
     *
     * @param int $size A size number to format.
     *
     * @return string
     */
    public static function formatSize($size)
    {
        switch(true) { // 1073741824 B - 1 GB, 1048576 B - 1 MB, 1024 B - 1 KB
            case ($size >= pow(1024, 3)):
                //notes: GB (Gigabyte) - a unit of digital information, displays near formatted file size
                return round($size / pow(1024, 3), 3, PHP_ROUND_HALF_DOWN) . str_pad(_("GB"), 3, ' ', STR_PAD_LEFT);
            case ($size >= pow(1024, 2)):
                //notes: MB (Megabyte) - a unit of digital information, displays near formatted file size
                return round($size / pow(1024, 2), 1, PHP_ROUND_HALF_DOWN) . str_pad(_("MB"), 3, ' ', STR_PAD_LEFT);
            case ($size >= 1024):
                //notes: KB (Kilobyte) - a unit of digital information, displays near formatted file size
                return round($size / 1024, 0, PHP_ROUND_HALF_DOWN) . str_pad(_("KB"), 3, ' ', STR_PAD_LEFT);
            default:
                //notes: B (Byte) - a unit of digital information, displays near formatted file size
                return $size . str_pad(_("B"), 2, ' ', STR_PAD_LEFT);
        }
    }

    /**
     * Parses a given file upload error code and returns a readable string.
     *
     * @param int $code A upload error code.
     *
     * @return string
     */
    public static function parseFileFormErrorCode($code)
    {
        switch($code) {
            case UPLOAD_ERR_INI_SIZE:
                return _("File size is exceeding the maximum.");
            case UPLOAD_ERR_FORM_SIZE:
                return _("File size is exceeding the maximum.");
            case UPLOAD_ERR_PARTIAL:
                return _("Error while uploading file.");
            case UPLOAD_ERR_NO_FILE:
                return _("Attach a file and try again.");
            case UPLOAD_ERR_NO_TMP_DIR:
                return _("Can't access temporary folder. Contact the server administrator or try again.");
            case UPLOAD_ERR_CANT_WRITE:
                return _("An error occurred while writing the file. Contact the server administrator or try again.");
            default:
                return _("Unknown error with code: {$code}.");
        }
    }
}