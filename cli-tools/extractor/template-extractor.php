#!/usr/bin/php
<?php

require(__DIR__ . "/../../vendor/autoload.php");

use Filehosting\Helper\Utils;
use Filehosting\Helper\LinkHelper;

$tplDir = __DIR__ . '/../../templates';
$tmpDir =__DIR__ . '/../../translation-cache/';
$loader = new Twig_Loader_Filesystem($tplDir);

if(!is_dir($tmpDir)) {
    mkdir($tmpDir);
}

// force auto-reload to always have the latest version of the template
$twig = new Twig_Environment($loader, array(
    'cache' => $tmpDir,
    'auto_reload' => true
));
$formatSize = new Twig_SimpleFunction('formatSize', function ($size) {
    return Utils::formatSize($size);
});
$twig->addFunction('formatSize', $formatSize);
$twig->addGlobal('linkHelper', new LinkHelper(\Locale::getDefault()));
$twig->addExtension(new Twig_Extensions_Extension_I18n());
$twig->addExtension(new Twig_Extensions_Extension_Intl());
// configure Twig the way you want

// iterate over all your templates
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tplDir), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
    // force compilation
    if ($file->isFile()) {
        $twig->loadTemplate(str_replace($tplDir.'/', '', $file));
    }
}

print('Template cache successfully generated.' . PHP_EOL);