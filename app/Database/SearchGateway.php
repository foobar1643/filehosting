<?php

namespace Filehosting\Database;

use \Filehosting\Model\File;

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

    public function search($searchQuery, $limit, $offset)
    {
        $query = $this->pdo->prepare("SELECT id FROM index_files, rt_files WHERE MATCH(:match_bind) ORDER BY id DESC LIMIT :offset_bind, :limit_bind");
        $query->bindValue(":offset_bind", $offset, \PDO::PARAM_INT);
        $query->bindValue(":limit_bind", $limit, \PDO::PARAM_INT);
        $query->bindValue(":match_bind", $searchQuery, \PDO::PARAM_STR);
        $query->execute();
        return $query->fetchAll();
    }

    public function insertRtValue($id, $name)
    {
        $query = $this->pdo->prepare("INSERT INTO rt_files VALUES(:id_bind, :name_bind)");
        $query->bindValue(":id_bind", $id, \PDO::PARAM_INT);
        $query->bindValue(":name_bind", $name);
        $query->execute();
    }

    public function deleteRtValue($id)
    {
        $query = $this->pdo->prepare("DELETE FROM rt_files WHERE id = :id_bind");
        $query->bindValue(":id_bind", $id, \PDO::PARAM_INT);
        $query->execute();
    }
}