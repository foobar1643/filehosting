<?php

namespace Filehosting\Helper;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Entity\File;
use Filehosting\Exception\FileUploadException;
use Slim\Container;

/**
 * Adds or deletes files from the database.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class FileHelper
{
    /**
     * @var \Filehosting\Config An app Config instance.
     */
    private $config;

    /**
     * @var \Filehosting\Helper\PreviewHelper PreviewHelper instance.
     */
    private $previewHelper;

    /**
     * @var \Filehosting\Helper\PreviewHelper PathingHelper instance.
     */
    private $pathingHelper;

    /**
     * @var \Filehosting\Database\FileMapper FileMapper instance.
     */
    private $fileMapper;

    /**
     * @var \Filehosting\Database\CommentMapper CommentMapper Instance
     */
    private $commentMapper;

    /**
     * @var \Filehosting\Database\SearchGateway SearchGateway instance.
     */
    private $searchGateway;

    /**
     * @var \Filehosting\Helper\IdHelper IdHelper instance.
     */
    private $idHelper;

    /**
     * Constructor.
     *
     * @param \Slim\Container $c DI container.
     */
    public function __construct(Container $c)
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
     * @throws \Exception if failed to access or create a storage folder
     *
     * @return File
     */
    public function uploadFile(File $file)
    {
        $this->fileMapper->beginTransaction();
        $file->setId($this->fileMapper->createFile($file));
        try {
            // throws FileUploadException
            $this->isFileFolderAvailable($this->pathingHelper->getPathToFileFolder($file));
            // throws \InvalidArgumentException and \RuntimeException
            $file->getUploadedFile()->moveTo($this->pathingHelper->getPathToFile($file));
        } catch (\Exception $e) {
            $this->fileMapper->rollBack();
            throw new $e;
        }
        $this->searchGateway->indexNewFile($file);
        $fileInfo = $this->idHelper->analyzeFile($file);
        if ($this->idHelper->isPreviewable($fileInfo)) {
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
     * @throws \Exception if failed to delete file from the filesystem
     *
     * @return bool
     */
    public function deleteFile(File $file)
    {
        $fileInfo = $this->idHelper->analyzeFile($file);
        $this->commentMapper->purgeComments($file->getId());
        $this->searchGateway->deleteIndexedFile($file);
        if ($this->idHelper->isPreviewable($fileInfo)) {
            $this->previewHelper->deletePreview($file);
        }
        $this->fileMapper->deleteFile($file);
        if (!unlink($this->pathingHelper->getPathToFile($file))) {
            throw new \Exception(_("Can't unlink file. Try again or contact server administrators."));
        }
        return true;
    }

    /**
     * Checks if a file with a given ID exists.
     *
     * @param int $fileId A file ID in the database.
     *
     * @return bool
     */
    public function fileExists(int $fileId): bool
    {
        $file = $this->fileMapper->getFile($fileId);

        return ($file && file_exists($this->pathingHelper->getPathToFile($file)));
    }

    /**
     * Returns a Response object with HTTP headers for X-Sendfile file download.
     *
     * @todo Make this static and relocate to Utils class.
     *
     * @param Request $request PSR-7 Request instance.
     * @param Response $response PSR-7 Response instance.
     * @param File $file File to download.
     *
     * @throws \Exception if X-Sendfile module is not found.
     *
     * @return Response
     */
    public function getXsendfileHeaders(Request $request, Response $response, File $file): Response
    {
        $serverParams = $request->getServerParams();
        if ($this->config->getValue('app', 'enableXsendfile') == 1) {
            if (strpos($serverParams["SERVER_SOFTWARE"], "nginx") !== false) {
                return $response->withHeader('X-Accel-Redirect', $this->pathingHelper->getXaccelPath($file))
                    ->withHeader('X-Accel-Charset', "utf-8");
            } elseif (strpos($serverParams["SERVER_SOFTWARE"], "apache") !== false
                && in_array("mod_xsendfile", apache_get_modules())) {
                return $response->withHeader('X-Sendfile', $this->pathingHelper->getPathToFile($file));
            }
        }
        throw new \Exception(_("X-Sendfile either is not enabled or not supported by the server."));
    }

    /**
     * Checks if given file folder exists, and if not, tries to create it.
     *
     * @param string $fileFolder
     *
     * @throws FileUploadException if folder is not found and it cannot be created.
     *
     * @return bool if folder is not found and it cannot be created.
     *
     */
    private function isFileFolderAvailable(string $fileFolder): bool
    {
        if (!is_dir($fileFolder)) {
            if (!mkdir($fileFolder)) {
                throw new FileUploadException(UPLOAD_ERR_CANT_WRITE);
            }
        }
        return true;
    }
}
