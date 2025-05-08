<?php
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "listasivu";
    $charset= 'utf8mb4';
	// Create connection
	$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
	?>