<?php
  $variables = [
      'DATABASE_HOST' => '127.0.0.1',
      'DATABASE_USERNAME' => 'root',
      'DATABASE_PASSWORD' => 'bukalapak',
      'DATABASE_NAME' => 'disburse_development',
      'DATABASE_PORT' => '3306',
			'FLIP_API_HOST' => 'https://nextar.flip.id',
			'FLIP_BASIC_USERNAME' => 'HyzioY7LP6ZoO7nTYKbG8O4ISkyWnX1JvAEVAhtWKZumooCzqp41',
			'FLIP_BASIC_PASSWORD' => '',
  ];
  foreach ($variables as $key => $value) {
      putenv("$key=$value");
  }
?>