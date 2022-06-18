<?php

require_once '../libs/Bootstrap.php';
require_once './utils/db_utils.php';
Bootstrap::initApp();

$db = new Db();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST': {
        session_start();

        $connection = $db->getConnection();

        if ($_SESSION["user_name"] == "" || !isset($_SESSION["user_name"])) {
            exit(json_encode(array("error" => "You are not authenticated")));
        }

        $user_id = getUser($_SESSION["user_name"], $connection);
        if ($user_id == -1) {
            exit(json_encode(array("error" => "No such user found in database")));
        }

        $user_folder = "../files/".$_SESSION['user_name'];

        if (!file_exists($user_folder)) {
            mkdir($user_folder);
        }

        if (!isset($_FILES['file'])) {
            header("Location: ../index.html?status=failed");
            exit();
        }

        $file_name = $_FILES['file']['name'];
        $file_size = $_FILES['file']['size'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_type= $_FILES['file']['type'];
        // $file_ext = strtolower(end(explode('.',$_FILES['file']['name'])));

        if ($file_name == "") {
            header("Location: ../index.html?status=empty");
            exit();
        }

        $new_location = "$user_folder/$file_name";
        if (file_exists($new_location)) {
            header("Location: ../index.html?status=alreadyExists");
            exit();
        }
        
        $md5 = md5_file($file_tmp);
        $name = checkIfFileExists($md5, $user_id, $connection);
        
        if ($name != "") {
            header("Location: ../index.html?status=hashExists-$name");
            exit();
        }

        if(addFile($md5, $user_id, $file_name, $connection)) {
            move_uploaded_file($file_tmp, "$user_folder/$file_name");
            header("Location: ../index.html?status=success");
            exit();
        } else {
            header("Location: ../index.html?status=databaseErr");
            exit();
        }

    }
}