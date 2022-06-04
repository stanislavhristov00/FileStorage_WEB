<?php

require_once '../libs/Bootstrap.php';
Bootstrap::initApp();

$db = new Db();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST': {
        $requestBody = json_decode(file_get_contents("php://input"), true);

        if (!isset($requestBody['username']) || !isset($requestBody['password']) || !isset($requestBody['email'])) {
            exit(json_encode(array('status' => false, 'message' => "username, password and email should be set")));
        }

        $username = $requestBody['username'];
        $password = $requestBody['password'];
        $email = $requestBody['email'];

        $hashed_password = sha1($password);

        $json_result = array('status' => true);
        $connection = $db->getConnection();

        $statement = $connection->prepare('SELECT * FROM users WHERE email=:email');
        $statement->execute(array("email" => $email));
        $result = $statement->fetchAll();

        if (sizeof($result) != 0){
            $json_result['status'] = false;
            $json_result['message'] = "Този email вече е зает!";

            exit(json_encode($json_result));
        }
        
        
        $statement = $connection->prepare('SELECT * FROM users WHERE username=:username');
        $statement->execute(array("username"=> $username));

        $result = $statement->fetchAll();


        if (sizeof($result) != 0) {
            $json_result['status'] = false;
            $json_result['message'] = "Това потребителско име вече е заето";

            exit(json_encode($json_result));
        }
    

    
        $statement = $connection->prepare('INSERT INTO users (username, h_password, email) VALUES (:username, :h_password, :email)');
        $status = $statement->execute(array("username" => $username, "h_password" => $hashed_password, "email" => $email));

        if ($status) {
            $json_result['status'] = true;
            $json_result['message'] = "Регистрирахте се успешно!";
        } else {
            $json_result['status'] = false;
            $json_result['message'] = "Нещо се обърка :(";
        }

        exit(json_encode($json_result));
    }
    
}