<?php

require_once "../libs/Bootstrap.php";
require_once "./utils/db_utils.php";

Bootstrap::initApp();
$db = new Db();


switch($_SERVER['REQUEST_METHOD']) {
    case 'GET': {
        session_start();

        //$requestBody = json_decode(file_get_contents("php://input"), true);

        if ($_SESSION["user_name"] == "" || !isset($_SESSION["user_name"])) {
            exit(json_encode(array("error" => "You are not authenticated")));
        }

        $fileName = $_GET['file_name'];//$requestBody['file_name'];
        $user_folder = "../files/".$_SESSION["user_name"]."/";
        $whole_file_name = $user_folder.$fileName;

        if ($fileName == '' || !isset($fileName)) {
            exit(json_encode(array("error" => "No file name passed")));
        }

        $connection = $db->getConnection();

        $user_id = getUser($_SESSION["user_name"], $connection);

        if ($user_id == -1) {
            exit(json_encode(array("error" => "No such user in database?")));
        }

        $file = getFileByNameAndUser($fileName, $user_id, $connection);

        if (sizeof($file) != 0) {
            $size = filesize($whole_file_name);
            $hash = $file['hash'];
            $json_file_name = "${user_folder}${fileName}.json";

            $json_result = json_encode(array("name" => $fileName, "hash" => $hash, 
                                             "user" => $_SESSION["user_name"], "size" => $size));

            file_put_contents($json_file_name, $json_result);

            header("Cache-Control: private");
            header("Content-Type: application/stream");
            header("Content-Length: ".$size);
            header("Content-Disposition: attachment; filename="."${fileName}-json");

            readfile ($json_file_name);

            if (!unlink($json_file_name)) {
                exit(json_encode(array("error" => "Failed to delete tmp json file")));
            }
        } else {
            exit(json_encode(array("error" => "No such file found in database")));
        }
    }
}