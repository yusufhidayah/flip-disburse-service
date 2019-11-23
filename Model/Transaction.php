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
				case 'amount':
					return $this->amount;
				case 'payment_method':
					return $this->payment_method;
				case 'created_at':
					return $this->created_at;
				default:
					throw new Exception('Invalid attribute: '.$attr);
			}
		}

		public function create() {
			$this->created_at = date("Y-m-d H:i:s");

			$statement = $this->dbh->prepare("INSERT INTO `transactions` (`amount`, `payment_method`, `created_at`) VALUES (?, ?, ?)");
			$statement->execute([$this->amount, $this->payment_method, $this->created_at]);
			$this->id = $this->dbh->lastInsertId();
			return $this->id;
		}
	}
?>