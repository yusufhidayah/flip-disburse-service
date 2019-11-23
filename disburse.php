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

			$transaction = new Model\Transaction($amount, "FLIP");
			$transaction->create();

			$data = array(
				"bank_code"=>(string)$bank_code,
				"account_number"=> (string)$account_number,
				"amount"=> (int)$amount,
				"remark"=> "transaction_id_".$transaction->id
			);
	
			$response = Lib\FlipAPI::createDisbursement($data);
			$json_response = json_decode($response);

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

			echo "success!\n";
			echo "info: you can check disbursement status using -> php disburse.php status ".$disbursement->id."\n";
			break;
		case 'status':
			echo "check disburse status and update it to our database\n";
			$flip_disbursements_id = (int)$argv[2];

			$disbursement = Model\FlipDisbursement::findById($flip_disbursements_id);
			if (!$disbursement) {
				echo "record not found, please try another disbursement id\n";
				return;
			}

			$response = Lib\FlipAPI::getDisbursement((int)$disbursement->external_disbursement_id);
			$json_response = json_decode($response);
			Model\FlipResponseLog::Log(
				$flip_disbursements_id,
				$json_response->id,
				"GET /disburse/".$json_response->id,
				$response
			);
				
			$data = array(
				"status" => $json_response->status,
				"receipt" => $json_response->receipt,
				"time_served" => $time_served
			);
			$result = $disbursement->update($data);
			if ($result) echo "successfully updated!\n"; else echo "update failed!\n";

			break;
		default:
			echo "unknown command!!!";
	}
?>