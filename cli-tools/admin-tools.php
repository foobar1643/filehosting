#!/usr/bin/php
<?php

require("../app/init.php");

use \Filehosting\Helper\TokenGenerator;
use \Filehosting\Entity\Comment;

class CLItools
{
    const TEMP_FILENAME = "/tmp/filehosting_cli_uploaded_file";

    private $container;
    private $commentHelper;
    private $fileHelper;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
        $this->commentHelper = $c->get('CommentHelper');
        $this->fileHelper = $c->get('FileHelper');
    }

    public function runWithOptions($options)
    {
        switch(key($options)) {
            case "add-file":
                $this->checkRequiredOptions(['f'], $options);
                $this->addFile($options['f']);
                break;
            case "delete-file": // requires option -i - used as file id
                $this->checkRequiredOptions(['i'], $options);
                $this->deleteFile($options['i']);
                break;
            case "list-files": // requires options -o and -l - they're used as offset and limit respectively
                $this->checkRequiredOptions(['o', 'l'], $options);
                $this->listFiles($options['o'], $options['l']);
                break;
            case "add-comment": // requires options -i and -t - they're used as file id and comment text respectively, also accepts option -p - used as parent comment id
                $this->checkRequiredOptions(['i', 't'], $options);
                $options['p'] = isset($options['p']) ? $options['p'] : NULL;
                $this->addComment($options['i'], $options['t'], $options['p']);
                break;
            case "delete-comment": // requires option -i - used as comment id
                $this->checkRequiredOptions(['i'], $options);
                $this->deleteComment($options['i']);
                break;
            case "purge-comments": // requires option -i - used as file id
                $this->checkRequiredOptions(['i'], $options);
                $this->purgeComments($options['i']);
                break;
            case "list-comments": // requires option -i - used as file id
                $this->checkRequiredOptions(['i'], $options);
                $this->listComments($options['i']);
                break;
            default:
                die($this->outputHelpMessage());
                break;
        }
    }

    private function checkRequiredOptions(array $required, $options)
    {
        foreach($required as $key => $value) {
            if(!isset($options[$value])) {
                die($this->outputHelpMessage());
            }
        }
        return true;
    }

    public function outputHelpMessage()
    {
        $argv = $_SERVER['argv'];
        print("This is a command line PHP script for administrators. The script can accept following options:" . PHP_EOL . PHP_EOL
        . "Option --add-file. Usage: {$argv[0]} --add-file -f <option>" . PHP_EOL
        . "<option> must be a valid path to file you want to add to the app." . PHP_EOL . PHP_EOL
        . "Option --delete-file. Usage: {$argv[0]} --delete-file -i <option>" . PHP_EOL
        . "<option> must be a valid id of the file you want to delete from the app." . PHP_EOL . PHP_EOL
        . "Option --list-files. Usage: {$argv[0]} --list-files -o <offset> -l <limit>" . PHP_EOL
        . "<offset> must be a positive numeric value that will be used as an offset in file selection." . PHP_EOL
        . "<limit> must be a positive numeric value that will be used as a limit in file selection." . PHP_EOL . PHP_EOL
        . "Option --add-comment. Usage: {$argv[0]} --add-comment -i <file-id> -t <text>" . PHP_EOL
        . "<file-id> must be a positive numeric value that will be used as a file id." . PHP_EOL
        . "<text> must be a quoted string that will be used as a comment text." . PHP_EOL
        . "Additionally, you can add an optional parameter -p, it must be a positive numeric value that will be used as a parrent comment id." . PHP_EOL . PHP_EOL
        . "Option --delete-comment. Usage: {$argv[0]} --delete-comment -i <option>" . PHP_EOL
        . "<option> must be a positive numeric value that will be used as a comment id." . PHP_EOL . PHP_EOL
        . "Option --purge-comments. Usage: {$argv[0]} --purge-comments -i <option>" . PHP_EOL
        . "<option> must be a positive numeric value that will be used as a file id." . PHP_EOL . PHP_EOL
        . "Option --list-comments. Usage: {$argv[0]} --list-comments -i <option>" . PHP_EOL
        . "<option> must be a positive numeric value that will be used as file id." . PHP_EOL . PHP_EOL
        . "Additionally, with the -h option you can get this help." . PHP_EOL);
    }

    private function addFile($filepath)
    {
        if(!file_exists($filepath)) {
            die("File $filepath does not exists." . PHP_EOL);
        }
        copy($filepath, self::TEMP_FILENAME);
        $uploadedFile = new Slim\Http\UploadedFile(self::TEMP_FILENAME, pathinfo($filepath, PATHINFO_BASENAME));
        $file = new \Filehosting\Entity\File();
        $file->setName(pathinfo($filepath, PATHINFO_BASENAME))
            ->setAuthToken(TokenGenerator::generateToken(45))
            ->setUploadObject($uploadedFile)
            ->setUploader('Administrator');
        $file = $this->fileHelper->uploadFile($file);
        print("File sucsessfuly added. ID: {$file->getId()}" . PHP_EOL);
    }

    private function deleteFile($id)
    {
        if(!$this->fileHelper->fileExists($id)) {
            die("File does not exsits." . PHP_EOL);
        }
        $fileMapper = $this->container->get('FileMapper');
        $file = $fileMapper->getFile($id);
        $file = $this->fileHelper->deleteFile($file);
        print("File sucsessfuly deleted." . PHP_EOL);
    }

    private function listFiles($offset, $limit)
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

    private function addComment($fileId, $text, $parentId = null)
    {
        $validator = $this->container->get('Validation');
        $comment = new Comment();
        $dateTime = new \DateTime("now");
        $comment->setFileId($fileId)
            ->setAuthor("Administrator")
            ->setDatePosted($dateTime->format(\DateTime::ATOM))
            ->setCommentText($text)
            ->setParentId((isset($parentId) ? $parentId : NULL));
        $errors = $validator->validateComment($comment);
        if(!$errors) {
            $comment = $this->commentHelper->addComment($comment);
            print("Comment sucsessfuly added, ID: " . $comment->getId() . PHP_EOL);
        } else {
            foreach($errors as $key => $error) {
                print($error . PHP_EOL);
            }
        }
    }

    private function deleteComment($commentId)
    {
        $commentMapper = $this->container->get('CommentMapper');
        if(!$this->commentHelper->commentExists($commentId)) {
            die("Comment does not exist." . PHP_EOL);
        }
        $children = $commentMapper->deleteComment($this->commentHelper->normalizePath($commentId));
        print("Comment sucsessfuly deleted. Child comments affected: " . ($children - 1) . PHP_EOL);
    }

    private function purgeComments($fileId)
    {
        $commentMapper = $this->container->get('CommentMapper');
        if(!$this->fileHelper->fileExists($fileId)) {
            die("File does not exsits." . PHP_EOL);
        }
        $commentMapper->purgeComments($fileId);
        print("Comments sucsessfuly purged." . PHP_EOL);
    }

    private function listComments($fileId)
    {
        if(!$this->fileHelper->fileExists($fileId)) {
            die("File does not exsits." . PHP_EOL);
        }
        $tree = $this->commentHelper->getComments($fileId);
        $this->printTree($tree['comments']);
    }

    private function printTree($tree, $depth = 0)
    {
        foreach($tree as $key => $comment) {
            for($i = 0; $i < $depth; $i++) print(".../");
            print("{$comment->getId()}: {$comment->getCommentText()} (". $comment->countDescendants() .")" . PHP_EOL);
            if($comment->countChildNodes() > 0) {
                $this->printTree($comment->getChildren(), $depth + 1);
            }
        }
    }
}

$cli = new CLItools($container);
$options = getopt("f:i:l:o:p:t:",
    ["add-file", "delete-file", "list-files",
    "add-comment", "delete-comment", "purge-comments", "list-comments"]);
$cli->runWithOptions($options);
