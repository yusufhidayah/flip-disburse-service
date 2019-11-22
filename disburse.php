#!/usr/local/bin/php
<?php
		require "Lib/Envloader.php";
		require "Lib/Autoloader.php";

		$command = (string)$argv[1];
		switch($command){
			case 'create':
				echo "create transaction data and disburse data\n";
				$amount					= (int)$argv[2];
				$bank_code			= (string)$argv[3];
				$account_number = (string)$argv[4];
				
				$data = array(
					"bank_code"=>(string)$bank_code,
					"account_number"=> (string)$account_number,
					"amount"=> (int)$amount,
					"remark"=> "remarkable"
				);
				$flipAPI = new FlipAPI();
				$get_data = $flipAPI->createDisbursement($data);
				var_dump($get_data);
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