<?php

require_once "../libs/Bootstrap.php";
require_once "./utils/db_utils.php";

Bootstrap::initApp();
$db = new Db();

$MAX_TIME_FOR_SHARED_FILE = 60 * 60 * 2; // 2 hours.

function now() {
    return date("Y-m-d H:i:s");
}

switch($_SERVER['REQUEST_METHOD']) {
    case 'GET': {
        if (!isset($_GET['hash']) || !isset($_GET['id'])) {
            exit(json_encode(array("error" => "Both hash and id must be set")));
        }

        $connection = $db->getConnection();
        
        $username = getUsernameFromId(intval($_GET['id']), $connection);

        if ($username === "") {
            exit(json_encode(array("error" => "Some database error occured on username retrieval. Contact admins")));
        }

        $file_id = getFileByHash($_GET['hash'], intval($_GET['id']), $connection);

        if ($file_id === -2) {
            exit(json_encode(array("error" => "No such file found")));
        }

        if ($file_id === -1) {
            exit(json_encode(array("error" => "Some database error occured. Contact admins")));
        }

        $statement = $connection->prepare("SELECT time FROM shared WHERE file_id=:file_id AND user_id=:user_id");
        $result = $statement->execute(array("file_id" => $file_id, "user_id" => $_GET['id']));

        if (!$result) {
            exit(json_encode(array("error" => "Database error")));
        }

        $arr = $statement->fetchAll();

        if (sizeof($arr) == 0) {
            exit(json_encode(array("error" => "There is no such file in database")));
        } else {
            $curr_time = strtotime(now());
            $timestamp = strtotime($arr[0]['time']);

            if ($curr_time - $timestamp < $MAX_TIME_FOR_SHARED_FILE) {
                exit(json_encode(array("error" => "This link has expired")));
            }
        }

        $file = getFileById($file_id, intval($_GET['id']), $connection);

        if (sizeof($file) == 0) {
            exit(json_encode(array("error" => "Some database error occured on file retrieval. Contact admins")));
        }


        $user_folder = "/files/".$username;
        $user_folder = $_SERVER["DOCUMENT_ROOT"].$user_folder;

        $whole_file_name = $user_folder."/".$file['name'];

        if(file_exists($whole_file_name)) {
            $fileSize = filesize($whole_file_name);
            
            header("Cache-Control: private");
            header("Content-Type: application/stream");
            header("Content-Length: ".$fileSize);
            header("Content-Disposition: attachment; filename=".$file['name']);

            readfile ($whole_file_name);                   
            exit();
        } else {
            exit(json_encode(array("error" => "File not found locally")));
        }
    }
}