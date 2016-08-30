<?php

namespace Filehosting\Helper;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Entity\File;
use Filehosting\Exception\FileUploadException;
use Filehosting\Database\FileMapper;
use Filehosting\Database\SearchGateway;
use Slim\Http\UploadedFile;

/**
 * Adds or deletes files from the database.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class FileHelper
{
    /** @var Config $request An app Config class instance. */
    private $config;
    /** @var PreviewHelper $request PreviewHelper instance. */
    private $previewHelper;
    /** @var PathingHelper $request PathingHelper instance. */
    private $pathingHelper;
    /** @var FileMapper $request FileMapper instance. */
    private $fileMapper;
    /** @var CommentMapper $request CommentMapper insance. */
    private $commentMapper;
    /** @var SearchGateway $request SearchGateway instance. */
    private $searchGateway;
    /** @var IdHelper $request IdHelper instance. */
    private $idHelper;

    /**
     * Constructor.
     *
     * @param \Slim\Container $c DI container.
     */
    public function __construct(\Slim\Container $c)
    {
        $this->config = $c->get('config');
        $this->previewHelper = $c->get('PreviewHelper');
        $this->pathingHelper = $c->get('PathingHelper');
        $this->fileMapper = $c->get('FileMapper');
        $this->commentMapper = $c->get('CommentMapper');
        $this->searchGateway = $c->get('SearchGateway');
        $this->idHelper =  $c->get('IdHelper');
    }

    /**
     * Adds a given file to the database and moves it to the storage folder.
     *
     * @param File $file A file entity to add.
     *
     * @throws Exception if failed to access or create a storage folder
     *
     * @return File
     */
    public function uploadFile(File $file)
    {
        $this->fileMapper->beginTransaction();
        $file->setId($this->fileMapper->createFile($file));
        try {
            $this->isFileFolderAvailable($this->pathingHelper->getPathToFileFolder($file)); // throws FileUploadException
            $file->getUploadedFile()->moveTo($this->pathingHelper->getPathToFile($file)); //  throws \InvalidArgumentException and \RuntimeException
        } catch(\Exception $e) {
            $this->fileMapper->rollBack();
            throw new $e;
        }
        $this->searchGateway->indexNewFile($file);
        $fileInfo = $this->idHelper->analyzeFile($file);
        if($this->idHelper->isPreviewable($fileInfo)) {
            $this->previewHelper->generatePreview($file);
        }
        $this->fileMapper->commit();
        return $file;
    }

    /**
     * Deletes a given file from the database and a storage folder.
     *
     * @param File $file A file entity to delete.
     *
     * @throws Exception if failed to delete file from the filesystem
     *
     * @return bool
     */
    public function deleteFile(File $file)
    {
        $fileInfo = $this->idHelper->analyzeFile($file);
        $this->commentMapper->purgeComments($file->getId());
        $this->searchGateway->deleteIndexedFile($file);
        if($this->idHelper->isPreviewable($fileInfo)) {
            $this->previewHelper->deletePreview($file);
        }
        $this->fileMapper->deleteFile($file);
        if(!unlink($this->pathingHelper->getPathToFile($file))) {
            throw new \Exception(_("Can't unlink file. Try again or contact server administrators."));
        }
        return true;
    }

    /**
     * Checks if a file with a given ID exists.
     *
     * @todo Refactor this code, make it more simple.
     *
     * @param int $fileId A file ID in the database.
     *
     * @return bool
     */
    public function fileExists($fileId)
    {
        $file = $this->fileMapper->getFile($fileId);
        if($file && file_exists($this->pathingHelper->getPathToFile($file))) {
            return true;
        }
        return false;
    }

    /**
     * Returns a Response object with HTTP headers for X-Sendfile file download.
     *
     * @todo Make this static and relocate to Utils class.
     *
     * @param Request $fileId A file ID in the database.
     * @param Response $fileId A file ID in the database.
     * @param File $fileId A file ID in the database.
     *
     * @throws Exception if X-Sendfile module is not found.
     *
     * @return Response
     */
    public function getXsendfileHeaders(Request $request, Response $response, File $file)
    {
        $serverParams = $request->getServerParams();
        if($this->config->getValue('app', 'enableXsendfile') == 1) {
            if(strpos($serverParams["SERVER_SOFTWARE"], "nginx") !== false) {
                return $response->withHeader('X-Accel-Redirect', $this->pathingHelper->getXaccelPath($file))
                    ->withHeader('X-Accel-Charset', "utf-8");
            } else if(strpos($serverParams["SERVER_SOFTWARE"], "apache") !== false && in_array("mod_xsendfile", apache_get_modules())) {
                return $response->withHeader('X-Sendfile', $this->pathingHelper->getPathToFile($file));
            }
        }
        throw new \Exception(_("X-Sendfile either is not enabled or not supported by the server."));
    }

    /**
     * Checks if given file folder exists, and if not, trys to create it.
     *
     * @param int $fileId A file ID in the database.
     *
     * @throws FileUploadException if folder is not found and it cannot be created.
     *
     * @return bool
     */
    private function isFileFolderAvailable($fileFolder)
    {
        if(!is_dir($fileFolder)) {
            if(!mkdir($fileFolder)) {
                throw new FileUploadException(UPLOAD_ERR_CANT_WRITE);
            }
        }
        return true;
    }
}
