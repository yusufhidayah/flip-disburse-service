<?php
	namespace Lib;

	class Database {
		static protected $instance = null;

    protected $connection = null;
    protected function __construct() {
				$dsn = "mysql:host=".env('DATABASE_HOST').";dbname=".env('DATABASE_NAME');
				$this->connection = new \PDO($dsn, env('DATABASE_USERNAME'), env('DATABASE_PASSWORD'));
				$this->connection->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
    }

    public function getConnection()
    {
        return $this->connection;
    }

    static public function getInstance()
    {
        if (!(static::$instance instanceof static)) {
            static::$instance = new static();
        }

        return static::$instance->getConnection();
    }
	}
?>