<?php

session_start();

require_once '../libs/Bootstrap.php';
Bootstrap::initApp();

$db = new Db();

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET': {

        try {
            $userInfo = Session::verifyUserIsLogged();
            $result = [
                'logged' => true,
                'session' => $userInfo,
            ];
        } catch (AccessDeniedException $e) {
            $result = ['logged' => false];
        }

        echo json_encode($result);
        break;
    }
    case 'POST': {
        // login

        $requestBody = json_decode(file_get_contents("php://input"), true);

        $username = $requestBody['username'];
        $password = $requestBody['password'];

        echo json_encode(["success" => Session::logUser($username, $password, $db->getConnection())]);

        break;
    }
    case 'DELETE': {

        Session::logout();
        echo json_encode(["success" => true]);
    }

}