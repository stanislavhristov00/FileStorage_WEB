<?php

require_once '../libs/Bootstrap.php';
Bootstrap::initApp();

$db = new Db();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST': {
        $requestBody = json_decode(file_get_contents("php://input"), true);

        $username = $requestBody['username'];
        $password = $requestBody['password'];
        $email = $requestBody['email'];

        $hashed_password = sha1($password);

        $connection = $db->getConnection();
        
        $statement = $connection->prepare('SELECT * FROM users WHERE username=:username AND h_password=:h_password AND email=:email');
        $statement->execute(array("username"=> $username, "h_password" => $hashed_password, "email" => $email));

        $result = $statement->fetchAll();
        $json_result = array('status' => true);


        if (sizeof($result) != 0) {
            // $json_result = [
            //     'status' => false,
            //     'message' => "User already exists"
            // ];
            $json_result['status'] = false;
            $json_result['message'] = "User already exists";
        }

        if ($json_result['status'] === true) {
            $statement = $connection->prepare('INSERT INTO users (username, h_password, email) VALUES (:username, :h_password, :email)');
            $status = $statement->execute(array("username" => $username, "h_password" => $hashed_password, "email" => $email));
    
            if ($status) {
                $json_result['status'] = true;
                $json_result['message'] = "You have registered successfully!";
    
                // $json_result = [
                //     'status' => true,
                //     'message' => "Opa"
                // ];
            } else {
                $json_result['status'] = false;
                $json_result['message'] = "Something went wrong :(";
                // $json_result = [
                //     'status' => false,
                //     'message' => "Shopa"
                // ];
            }
        }

        echo json_encode($json_result);
    }
    
}