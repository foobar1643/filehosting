<?php

namespace Filehosting\Installer;

abstract class Installer
{
    /**
     * Creates a directory with a given name.
     *
     * @param string $dir Path to directory to create.
     *
     * @throws \RuntimeException If directory creation has failed.
     */
    public static function createDirectory($dir)
    {
        if(file_exists($dir) === false && mkdir($dir, 0777, true) === false) {
            throw new \RuntimeException('Failed to create a directory.');
        }
    }

    /**
     * Copyies file with a given name to a given destanation.
     *
     * @param string $file File to copy.
     * @param string $dest Destanation to copy to.
     *
     * @throws \RuntimeException If an error occured while copying file.
     */
    public static function copyFile($file, $dest)
    {
        if(file_exists($dest) === false && copy($file, $dest) === false) {
            throw new \RuntimeException('Failed to copy file.');
        }
    }

    /**
     * Copyies a directory (all files included) with a given name to a given destanation.
     *
     * @param string $directory Directory to copy.
     * @param string $dest Destanation to copy to.
     *
     * @throws \RuntimeException If an error occured while copying a directory.
     */
    public static function copyDirectory($directory, $dest)
    {
        $handle = opendir($directory);
        if($handle === false) {
            throw new \RuntimeException('Can\'t open directory');
        }
        while($entry = readdir($handle)) {
            $copyFrom = $directory . $entry;
            $copy = $entry == '.' || $entry == '..' ? true : copy($copyFrom, $dest .  $entry);
            if($copy === false) {
                throw new \RuntimeException('Failed to copy file.');
            }
        }
    }
}