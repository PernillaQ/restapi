<?php

namespace App\Controllers;

class EntriesController
{
    private $db;

    /**
     * Dependeny Injection (DI): http://www.phptherightway.com/#dependency_injection
     * If this class relies on a database-connection via PDO we inject that connection
     * into the class at start. If we do this TodoController will be able to easily
     * reference the PDO with '$this->db' in ALL functions INSIDE the class
     * This class is later being injected into our container inside of 'App/container.php'
     * This results in we being able to call '$this->get('entries')' to call this class
     * inside of our routes.
     */
    public function __construct(\PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getAll($limit)
    {
            $argss = func_num_args();// array of parameters. Super Cecilia! <3

        if (func_get_arg(1) == null)
        { // Any args?
            $limit = func_get_arg(0); // get arg - limit
            $getAllEntries= $this->db->prepare("SELECT * FROM entries LIMIT :limit");
            $getAllEntries->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $getAllEntries->execute();
            $allEntries = $getAllEntries->fetchAll();
            return $allEntries;
        }

        else if((func_get_arg(1) != null))
        {
            $title = func_get_arg(1); // get arg - title
            $getAllEntries = $this->db->prepare("SELECT * FROM entries WHERE title LIKE :title");
            $getAllEntries->execute([':title' => '%'.$title.'%']);
            $allEntries = $getAllEntries->fetchAll();
            return $allEntries;
        }
        else
        {   //if no args get all.
          $getAll = $this->db->prepare('SELECT * FROM entries LIMIT :limit');
          $getAll->bindParam(':limit', $limit, \PDO::PARAM_INT);
          $getAll->execute();
          return $getAll->fetchAll();
        }
    }

    public function getEntryComments($id)
    {
        $argss = func_num_args();// array of parameters.

        if ((func_get_arg(1) != null))
        {
            $limit = func_get_arg(1);
            $getEntryComments = $this->db->prepare("SELECT * FROM comments WHERE entryID = :id LIMIT :limit");
            $getEntryComments->bindParam(':id', $id);
            $getEntryComments->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $getEntryComments->execute();
            $entryComments = $getEntryComments->fetchAll();
            return $entryComments;
        }
        else
        {
            $getEntryComments = $this->db->prepare("SELECT * FROM comments WHERE entryID = :id");
            $getEntryComments->execute([":id" => $id]);
            $entryComments = $getEntryComments->fetchAll();
            return $entryComments;
        }
    }

    public function getOne($id)
    {
        $getOne = $this->db->prepare('SELECT * FROM entries WHERE entryID = :id');
        $getOne->execute([':id' => $id]);
        return $getOne->fetch();
    }

    public function deleteOne($id)
    {
        $deleteOne = $this->db->prepare('DELETE FROM entries WHERE entryID = :id');
        $deleteOne->execute([':id' => $id]);
    }

    public function update($id,$body)
    {
      $updateOne = $this->db->prepare('UPDATE entries SET title = :title, content = :content WHERE entryID = :entryID');
      $updateOne->execute([ ':title' => $body['title'], ':content' => $body['content'], ':entryID' => $id]);
    }

    public function add($entry)
    {
        $newDate= date("Y-m-d H:i:s", strtotime('+2 hours'));

        $addOne = $this->db->prepare(
            'INSERT INTO entries (title, content, createdBy, createdAt) VALUES (:title, :content, :createdBy, :createdAt)'
        );
        
        /**
         * Insert the value from the parameter into the database
         */
        $addOne->execute([':title' => $entry['title'],
          ':content'  => $entry['content'],
          ':createdBy' => $entry['createdBy'],
          ':createdAt' => $newDate
          ]);

        /**
         * A INSERT INTO does not return the created object. If we want to return it to the user
         * that has posted the todo we must build it ourself or fetch it after we have inserted it
         * We can always get the last inserted row in a database by calling 'lastInsertId()'-function
         */
        return [
          'id'          => (int)$this->db->lastInsertId(),
          'title'       => $entry['title'],
          'content'     => $entry['content'],
          'completed'   => false
        ];
    }
}
