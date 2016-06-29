#!/usr/bin/php
<?php

require(__DIR__ . "/../../vendor/autoload.php");

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
$twig->addExtension(new Twig_Extensions_Extension_I18n());
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