<?php
	require "Lib/Envloader.php";
	require "Lib/Autoloader.php";

	// echo "".env("FLIP_API_HOST").env("FLIP_API_HOST");
	
	// $query = Database::getInstance()->prepare("SHOW TABLES;");
	// $query->execute();
	// foreach($query as $row) {
	// 	print_r($row);
	// }

	$google = new FlipAPI();
	$get_data = $google->getDisbursement(5535152564);
	var_dump($get_data)
?>