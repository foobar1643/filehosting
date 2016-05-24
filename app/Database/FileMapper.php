<?php

namespace Filehosting\Database;

use \Filehosting\Entity\File;

class FileMapper
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    public function deleteFile(File $file)
    {
        $query = $this->pdo->prepare("DELETE FROM files WHERE id = :id_bind");
        $query->bindValue(":id_bind", $file->getId(), \PDO::PARAM_INT);
        $query->execute();
    }

    public function updateFile(File $file)
    {
        $query = $this->pdo->prepare("UPDATE files SET name = :name_bind,"
        ." downloads = :downloads_bind WHERE id = :id_bind");
        $query->bindValue(":id_bind", $file->getId(), \PDO::PARAM_INT);
        $query->bindValue(":name_bind", $file->getName(), \PDO::PARAM_STR);
        $query->bindValue(":downloads_bind", $file->getDownloads(), \PDO::PARAM_INT);
        $query->execute();
    }

    public function getLastFiles($count)
    {
        $query = $this->pdo->prepare("SELECT * FROM files ORDER BY upload_date DESC LIMIT 10");
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
        return $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
    }

    public function getPopularFiles($count)
    {
        $query = $this->pdo->prepare("SELECT * FROM files ORDER BY downloads DESC LIMIT 10");
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
        return $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
    }

    public function createFile(File $file)
    {
        $query = $this->pdo->prepare("INSERT INTO files (name, uploader, auth_token)"
            ."VALUES (:name_bind, :uploader_bind, :token_bind) RETURNING id");
        $query->bindValue(":name_bind", $file->getName(), \PDO::PARAM_STR);
        $query->bindValue(":uploader_bind", $file->getUploader(), \PDO::PARAM_STR);
        $query->bindValue(":token_bind", $file->getAuthToken(), \PDO::PARAM_STR);
        $query->execute();
        return $query->fetchColumn();
    }

    public function countFiles()
    {
        $query = $this->pdo->prepare("SELECT COUNT(*) FROM files");
        $query->execute();
        return $query->fetchColumn();
    }

    public function getFiles($limit, $offset)
    {
        $query = $this->pdo->prepare("SELECT * FROM files ORDER BY id DESC LIMIT :limit_bind OFFSET :offset_bind");
        $query->bindValue(":limit_bind", $limit, \PDO::PARAM_INT);
        $query->bindValue(":offset_bind", $offset, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
    }

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

    public function getFile($id)
    {
        $query = $this->pdo->prepare("SELECT * FROM files WHERE id = :id_bind");
        $query->bindValue("id_bind", $id, \PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, '\Filehosting\Entity\File');
        return $query->fetch();
    }
}
