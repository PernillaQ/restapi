<?php

namespace App\Controllers;

class CommentsController
{
    private $db;

    public function __construct(\PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getAll()
    {
        $getAll = $this->db->prepare('SELECT * FROM comments');
        $getAll->execute();
        return $getAll->fetchAll();
    }

    public function getOne($id)
    {
        $getOne = $this->db->prepare('SELECT * FROM comments WHERE commentsID = :id');
        $getOne->execute([':id' => $id]);
        return $getOne->fetch();
    }

    public function add($entry)
    {
        /**
         * Default 'completed' is false so we only need to insert the 'content'
         */
        $addOne = $this->db->prepare(
            'INSERT INTO comments (content, createdBy) VALUES (:content, :createdBy)'
        );

        /**
         * Insert the value from the parameter into the database
         */
        $addOne->execute([':content'  => $entry['content'],
          ':createdBy' => $entry['createdBy']]);

        /**
         * A INSERT INTO does not return the created object. If we want to return it to the user
         * that has posted the todo we must build it ourself or fetch it after we have inserted it
         * We can always get the last inserted row in a database by calling 'lastInsertId()'-function
         */
        return [
          'id'          => (int)$this->db->lastInsertId(),
          'content'     => $entry['content'],
          'completed'   => false
        ];
    }
}