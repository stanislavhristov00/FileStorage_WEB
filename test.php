<?php

require('./libs/Db.php');

$db = new Db();
$connection = $db->getConnection();
$statement = $connection->query('SELECT * FROM users');

$statement->execute();
$result = $statement->fetchAll();

echo json_encode($result, JSON_UNESCAPED_UNICODE);



