<?php
	namespace Disburse;

	class Autoloader {
		public function load($className) {
			$root_directory = chop(__DIR__, "/Lib");
			$file = $root_directory . "/" . str_replace("\\", "/", $className) . '.php';
			if (file_exists($file)) {
					require $file;
			} else {
					return false;
			}
		}

		public function register() {
			spl_autoload_register([$this, 'load']);
		}
	}

	$loader = new Autoloader();
	$loader->register();
?>