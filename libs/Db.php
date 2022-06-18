<?php

class Db {

    private PDO $connection;

    public function __construct() {

        $config = parse_ini_file('config.ini', true);

        $dbhost = $config['db']['host'];
        $dbName = $config['db']['database'];
        $userName = $config['db']['username'];
        $userPassword = $config['db']['password'];

        $this->connection = new PDO("mysql:host=$dbhost;dbname=$dbName", $userName, $userPassword,
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
    }

    public function getConnection() {
        return $this->connection;
    }
}