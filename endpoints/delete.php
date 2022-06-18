<?php

require_once '../libs/Bootstrap.php';
require_once './utils/db_utils.php';
Bootstrap::initApp();

$db = new Db();

switch($_SERVER['REQUEST_METHOD']) {
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

        $file = $user_folder.'/'.$fileName;

        if (!file_exists($file)) {
            exit(json_encode(array("error" => "Such a file doesn't exist")));
        }

        $md5 = md5_file($file);
        if (checkIfFileExists($md5, $user_id, $connection) != "") {
            if (!deleteFile($md5, $user_id, $connection)) {
                exit(json_encode(array("error" => "Failed to delete file from database")));
            }
        } else {
            exit(json_encode(array("error" => "Can't find this file in database?")));
        }

        if (!unlink($file)) {
            exit(json_encode(array("error" => "Failed to delete $file")));
        }

        exit(json_encode(array("status" => true)));
    }
}