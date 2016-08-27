<?php

namespace Filehosting\Database;

use \Filehosting\Entity\Comment;

class CommentMapper
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addComment(Comment $comment)
    {
        $query = $this->pdo->prepare("INSERT INTO comments (file_id, parent_id, author, date_posted, comment_text, matpath)"
            ."VALUES (:file_id_bind, :parent_id, :author_bind, :date_bind, :text_bind, :matpath_bind) RETURNING id");
        $query->bindValue(":parent_id", ($comment->getParentId() != 0 ? $comment->getParentId() : NULL), \PDO::PARAM_INT);
        $query->bindValue(":file_id_bind", $comment->getFileId(), \PDO::PARAM_INT);
        $query->bindValue(":author_bind", $comment->getAuthor(), \PDO::PARAM_STR);
        $query->bindValue(":date_bind", ($comment->getDatePosted() === null) ? 'DEFAULT' : $comment->getDatabaseDate() , \PDO::PARAM_STR);
        $query->bindValue(":text_bind", $comment->getCommentText(), \PDO::PARAM_STR);
        $query->bindValue(":matpath_bind", $comment->getMatPath(), \PDO::PARAM_STR);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getRootMaxPath($fileId)
    {
        $query = $this->pdo->prepare("SELECT MAX(matpath) FROM comments WHERE NOT (parent_id IS NOT NULL) AND file_id = :file_id_bind");
        $query->bindValue(":file_id_bind", $fileId, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getChildMaxPath($parentId)
    {
        $query = $this->pdo->prepare("SELECT MAX(matpath) FROM comments WHERE parent_id = :parent_id_bind");
        $query->bindValue(":parent_id_bind", $parentId, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getComments($fileId)
    {
        $query = $this->pdo->prepare("SELECT * FROM comments WHERE file_id = :file_id_bind ORDER BY matpath ASC");
        $query->bindValue(":file_id_bind", $fileId, \PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, '\Filehosting\Entity\Comment');
        return $query->fetchAll(\PDO::FETCH_CLASS, '\Filehosting\Entity\Comment');
    }

    public function getComment($commentId)
    {
        $query = $this->pdo->prepare("SELECT * FROM comments WHERE id = :id_bind");
        $query->bindValue(":id_bind", $commentId, \PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, '\Filehosting\Entity\Comment');
        return $query->fetch();
    }

    public function deleteComment($commentId)
    {
        $query = $this->pdo->prepare("DELETE FROM comments WHERE parent_path LIKE :comment_id");
        $query->bindValue(":comment_id", "%" . $commentId . "%", \PDO::PARAM_STR);
        $query->execute();
        return $query->rowCount();
    }

    public function purgeComments($fileId)
    {
        $query = $this->pdo->prepare("DELETE FROM comments WHERE file_id = :file_id_bind");
        $query->bindValue(":file_id_bind", $fileId, \PDO::PARAM_INT);
        $query->execute();
    }
}