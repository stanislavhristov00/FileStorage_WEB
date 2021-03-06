<?php

class Session {

    /**
     * @throws AccessDeniedException when the current user is not logged
     * @return logged user info
     */
    public static function verifyUserIsLogged(): array {

        $logged = isset($_SESSION['user_id']);

        if (!$logged) {
            throw new AccessDeniedException();
        }

        return $_SESSION;
    }

    public static function logUser(string $username, string $password, PDO $connection): bool {
        $statement = $connection->prepare('SELECT * FROM users WHERE username=:username AND h_password=:h_password');

        $hashed_password = sha1($password);
        $statement->execute(array("username"=> $username, "h_password" => $hashed_password));

        $result = $statement->fetchAll();

        if (sizeof($result) == 0) {
            return false;
        }

        $_SESSION['user_id'] = uniqid();
        $_SESSION['user_name'] = $username;

        return true;
    }

    public static function logout(): void {
        session_destroy();
    }

}