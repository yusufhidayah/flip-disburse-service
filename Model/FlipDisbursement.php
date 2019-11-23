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

		private $db_connection;

		public function __construct() {
			$this->db_connection = \Lib\Database::getInstance();
		}

		public static function create($transactions_id, $external_disbursement_id, $bank_code, $account_number, $remark, $status, $receipt, $time_served, $fee) {
			$current_time = date("Y-m-d H:i:s");
			$data = array(
				"transactions_id" => $transactions_id,
				"external_disbursement_id" => $external_disbursement_id,
				"bank_code" => $bank_code,
				"account_number" => $account_number,
				"remark" => $remark,
				"status" => $status,
				"receipt" => $receipt,
				"time_served" => $time_served,
				"fee" => $fee,
				"created_at" => $current_time,
				"updated_at" => $current_time
			);

			$instance = new self();
			$instance->fillAttribute($data);
			$instance->insertToDatabase();

			return $instance;
		}

		public static function findById($inputId) {
			$instance = new self();
			$id = (int)$inputId;
			$result = $instance->selectFromDatabaseById($id);
			if ($result) {
				$instance->fillAttribute($result);
			} else {
				$instance = null;
			}
			
			return $instance;
		}

		public function update($data){
			if ($this->status == $data['status']){ return false; };

			$this->status				= $data['status'];
			$this->receipt			= $data['receipt'];
			$this->time_served	= $this->adjusted_time_served($data['time_served']);
			$this->updated_at		=	date("Y-m-d H:i:s");

			$statement = $this->db_connection->prepare("UPDATE `flip_disbursements` ".
				"SET `status`=?, `receipt`=?, `time_served`=?, `updated_at`=? WHERE `id`=?");
			$result = $statement->execute([
				$this->status,
				$this->receipt,
				$this->time_served,
				$this->updated_at,
				$this->id
			]);

			return $result;
		}

		protected function selectFromDatabaseById($id) {
			$stmt = $this->db_connection->prepare("SELECT * FROM `flip_disbursements` WHERE id=?");
			$stmt->execute([$id]);
			return $stmt->fetch();
		}

		protected function fillAttribute($data) {
			$this->id												= $data['id'];
			$this->transactions_id					= $data['transactions_id'];
			$this->external_disbursement_id = $data['external_disbursement_id'];
			$this->bank_code								= $data['bank_code'];
			$this->account_number						= $data['account_number'];
			$this->remark										= $data['remark'];
			$this->status										= $data['status'];
			$this->receipt									= $data['receipt'];
			$this->time_served							= $this->adjusted_time_served($data['time_served']);
			$this->fee											= $data['fee'];
			$this->created_at								= $data['created_at'];
			$this->updated_at								= $data['updated_at'];
		}

		protected function insertToDatabase() {
			$statement = $this->db_connection->prepare("INSERT INTO `flip_disbursements` ".
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

			$this->id = $this->db_connection->lastInsertId();
			return $this->id;
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
					throw new \Exception('Invalid attribute: '.$attr);
			}
		}

		private function adjusted_time_served($time_served) {
			return (strtotime($json_response->time_served) > 0) ? $time_served : null;
		}
	}
?>