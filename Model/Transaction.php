<?php
	namespace Model;

	class Transaction {
		private $id;
		private $amount;
		private $payment_method;
		private $created_at;

		private $dbh;

		public function __construct($amount, $payment_method) {
			$this->amount					= $amount;
			$this->payment_method = $payment_method;

			$this->dbh = \Lib\Database::getInstance();
		}

		public function __get($attr) {
			switch($attr) {
				case 'id':
					return $this->id;
				default:
					throw new Exception('Invalid attribute: '.$attr);
			}
		}

		public function create() {
			$statement = $this->dbh->prepare("INSERT INTO `transactions` (`amount`, `payment_method`, `created_at`) VALUES (?, ?, CURTIME())");
			$statement->execute([$this->amount, $this->payment_method]);
			$this->id = $this->dbh->lastInsertId();
			return $this->id;
		}
	}
?>