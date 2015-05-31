<?php

class Form {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function checkRegister($fields) {
        $errors = [];

        if (!$this->isValidNick($fields['nick'])) {
            $errors['nick'] = true;
        }

        if (!$this->isValidString($fields['pass'])) {
            $errors['pass'] = true;
        }

        if ($fields['pass'] !== $fields['confirmPass'] || !$this->isValidString($fields['pass'])) {
            $errors['confirmPass'] = true;
        }

        if (!$this->isValidEmail($fields['email'])) {
            $errors['email'] = true;
        }

        if (!isset($fields['age']['day'])) {
            $errors['day'] = true;
        }

        if (!isset($fields['age']['month'])) {
            $errors['month'] = true;
        }

        if (!isset($fields['age']['year'])) {
            $errors['year'] = true;
        }

        return $errors;
    }

    public function checkAuth($fields) {
        $errors = [];

        $user = $this->db->getUserByNick($fields['nick']);

        if (!$user) {
            $errors['pass'] = $errors['nick'] = true;

        } elseif ($fields['pass'] !== $user['pass']) {
            $errors['pass'] = true;
        }

        return $errors;
    }

    public function isValidNick($nick) {
        $user = $this->db->getUserByNick($nick);
        return !$user && $this->isValidString($nick);
    }

    public function isValidString($string) {
        return !(preg_match('/[^a-zA-Z0-9]/', $string) || strlen($string) > 25 || strlen($string) < 5);
    }

    public function isValidEmail($email) {
        return preg_match('/[a-zA-Z0-9]+@[a-z]+.[a-z]+/', $email);
    }

}