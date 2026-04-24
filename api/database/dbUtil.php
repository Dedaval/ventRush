<?php
include 'dbConnectionInfo.php';

function getDb() : PDO {
	static $db = NULL;
	if ($db == NULL) {
		$db = createConnection(DB_NAME, DB_USER, DB_PASSWORD);
	}
	return $db;
}
function createConnection($dbname, $username, $passwd) {
	try {
		return new PDO("mysql:host=" . DB_HOST . ";dbname=" . $dbname, $username, $passwd, array (
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
				PDO::ATTR_PERSISTENT => true,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_EMULATE_PREPARES => false,
		        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		));
	} catch (PDOException $e) {
		echo "DB connection error, see logs.";
		var_dump($e);
		error_log($e->getMessage());
	}
}