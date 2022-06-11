<?php

require_once '../libs/Bootstrap.php';
Bootstrap::initApp();

switch($_SERVER['REQUEST_METHOD']) {
    case 'GET': {
        session_start();
        $user_folder = "../files/".$_SESSION['user_name'];

        $json_result = array();
        $all_files = array();

        if (!file_exists($user_folder)) {
            $json_result['files'] = null;
            exit(json_encode($json_result));
        }
        
        $user_folder = $user_folder."/*";

        foreach(array_filter(glob($user_folder), 'is_file') as $file) {
            array_push($all_files, $file);
        }

        $json_result['files'] = $all_files;
        exit(json_encode($json_result));
    }
}