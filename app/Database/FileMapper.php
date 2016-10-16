<?php

namespace Filehosting\Database;

use Filehosting\Entity\File;

/**
 * Provides a simple interface that works with a 'files' table in the database.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class FileMapper
{
    /** @var PDO $pdo PDO object. */
    private $pdo;

    /**
     * Constructor.
     *
     * @param PDO $pdo PDO object.
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Begins database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commits database transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * Rolls back database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    /**
     * Deletes file from the database.
     *
     * @param File $file A File entity to delete.
     *
     * @return void
     */
    public function deleteFile(File $file)
    {
        $query = $this->pdo->prepare("DELETE FROM files WHERE id = :id_bind");
        $query->bindValue(":id_bind", $file->getId(), \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * Updates file in the database.
     *
     * @param File $file A File entity to update.
     *
     * @return void
     */
    public function updateFile(File $file)
    {
        $query = $this->pdo->prepare("UPDATE files SET name = :name_bind,"
        ." downloads = :downloads_bind WHERE id = :id_bind");
        $query->bindValue(":id_bind", $file->getId(), \PDO::PARAM_INT);
        $query->bindValue(":name_bind", $file->getClientFilename(), \PDO::PARAM_STR);
        $query->bindValue(":downloads_bind", $file->getDownloads(), \PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * Returns an array with last files.
     *
     * @param int $count A number of files to select.
     *
     * @return array
     */
    public function getLastFiles($count)
    {
        $query = $this->pdo->prepare("SELECT * FROM files ORDER BY upload_date DESC LIMIT 10");
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
        return $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
    }

    /**
     * Returns an array with popular files.
     *
     * @param int $count A number of files to select.
     *
     * @return array
     */
    public function getPopularFiles($count)
    {
        $query = $this->pdo->prepare("SELECT * FROM files ORDER BY downloads DESC LIMIT 10");
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
        return $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
    }

    /**
     * Adds a file to the database and returns the ID of the added file.
     *
     * @param File $file A File entity to be added.
     *
     * @return int
     */
    public function createFile(File $file)
    {
        $query = $this->pdo->prepare("INSERT INTO files (name, uploader, size, auth_token)"
            ."VALUES (:name_bind, :uploader_bind, :filesize_bind, :token_bind) RETURNING id");
        $query->bindValue(":name_bind", $file->getClientFilename(), \PDO::PARAM_STR);
        $query->bindValue(":uploader_bind", $file->getUploader(), \PDO::PARAM_STR);
        $query->bindValue(":filesize_bind", $file->getSize(), \PDO::PARAM_INT);
        $query->bindValue(":token_bind", $file->getAuthToken(), \PDO::PARAM_STR);
        $query->execute();
        return $query->fetchColumn();
    }

    /**
     * Filters files using their IDs, reutrns files that are present in the database.
     *
     * @param File $file A File entity to delete.
     *
     * @return array
     */
    public function getFilteredFiles($ids)
    {
        if(empty($ids)) {
            return false;
        }
        $sql = "SELECT * FROM files WHERE id IN(";
        for($i = 0; $i < count($ids); $i++) {
            $sql .= "?" . (isset($ids[$i+1]) ? ", " : "");
        }
        $sql .= ")";
        $query = $this->pdo->prepare($sql);
        $query->execute($ids);
        return $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
    }

    /**
     * Returns a single file from the database.
     *
     * @param int $id ID of the file in the database.
     *
     * @return File
     */
    public function getFile($id)
    {
        $query = $this->pdo->prepare("SELECT * FROM files WHERE id = :id_bind");
        $query->bindValue("id_bind", $id, \PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
        return $query->fetch();
    }
}
