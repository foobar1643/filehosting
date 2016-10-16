<?php

namespace Filehosting\Installer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class FilehostingInstaller extends Installer
{
    public static function installDependencies(Event $event)
    {
        self::installJquery();
        self::installBootstrap();
        self::installVideoJs();
    }

    public static function installBootstrap()
    {
        self::createDirectory(__DIR__ . '/../../public/media/bootstrap/css');
        self::createDirectory(__DIR__ . '/../../public/media/bootstrap/js');
        self::createDirectory(__DIR__ . '/../../public/media/bootstrap/fonts');

        self::copyFile(__DIR__ . '/../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
            __DIR__ . '/../../public/media/bootstrap/css/bootstrap.min.css');
        self::copyFile(__DIR__ . '/../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css.map',
            __DIR__ . '/../../public/media/bootstrap/css/bootstrap.min.css.map');

        self::copyFile(__DIR__ . '/../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js',
            __DIR__ . '/../../public/media/bootstrap/js/bootstrap.min.js');

        self::copyDirectory(__DIR__ . '/../../vendor/twbs/bootstrap/dist/fonts/',
            __DIR__ . '/../../public/media/bootstrap/fonts/');
    }

    public static function installVideoJs()
    {
        self::createDirectory(__DIR__ . '/../../public/media/css/videojs/font');
        self::createDirectory(__DIR__ . '/../../public/media/flash/videojs');
        self::createDirectory(__DIR__ . '/../../public/media/js/videojs');

        self::copyFile(__DIR__ . '/../../vendor/videojs/video.js/dist/video-js.min.css',
            __DIR__ . '/../../public/media/css/videojs/video-js.min.css');

        self::copyFile(__DIR__ . '/../../vendor/videojs/video.js/dist/video-js.swf',
            __DIR__ . '/../../public/media/flash/videojs/video-js.swf');

        self::copyDirectory(__DIR__ . '/../../vendor/videojs/video.js/dist/font/',
            __DIR__ . '/../../public/media/css/videojs/font/');

        self::copyFile(__DIR__ . '/../../vendor/videojs/video.js/dist/video.min.js',
            __DIR__ . '/../../public/media/js/videojs/video.min.js');
    }

    public static function installJquery()
    {
        self::createDirectory(__DIR__ . '/../../public/media/js/jquery');
        self::copyFile(__DIR__ . '/../../vendor/components/jquery/jquery.min.js',
            __DIR__ . '/../../public/media/js/jquery/jquery.min.js');
    }
}