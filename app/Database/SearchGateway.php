<?php

namespace Filehosting\Database;

use \Filehosting\Entity\File;

class SearchGateway
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function countSearchResults($searchQuery)
    {
        $query = $this->pdo->prepare("SELECT COUNT(*) FROM index_files, rt_files WHERE MATCH(:match_bind);");
        $query->bindValue(":match_bind", $searchQuery, \PDO::PARAM_STR);
        $query->execute();
        return $query->fetchColumn();
    }

    public function showMeta()
    {
        $query = $this->pdo->prepare("SHOW META");
        $query->execute();
        return $query->fetchAll();
    }

    public function searchQuery($searchQuery, $offset, $limit)
    {
        $query = $this->pdo->prepare("SELECT id FROM index_files, rt_files WHERE MATCH(:match_bind) ORDER BY id ASC LIMIT :offset_bind, :limit_bind");
        $query->bindValue(":offset_bind", $offset, \PDO::PARAM_INT);
        $query->bindValue(":limit_bind", $limit, \PDO::PARAM_INT);
        $query->bindValue(":match_bind", $searchQuery, \PDO::PARAM_STR);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function indexNewFile(File $file)
    {
        $query = $this->pdo->prepare("INSERT INTO rt_files VALUES(:id_bind, :name_bind)");
        $query->bindValue(":id_bind", $file->getId(), \PDO::PARAM_INT);
        $query->bindValue(":name_bind", $file->getClientFilename(), \PDO::PARAM_STR);
        $query->execute();
    }

    public function deleteIndexedFile(File $file)
    {
        $query = $this->pdo->prepare("DELETE FROM rt_files WHERE id = :id_bind");
        $query->bindValue(":id_bind", $file->getId(), \PDO::PARAM_INT);
        $query->execute();
    }
}