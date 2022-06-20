<?php

use LDAP\Result;

function getUser(string $username, PDO $connection): int {
    $statement = $connection->prepare('SELECT id FROM users WHERE username=:username');
    $status = $statement->execute(array("username" => $username));

    if (!$status) {
        return -1;
    }

    return $statement->fetchAll()[0]['id'];
}

function checkIfFileExists(string $md5, int $user, PDO $connection): string {
    $statement = $connection->prepare('SELECT name FROM files WHERE hash=:hash AND user=:user');
    $status = $statement->execute(array("hash" => $md5, "user" => $user));

    if (!$status) {
        return "";
    }

    $result = $statement->fetchAll();

    if (sizeof($result) != 0) {
        return $result[0]['name'];
    }

    return "";
}

function addFile(string $md5, int $user, string $name, PDO $connection): bool {
    $statement = $connection->prepare('INSERT INTO files (hash, name, user) VALUES (:hash, :name, :user)');
    $status = $statement->execute(array("hash" => $md5, "name" => $name, "user" => $user));

    if (!$status) {
        return false;
    }
    
    return true;
}

function deleteFile(string $md5, int $user, PDO $connection): bool {
    $statement = $connection->prepare("DELETE FROM files WHERE hash=:hash AND user=:user");
    $status = $statement->execute(array("hash" => $md5, "user" => $user));

    if (!$status) {
        return false;
    }

    return true;
}

function getFileByHash(string $md5, int $user_id, PDO $connection): int {
    $statement = $connection->prepare("SELECT id FROM files WHERE hash=:hash AND user=:user");
    $result = $statement->execute(array("hash" => $md5, "user" => $user_id));

    if (!$result) {
        return -1;
    }

    $arr = $statement->fetchAll();

    if (sizeof($arr) == 0) {
        return -2;
    }

    return $arr[0]['id'];
}