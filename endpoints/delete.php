<?php

    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST': {
            session_start();
    
            $requestBody = json_decode(file_get_contents("php://input"), true);

            if ($_SESSION["user_name"] == "" || !isset($_SESSION["user_name"])) {
                exit(json_encode(array("error" => "You are not authenticated")));
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

            if (!unlink($file)) {
                exit(json_encode(array("error" => "Failed to delete $file")));
            }

            exit(json_encode(array("status" => true)));

        }
    }