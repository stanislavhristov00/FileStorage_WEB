<?php

require_once '../libs/Bootstrap.php';
require_once './utils/db_utils.php';

Bootstrap::initApp();
date_default_timezone_set('EEST');

function now() {
    return date("Y-m-d H:i:s");
}

$db = new Db();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET': {

    }

    case 'POST': {
        session_start();

        $requestBody = json_decode(file_get_contents("php://input"), true);

        if ($_SESSION["user_name"] == "" || !isset($_SESSION["user_name"])) {
            exit(json_encode(array("error" => "You are not authenticated")));
        }

        $connection = $db->getConnection();

        $user_id = getUser($_SESSION["user_name"], $connection);

        if ($user_id == -1) {
            exit(json_encode(array("error" => "Couldn't find this user in database")));
        }

        $user_folder = "../files/".$_SESSION['user_name'];

        if (!file_exists($user_folder)) {
            exit(json_encode(array("error" => "User doesn't have any uploaded files")));
        }

        $fileName = $requestBody['file_name'];

        if ($fileName == '' || !isset($fileName)) {
            exit(json_encode(array("error" => "No file name passed")));
        }

        if (!file_exists($file)) {
            exit(json_encode(array("error" => "Such a file doesn't exist")));
        }

        $md5 = md5_file($file);
        $file_id = getFileByHash($md5, $user_id, $connection);

        if ($file_id < 0) {
            exit(json_encode(array("error" => "File not found in database")));
        }

        $statement = $connection->prepare("SELECT * FROM shared WHERE file_id=:file_id AND user_id=:user_id");
        $result = $statement->execute(array("file_id" => $file_id, "user_id" => $user_id));

        if (!$result) {
            exit(json_encode(array("error" => "Something went wrong with the database")));
        }

        if (sizeof($statement->fetchAll()) != 0) {
            $statement = $connection->prepare("UPDATE shared SET time=:time WHERE file_id=:file_id AND user_id=:user_id");
            $result = $statement->execute(array("time" => now(), "file_id" => $file_id, "user_id" => $user_id));

            if (!$result) {
                exit(json_encode(array("error" => "Something went wrong with the database")));
            }
        } else {
            $statement = $connection->prepare("INSERT INTO shared (file_id, user_id, time) VALUES (:file_id, :user_id, :time)");
            $result = $statement->execute(array("file_id" => $file_id, "user_id" => $user_id, "time" => now()));

            if (!$result) {
                exit(json_encode(array("error" => "Something went wrong with the database")));
            }
        }

        exit(json_encode(array("md5" => $md5, "id" => $user_id)));
    }
}