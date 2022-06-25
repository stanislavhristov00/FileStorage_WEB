<?php

require_once '../libs/Bootstrap.php';
require_once './utils/db_utils.php';

Bootstrap::initApp();
date_default_timezone_set('UTC');

$MAX_TIME_FOR_SHARED_FILE = 60 * 60 * 2; // 2 hours.

function now() {
    return date("Y-m-d H:i:s");
}

function getFileType(string $fileName): string {
    $res = explode(".", $fileName);
    if (sizeof($res) == 1) {
        return "File";
    }

    $ext = $res[sizeof($res) - 1];

    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg" || $ext == "gif" ||
        $ext == "jif" || $ext == "svg" || $ext == "bmp") {
            return "Image";
        }

    if ($ext == "pdf") {
        return "PDF";
    }

    if ($ext == "docx" || $ext == "docm" || $ext == "dot" || $ext == "dotx") {
        return "Word File";
    }

    if ($ext == "xlsx" || $ext == "xlsm" || $ext == "xslb" || $ext == "xltx") {
        return "Excel File";
    }

    if ($ext == "pptx" || $ext == "pptm" || $ext == "ppt") {
        return "PowerPoint File";
    }

    if ($ext == "json" || $ext == "JSON") {
        return "JSON";
    }

    if ($ext == "txt") {
        return "Text File";
    }
}

$db = new Db();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET': {
        $connection = $db->getConnection();

        if (!isset($_GET['hash']) || !isset($_GET['id'])) {
            echo "
            <html>
                <head>
                    <link rel=\"stylesheet\" href=\"/./styles/shared.css\"/>
                    <script src=\"/./scripts/share.js\" defer></script>
                    <title>uCloud</title>
                </head>
                <body>
                    <h1>Невалиден линк :(</h1>
                </body>
            </html>";
            exit();
        }
        
        $md5 = $_GET['hash'];
        $user_id = $_GET['id'];

        $file_id = getFileByHash($md5, $user_id, $connection);

        if ($file_id == -2) {
            echo "
            <html>
                <head>
                    <link rel=\"stylesheet\" href=\"/./styles/shared.css\"/>
                    <script src=\"/./scripts/share.js\" defer></script>
                    <title>uCloud</title>
                </head>
                <body>
                    <h1>Няма такъв файл.</h1>
                </body>
            </html>";
            exit();
        }

        $statement = $connection->prepare("SELECT time FROM shared WHERE file_id=:file_id AND user_id=:user_id");
        $result = $statement->execute(array("file_id" => $file_id, "user_id" => $user_id));

        if (!$result) {
            echo "
            <html>
                <head>
                    <link rel=\"stylesheet\" href=\"/./styles/shared.css\"/>
                    <script src=\"/./scripts/share.js\" defer></script>
                    <title>uCloud</title>
                </head>
                <body>
                    <h1>Възникна проблем с базата данни. Свържете се с администратор.</h1>
                </body>
            </html>";
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
                $path = "$root/files/$user_name/$file_name";
                
                $file_size = filesize($path);
                $type = getFileType($file_name);

                echo "
                    <html>
                        <head>
                            <link rel=\"stylesheet\" href=\"/./styles/shared.css\"/>
                            <script src=\"/./scripts/share.js\" defer></script>
                            <title>uCloud</title>
                        </head>
                        <body>
                            <div>
                                <div id=\"text-spans\">
                                    <span class=\"text\"id=\"name\">File name: ${file_name}</span>
                                    <span class=\"text\"id=\"file_size\">File size: ${file_size}B</span>
                                    <span class=\"text\">File Type: ${type}</span>
                                    <span class=\"text\">Shared by: ${user_name}</span>
                                </div>
                                <div id=\"buttons\">
                                    <a href=\"/./endpoints/share_download.php?hash=${md5}&id=${user_id}\" target=\"_blank\"><span id=\"download\">Изтегли</span></a>
                                    <span id=\"show\">Покажи</span>
                                </div>
                                <div id=\"frame\">
                                    <iframe id=\"actual-frame\" src=\"/./files/$user_name/$file_name\"></iframe>
                                </div>
                            </div>
                        </body>
                    </html>
                ";
                exit();
            } else {
                echo "
            <html>
                <head>
                    <link rel=\"stylesheet\" href=\"/./styles/shared.css\"/>
                    <script src=\"/./scripts/share.js\" defer></script>
                    <title>uCloud</title>
                </head>
                <body>
                    <h1>Нямате достъп до този файл</h1>
                </body>
            </html>";
                exit();
            }
        } else {
            echo "
            <html>
                <head>
                    <link rel=\"stylesheet\" href=\"/./styles/shared.css\"/>
                    <script src=\"/./scripts/share.js\" defer></script>
                    <title>uCloud</title>
                </head>
                <body>
                    <h1>Нямате достъп до този файл</h1>
                </body>
            </html>";
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