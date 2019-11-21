<?php
	require "Lib/Envloader.php";
	require "Lib/Autoloader.php";

	echo "".env("FLIP_API_HOST").env("FLIP_API_HOST");
	
	$query = Database::getInstance()->prepare("SHOW TABLES;");
	$query->execute();
	foreach($query as $row) {
		print_r($row);
	}
?>