<?php
	namespace Model;

	class FlipDisbursement {
		private $id;
		private $transactions_id;
		private $external_disbursement_id;
		private $bank_code;
		private $account_number;
		private $remark;
		private $status;
		private $receipt;
		private $time_served;
		private $fee;
		private $created_at;
		private $updated_at;

		private $dbh;

		public function __construct($transactions_id, $external_disbursement_id, $bank_code, $account_number, $remark, $status, $receipt, $time_served, $fee) {
			$this->transactions_id					= $transactions_id;
			$this->external_disbursement_id	= $external_disbursement_id;
			$this->bank_code								= $bank_code;
			$this->account_number						= $account_number;
			$this->remark										= $remark;
			$this->status										= $status;
			$this->receipt									= $receipt;
			$this->time_served							= $time_served;
			$this->fee											= $fee;

			$this->dbh = \Lib\Database::getInstance();
		}

		public function __get($attr) {
			switch($attr) {
				case 'id':
					return $this->id;
				case 'transactions_id':
					return $this->transactions_id;
				case 'external_disbursement_id':
					return $this->external_disbursement_id;
				case 'bank_code':
					 return $this->bank_code;
				case 'account_number':
					return $this->account_number;
				case 'remark':
					return $this->remark;
				case 'status':
					return $this->status;
				case 'receipt':
					return $this->receipt;
				case 'time_served':
					return $this->time_served;
				case 'fee':
					return $this->fee;
				case 'created_at':
					return $this->created_at;
				case 'updated_at':
					return $this->updated_at;
				default:
					throw new Exception('Invalid attribute: '.$attr);
			}
		}

		public function create() {
			$this->created_at = date("Y-m-d H:i:s");
			$this->updated_at = $this->created_at;

			$statement = $this->dbh->prepare("INSERT INTO `flip_disbursements` ".
			"(`transactions_id`, `external_disbursement_id`, ".
			"`bank_code`, `account_number`, `remark`, `status`, ".
			"`receipt`, `time_served`, `fee`, `created_at`, `updated_at`) ".
			"VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$statement->execute([
				$this->transactions_id,
				$this->external_disbursement_id,
				$this->bank_code,
				$this->account_number,
				$this->remark,
				$this->status,
				$this->receipt,
				$this->time_served,
				$this->fee,
				$this->created_at,
				$this->updated_at]);

			$this->id = $this->dbh->lastInsertId();
			return $this->id;
		}
	}
?>