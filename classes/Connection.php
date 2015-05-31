<?php

class Connection {
    private $pdo;

    public function __construct($dsn, $host, $pass, $options = []) {
        $this->pdo = new PDO($dsn, $host, $pass, $options);
    }

    public function getUserByNick($nick) {
        $sql = 'SELECT * FROM Users WHERE nick = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nick]);
        return $stmt->fetch();
    }

    public function addUser($user) {
        $sql = 'INSERT INTO Users (`nick`, `e-mail`, `pass`, `age`) VALUES (?, ?, ?, ?)';
        $this->pdo->prepare($sql)->execute($user);
    }

    public function addPost($post) {
        $sql = 'INSERT INTO Posts (`post_id`, `nick`, `title`, `text`, `date`) VALUES (?, ?, ?, ?, ?)';
        $this->pdo->prepare($sql)->execute($post);
    }

    public function addComment($comment) {
        $sql = 'INSERT INTO Comments (`comment_id`, `post_id`, `nick`, `text`, `date`) VALUES (?, ?, ?, ?, ?)';
        $this->pdo->prepare($sql)->execute($comment);
    }

    public function getAllPostsByNick($nick) {
        $sql = 'SELECT * FROM Posts WHERE `nick`=? ORDER BY `date` DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nick]);
        return $stmt->fetchAll();
    }

    public function getAllCommentsByPostId($postId) {
        $sql = 'SELECT * FROM Comments WHERE `post_id`=? ORDER BY `date` DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function getPostByPostId($postId) {
        $sql = 'SELECT * FROM Posts WHERE `post_id`=?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$postId]);
        return $stmt->fetch();
    }

    public function deletePostByPostId($postId) {
        $sql = 'DELETE FROM Posts WHERE `post_id`=?';
        $this->pdo->prepare($sql)->execute([$postId]);
    }

    public function deleteCommentByCommentId($commentId) {
        $sql = 'DELETE FROM Comments WHERE `comment_id`=?';
        $this->pdo->prepare($sql)->execute([$commentId]);
    }

    public function getLastUsers($number) {
        $sql = 'SELECT * FROM Users ORDER BY `nick` LIMIT 0, :number';
        $stmt =  $this->pdo->prepare($sql);
        $stmt->bindParam(':number', $number, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}