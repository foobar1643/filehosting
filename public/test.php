<?php

require("../app/init.php");

use Filehosting\Entity\File;
use Filehosting\Entity\TreeNode;
use Filehosting\Entity\Comment;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\RequestBody;
use Slim\Http\UploadedFile;
use Slim\Http\Uri;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\FigRequestCookies;
use \Filehosting\Helper\TokenGenerator;
use \Filehosting\Helper\AuthHelper;
use \Filehosting\Helper\CookieHelper;
use \Filehosting\Helper\Utils;

class Test {

}

/*
use \Filehosting\Entity\Comment;

$container = new \Slim\Container();
$container = getServices($container);

$cfg = $container->get('config');
$dsn = sprintf("mysql:host=%s;port=%s",
    $cfg->getValue('sphinx', 'host'),
    $cfg->getValue('sphinx', 'port'));
$pdo = new \PDO($dsn);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);


$commentGateway = $container->get('CommentMapper');
$commentHelper = $container->get('CommentHelper');
*/


/* ------------------------ COMMENT FILLER ------------------------*/

/*
function getRandomString($min, $max) {
    $string = null;
    $letters = preg_split("//u", "йцукенгшщзхъфывапролджэячсмитьбюЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮqwertyuiopasdfghjklzxcvbnmABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");
    for($i = 0; $i < mt_rand($min, $max); $i++) {
        $string .= $letters[mt_rand(0, count($letters) - 1)];
    }
    return $string;
}
$filePdo = $container->get('pdo');
$query = $filePdo->prepare("SELECT id FROM files");
$query->execute();
$ids = $query->fetchAll(\PDO::FETCH_COLUMN);
$c = 200;

for($i = 0; $i < $c; $i++) {
    $dateTime = new \DateTime("now");
    $comment = new Comment();
    $comment->setFileId($ids[mt_rand(0, count($ids) - 1)])
        ->setAuthor("Anonymous")
        ->setDatePosted($dateTime->format(\DateTime::ATOM))
        ->setCommentText(getRandomString(15, 200));
    $commentHelper->addComment($comment);
}

print("Added $c comments!");
/* ------------------------ COMMENT FILLER ------------------------*/


/*
print("
<form method='post' enctype='multipart/form-data'>
    <input id='file-upload-input' name='uploaded-file' type='file'>
    <input type='submit' name='sub'>
</form>
");

if($_POST) {
    $uploadedFile = new UploadedFile($_FILES['uploaded-file']['tmp_name'],
        $_FILES['uploaded-file']['name'],
        $_FILES['uploaded-file']['type'],
        $_FILES['uploaded-file']['size'],
        $_FILES['uploaded-file']['error']);
    $file = new File();
    $file->fromUploadedFile($uploadedFile);
    $file->setUploader('Anonymous')->setAuthToken("123456789");
    //$file = new File("/dev/null");
    var_dump($file);
}*/

/*$filePdo = $container->get('pdo');
$query = $filePdo->prepare("SELECT * FROM files ORDER by id DESC LIMIT 1 OFFSET 3");
$query->execute();
$files = $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
var_dump($files);*/

/*$commentHelper = $container->get('CommentHelper');
$commentPdo = $container->get('pdo');
$query = $commentPdo->prepare("SELECT * FROM comments WHERE file_id = 8 ORDER BY matpath ASC");
$query->execute();
$rawComments = $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\Comment');
$treeComments = $commentHelper->makeTrees($rawComments);

printTree($treeComments);*/
if($_GET['size']) {
    print(Utils::formatSize($_GET['size']));
}


/*$baseName = pathinfo('./filename   $#@^!#$ forbidden symbols!.jpg', PATHINFO_BASENAME);
print($baseName . "<br>");
print(urlencode($baseName));

print('<br><br><form method="post">
    <textarea name="text" rows="6" cols="12"></textarea>
    <input type="submit" name="send">
</form>');

if($_POST['send']) {
    print("<br><br><br>");
    print(nl2br($_POST['text']));
}*/

function fact($n)
{
    return ($n == 0) ? 1 : $n * fact($n - 1);
}

function generateToken($length)
{
    $source = str_split('abcdefghijklmnopqrstuvwxyz'
      .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
      .'0123456789');
    $result = array_rand($source, $length);
    return implode($result);
}

function getDateFromLocale(DateTime $date)
{
    $locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $dateFormater = new IntlDateFormatter($locale, IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM, "Europe/Kiev");
    return $dateFormater->format($date);
}

function printTree($tree, $depth = 0)
{
    foreach($tree as $key => $value) {
        for($i = 0; $i < $depth; $i++) print(".../");
        print("<b>{$value->getObject()->getId()}</b>: {$value->getObject()->getCommentText()} " . $value->countDescendants() . "<br>");
        if(count($value->getChildren()) > 0) {
            printTree($value->getChildren(), $depth + 1);
        }
    }
}

function getBranchSize($branch, $helper)
{
    $path = $helper->splitPath($branch->getParentPath());
    return count($path);
}