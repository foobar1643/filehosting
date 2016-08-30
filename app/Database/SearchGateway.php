<?php

namespace Filehosting\Database;

use Filehosting\Entity\File;

/**
 * Provides a simple interface that works with a Sphinx search through SphinxQL.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class SearchGateway
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
     * Counts total matches in a given query.
     *
     * @todo Remove this method.
     *
     * @param string $searchQuery A query to count matches in.
     *
     * @return int
     */
    public function countSearchResults($searchQuery)
    {
        $query = $this->pdo->prepare("SELECT COUNT(*) FROM index_files, rt_files WHERE MATCH(:match_bind);");
        $query->bindValue(":match_bind", $searchQuery, \PDO::PARAM_STR);
        $query->execute();
        return $query->fetchColumn();
    }

    /**
     * Returns additional meta information.
     *
     * @return array
     */
    public function showMeta()
    {
        $query = $this->pdo->prepare("SHOW META");
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * Preforms a search query and returns the result.
     *
     * @param string $searchQuery A query to execute.
     * @param int $offset Offset to apply to query.
     * @param int $limit Limit to applay to query.
     *
     * @return array
     */
    public function searchQuery($searchQuery, $offset, $limit)
    {
        $query = $this->pdo->prepare("SELECT id FROM index_files, rt_files WHERE MATCH(:match_bind) ORDER BY id ASC LIMIT :offset_bind, :limit_bind");
        $query->bindValue(":offset_bind", $offset, \PDO::PARAM_INT);
        $query->bindValue(":limit_bind", $limit, \PDO::PARAM_INT);
        $query->bindValue(":match_bind", $searchQuery, \PDO::PARAM_STR);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Adds a file to realtime index.
     *
     * @param File $file File to add.
     *
     * @return void
     */
    public function indexNewFile(File $file)
    {
        $query = $this->pdo->prepare("INSERT INTO rt_files VALUES(:id_bind, :name_bind)");
        $query->bindValue(":id_bind", $file->getId(), \PDO::PARAM_INT);
        $query->bindValue(":name_bind", $file->getClientFilename(), \PDO::PARAM_STR);
        $query->execute();
    }

    /**
     * Removes a file from realtime index.
     *
     * @param File $file File to remove.
     *
     * @return void
     */
    public function deleteIndexedFile(File $file)
    {
        $query = $this->pdo->prepare("DELETE FROM rt_files WHERE id = :id_bind");
        $query->bindValue(":id_bind", $file->getId(), \PDO::PARAM_INT);
        $query->execute();
    }
}