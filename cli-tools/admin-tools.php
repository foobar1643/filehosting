<?php

require("../app/init.php");

use \Filehosting\Helper\TokenGenerator;

class CLItools
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function runWithOptions($options)
    {
        switch(key($options)) {
            case "a":
                $this->addFile($options['a']);
                break;
            case "d":
                $this->deleteFile($options['d']);
                break;
            case "l":
                if(!isset($options['o'])) {
                    die($this->outputHelpMessage());
                }
                $this->listFiles($options['l'], $options['o']);
                break;
            default:
                die($this->outputHelpMessage());
                break;
        }
    }

    public function outputHelpMessage()
    {
        $argv = $_SERVER['argv'];
        print("This is a command line PHP script for administrators. The script can accept following options:" . PHP_EOL . PHP_EOL
        . "Option -a. Usage: {$argv[0]} -a <option>" . PHP_EOL
        . "<option> must be a valid path to file you want to add to the app." . PHP_EOL . PHP_EOL
        . "Option -d. Usage: {$argv[0]} -d <option>" . PHP_EOL
        . "<option> must be a valid id of the file you want to delete from the app." . PHP_EOL . PHP_EOL
        . "Option -l can only be used with -o option. Usage: {$argv[0]} -l <limit> -o <offset>" . PHP_EOL
        . "<limit> must be a valid numeric value that will be used as a limit in file selection." . PHP_EOL
        . "<offset> must be a valid numeric value that will be used as an offset in file selection." . PHP_EOL . PHP_EOL
        . "Additionally, with the -h option you can get this help." . PHP_EOL);
    }

    private function addFile($filepath)
    {
        if(!file_exists($filepath)) {
            die("File $filepath does not exists." . PHP_EOL);
        }
        $fileHelper = $this->container->get('FileHelper');
        $file = new \Filehosting\Entity\File();
        $file->setName(pathinfo($filepath, PATHINFO_BASENAME));
        $file->setAuthToken(TokenGenerator::generateToken(45));
        $file->setOriginalName($filepath);
        $file->setUploader('Administrator');
        $file = $fileHelper->createFile($file, true);
        print("File sucsessfuly added. Id: {$file->getId()}" . PHP_EOL);
    }

    private function deleteFile($id)
    {
        $fileHelper = $this->container->get('FileHelper');
        $fileMapper = $this->container->get('FileMapper');
        $file = $fileMapper->getFile($id);
        if($file == null) {
            die("File does not exsits." . PHP_EOL);
        }
        $file = $fileHelper->deleteFile($file);
        print("File sucsessfuly deleted." . PHP_EOL);
    }

    private function listFiles($limit, $offset)
    {
        $fileMapper = $this->container->get('FileMapper');
        $files = $fileMapper->getFiles($limit, $offset);
        if($files == null) {
            die("There is no files in specified range." . PHP_EOL);
        } else {
            foreach($files as $file) {
                print("{$file->getName()} (ID: {$file->getId()})" . PHP_EOL);
            }
        }
    }
}

$container = new \Slim\Container();
$container = getServices($container);
$cli = new CLItools($container);
$options = getopt("a:d:l:o:");
$cli->runWithOptions($options);