<?php

require_once '../libs/Bootstrap.php';
require_once './utils/db_utils.php';

Bootstrap::initApp();
date_default_timezone_set('UTC');

$MAX_TIME_FOR_SHARED_FILE = 60 * 60 * 2; // 2 hours.

function now() {
    return date("Y-m-d H:i:s");
}

$db = new Db();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET': {
        $connection = $db->getConnection();

        if (!isset($_GET['hash']) || !isset($_GET['id'])) {
            echo "<h1>Трябва и двата</h1>";
            exit();
        }
        
        $md5 = $_GET['hash'];
        $user_id = $_GET['id'];

        $file_id = getFileByHash($md5, $user_id, $connection);

        if ($file_id == -2) {
            echo "<h1>Няма такъв файл</h1>";
            exit();
        }

        $statement = $connection->prepare("SELECT time FROM shared WHERE file_id=:file_id AND user_id=:user_id");
        $result = $statement->execute(array("file_id" => $file_id, "user_id" => $user_id));

        if (!$result) {
            echo "<h1>Sorry pich, bazata bastisa</h1>";
            exit();
        }

        $arr = $statement->fetchAll();

        if (sizeof($arr) != 0) {
            $curr_time = strtotime(now());
            $timestamp = strtotime($arr[0]['time']);

            
            if ($curr_time - $timestamp < $MAX_TIME_FOR_SHARED_FILE) {
                $file = getFileById($file_id, $user_id, $connection);
                
                $file_name = $file['name'];
                $user_name = getUsernameFromId($user_id, $connection);
                $root = $_SERVER["DOCUMENT_ROOT"];
                
                $file_size = filesize("$root/files/$user_name/$file_name");

                echo "
                    <html>
                        <head>
                            <link rel=\"stylesheet\" href=\"/./styles/shared.css\"/>
                            <script src=\"/./scripts/share.js\" defer></script>
                        </head>
                        <body>
                            <div>
                                <h1>NAME: $file_name</h1>
                                <h1>File size: ${file_size}B<h1>
                                <span class=\"openpop\" id=\"openpop-span\">Виж</span>
                                <span><a href=\"download.php?file_name=${file_name}\" target=\"_blank\">Изтегли</a></span>
                                <span id=\"delete\" class=\"openpop\">Изтрий</span>
                                <span id=\"share\" class=\"openpop\">Сподели</span>
                            </div>
                        </body>
                    </html>
                ";
                exit();
            } else {
                echo "<h1>Sorry bro</h1>";
                exit();
            }
        } else {
            echo "<h1>Nqma takuw file brato</h1>";
            exit();
        }

        break;
        
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

        $file = $user_folder.'/'.$fileName;

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