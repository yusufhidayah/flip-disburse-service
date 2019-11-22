#!/usr/local/bin/php
<?php
		require "Lib/Envloader.php";
		require "Lib/Autoloader.php";

		$command = (string)$argv[1];
		switch($command){
			case 'create':
				echo "create transaction data and disburse data...";
				$amount					= (int)$argv[2];
				$bank_code			= (string)$argv[3];
				$account_number = (string)$argv[4];
				$current_time		= date("Y-m-d H:i:s");
				$payment_method	=	'FLIP';
				$time_served		= null;

				$data = array(
					"bank_code"=>(string)$bank_code,
					"account_number"=> (string)$account_number,
					"amount"=> (int)$amount,
					"remark"=> ""
				);

				$dbh = Database::getInstance();
				$dbh->beginTransaction();

				$statement = $dbh->prepare("INSERT INTO `transactions` (`amount`, `payment_method`, `created_at`) VALUES (?, ?, ?)");
				if (!$statement->execute([$data['amount'], $payment_method, $current_time])) $dbh->rollback();
				$last_transaction_id = $dbh->lastInsertId();

				$data['remark'] = "transaction_id_".$last_transaction_id;
				$flipAPI = new FlipAPI();
				$response = $flipAPI->createDisbursement($data);
				$json_response = json_decode($response);
				if (strtotime($json_response->time_served) > 0) {
					$time_served = $json_response->time_served;
				};

				$statement = $dbh->prepare("INSERT INTO `flip_disbursements` ".
					"(`transactions_id`, `external_disbursement_id`, ".
					"`bank_code`, `account_number`, `remark`, `status`, ".
					"`receipt`, `time_served`, `fee`, `created_at`, `updated_at`) ".
					"VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				if (!$statement->execute([
					$last_transaction_id,
					$json_response->id,
					$json_response->bank_code,
					$json_response->account_number,
					$json_response->remark,
					$json_response->status,
					$json_response->receipt,
					$time_served,
					$json_response->fee,
					$current_time,
					$current_time])) $dbh->rollback();
				$last_disbursement_id = $dbh->lastInsertId();

				$statement = $dbh->prepare("INSERT INTO `flip_response_logs` ".
					"(`disbursements_id`, `external_disbursement_id`, ".
					"`request_path`, `response`, `created_at`) ".
					"VALUES (?, ?, ?, ?, ?)");

				if (!$statement->execute([
					$last_disbursement_id,
					$json_response->id,
					'POST /disburse',
					$response,
					$current_time])) $dbh->rollback();

				$dbh->commit();
				echo "success!\n";
				echo "info: you can check disbursement status using -> php disburse.php status ".$last_disbursement_id."\n";
				break;
			case 'status':
				echo "check disburse status and update it to our database\n";
				$flip_disbursements_id = (int)$argv[2];
				$current_time = date("Y-m-d H:i:s");
				$time_served = null;

				$dbh = Database::getInstance();

				$stmt = $dbh->prepare("SELECT * FROM `flip_disbursements` WHERE id=?");
				$stmt->execute([$flip_disbursements_id]);
				$disbursement = $stmt->fetch();

				if (!$disbursement) {
					echo "record not found, please try another disbursement id";
					return;
				}

				$flipAPI	= new FlipAPI();
				$response = $flipAPI->getDisbursement((int)$disbursement['external_disbursement_id']);
				$json_response = json_decode($response);
				if (strtotime($json_response->time_served) > 0) {
					$time_served = $json_response->time_served;
				};

				$statement = $dbh->prepare("INSERT INTO `flip_response_logs` ".
					"(`disbursements_id`, `external_disbursement_id`, ".
					"`request_path`, `response`, `created_at`) ".
					"VALUES (?, ?, ?, ?, ?)");

				if (!$statement->execute([
					$flip_disbursements_id,
					$json_response->id,
					"GET /disburse/".$json_response->id,
					$response,
					$current_time])) $dbh->rollback();

				$status_changed = $disbursement['status'] != $json_response->status;

				if ($status_changed) {
					echo "status changed, save it to database... ";

					$dbh->beginTransaction();

					$statement = $dbh->prepare("UPDATE `flip_disbursements` ".
						"SET `status`=?, `receipt`=?, `time_served`=?, `updated_at`=? WHERE `id`=?");
					if (!$statement->execute([
						$json_response->status,
						$json_response->receipt,
						$time_served,
						$current_time,
						$flip_disbursements_id])) $dbh->rollback();

						$dbh->commit();
						echo "successfully updated!\n";
				}
				break;
			default:
				echo "unknown command!!!";
		}
?>