<?php

require_once '../libs/Bootstrap.php';
Bootstrap::initApp();

$db = new Db();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST': {
        session_start();

        $json_result = array();
        $user_folder = "../files/".$_SESSION['user_name'];

        if (!file_exists($user_folder)) {
            mkdir($user_folder);
        }

        if (!isset($_FILES['file'])) {
            $json_result['status'] = false;
            $json_result['message'] = "Няма качен файл";

            exit(json_encode($json_result));
        }

        $file_name = $_FILES['file']['name'];
        $file_size = $_FILES['file']['size'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_type= $_FILES['file']['type'];
        $file_ext = strtolower(end(explode('.',$_FILES['file']['name'])));

        move_uploaded_file($file_tmp, "$user_folder/$file_name");

        $json_result['status'] = true;
        $json_result['message'] = "Качихте ".$file_name." успешно!";

        exit(json_encode($json_result));
    }
    
}