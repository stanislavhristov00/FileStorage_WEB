<?php

switch($_SERVER['REQUEST_METHOD']) {
    case 'GET': {
        session_start();

        if ($_SESSION["user_name"] == "" || !isset($_SESSION["user_name"])) {
            exit(json_encode(array("error" => "You are not authenticated")));
        }

        if (!isset($_GET['file_name'])) {
            exit(json_encode(array("error" => "No file name passed")));
        }

        $user_folder = "/files/".$_SESSION['user_name'];
        $user_folder = $_SERVER["DOCUMENT_ROOT"].$user_folder;

        $whole_file_name = $user_folder."/".$_GET['file_name'];

        if(file_exists($whole_file_name)) {
            $fileSize = filesize($whole_file_name);
            
            header("Cache-Control: private");
            header("Content-Type: application/stream");
            header("Content-Length: ".$fileSize);
            header("Content-Disposition: attachment; filename=".$_GET['file_name']);

            readfile ($whole_file_name);                   
            exit();
        }
        else {
            die('The provided file path is not valid.');
        }
    }
}