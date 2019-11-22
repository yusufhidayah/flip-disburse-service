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
				if (strtotime('0000-00-00 00:00:00') < 0) {
					$time_served = null;
				} else {
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
				echo "info: flip disbursement id: ".$json_response->id;
				break;
			case 'status':
				echo "check disburse status and update if any\n";
				$disburse_transaction_id = (int)$argv[2];

				$flipAPI = new FlipAPI();
				$get_data = $flipAPI->getDisbursement($disburse_transaction_id);
				var_dump($get_data);
				break;
			default:
				echo "unknown command!!!";
		}
?>