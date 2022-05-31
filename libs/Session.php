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
        $statement = $connection->prepare('SELECT * FROM users WHERE username=:username AND password=:password');
        $statement->execute(array("username"=> $username, "password" => $password));

        $result = $statement->fetchAll();

        if (sizeof($result) == 0) {
            return false;
        }

        $userId = 5;

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $username;

        return true;
    }

    public static function logout(): void {
        session_destroy();
    }

}