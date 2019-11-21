<?php
	require "Lib/Envloader.php";

	echo ("Execute Database Migration\n");

	$dsn = "mysql:host=".env('DATABASE_HOST');
	$db_connection = new \PDO($dsn, env('DATABASE_USERNAME'), env('DATABASE_PASSWORD'));
	$db_connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	echo ("Create ".env("DATABASE_NAME")." Database...");
	try {
		$sql = "CREATE DATABASE `".env("DATABASE_NAME")."`;";
		$db_connection->exec($sql);
		print("created\n");
	} catch(PDOException $e) {
		echo $e->getMessage()."\n";
	}

	echo ("Create transaction table...");
	try {
		$sql = "CREATE table transaction(
		id INT(20) AUTO_INCREMENT PRIMARY KEY,
		amount INT(11) NOT NULL,
		flip_disburse_id INT(20) NULL, 
		created_at DATETIME(6) NOT NULL);" ;
		$db_connection->exec("USE ".env("DATABASE_NAME").";");
		$db_connection->exec($sql);
		print("created\n");
	} catch(PDOException $e) {
		echo $e->getMessage()."\n";
	}

	echo ("Create disburse table...");
	try {
		$sql = "CREATE table disburse(
		id INT(20) AUTO_INCREMENT PRIMARY KEY,
		disburse_transaction_id INT(20) NOT NULL,
		bank_code VARCHAR(50) NOT NULL,
		account_number VARCHAR(50) NOT NULL,
		remark VARCHAR(50) NULL,
		status VARCHAR(10) NOT NULL,
		receipt VARCHAR(250) NULL,
		time_served DATETIME(6) NOT NULL,
		fee INT(11) NOT NULL,
		created_at DATETIME(6) NOT NULL,
		updated_at DATETIME(6) NOT NULL);";
		$db_connection->exec("USE ".env("DATABASE_NAME").";");
		$db_connection->exec($sql);
		print("created\n");
	} catch(PDOException $e) {
		echo $e->getMessage()."\n";
	}

	echo ("Create flip_response_log table...");
	try {
		$sql = "CREATE table flip_response_log(
		id INT(20) AUTO_INCREMENT PRIMARY KEY,
		disburse_id INT(20) NOT NULL,
		disburse_transaction_id INT(20) NOT NULL,
		request_type VARCHAR(50) NOT NULL,
		response TEXT NULL,
		created_at DATETIME(6) NOT NULL);" ;
		$db_connection->exec("USE ".env("DATABASE_NAME").";");
		$db_connection->exec($sql);
		print("created\n");
	} catch(PDOException $e) {
		echo $e->getMessage()."\n";
	}

	echo "Migration Done"
?>