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

				$dbh = Lib\Database::getInstance();
				$dbh->beginTransaction();

				$transaction = new Model\Transaction($data['amount'], "FLIP");
				$transaction->create();

				$data['remark'] = "transaction_id_".$transaction->id;
				$flipAPI = new Lib\FlipAPI();
				$response = $flipAPI->createDisbursement($data);
				$json_response = json_decode($response);
				if (strtotime($json_response->time_served) > 0) {
					$time_served = $json_response->time_served;
				};

				$disbursement = Model\FlipDisbursement::create(
					$transaction->id,
					(int)$json_response->id,
					(string)$json_response->bank_code,
					(string)$json_response->account_number,
					(string)$json_response->remark,
					(string)$json_response->status,
					(string)$json_response->receipt,
					$time_served,
					(string)$json_response->fee
				);

				Model\FlipResponseLog::Log(
					$disbursement->id,
					$json_response->id,
					'POST /disburse',
					$response
				);

				$dbh->commit();
				echo "success!\n";
				echo "info: you can check disbursement status using -> php disburse.php status ".$disbursement->id."\n";
				break;
			case 'status':
				echo "check disburse status and update it to our database\n";
				$flip_disbursements_id = (int)$argv[2];
				$current_time = date("Y-m-d H:i:s");
				$time_served = null;

				$dbh = Lib\Database::getInstance();

				$stmt = $dbh->prepare("SELECT * FROM `flip_disbursements` WHERE id=?");
				$stmt->execute([$flip_disbursements_id]);
				$disbursement = $stmt->fetch();

				if (!$disbursement) {
					echo "record not found, please try another disbursement id";
					return;
				}

				$flipAPI	= new Lib\FlipAPI();
				$response = $flipAPI->getDisbursement((int)$disbursement['external_disbursement_id']);
				$json_response = json_decode($response);
				if (strtotime($json_response->time_served) > 0) {
					$time_served = $json_response->time_served;
				};

				Model\FlipResponseLog::Log(
					$flip_disbursements_id,
					$json_response->id,
					"GET /disburse/".$json_response->id,
					$response
				);

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
			case 'test':
				$disbursement = Model\FlipDisbursement::find_by_id(5);
				var_dump($disbursement);
				break;
			default:
				echo "unknown command!!!";
		}
?>