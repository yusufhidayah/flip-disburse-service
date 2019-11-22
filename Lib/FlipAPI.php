<?php
	namespace Lib;

	class FlipAPI {
		public function createDisbursement($data) {
			$permitted_data = array(
				"bank_code"=>(string)$data['bank_code'],
				"account_number"=>(string)$data['account_number'],
				"amount"=>(int)$data['amount'],
				"remark"=>(string)$data['remark']
			);

			return $this->sendRequest("POST", "/disburse", $permitted_data);
		}

		public function getDisbursement($transaction_id) {
			return $this->sendRequest("GET", "/disburse/".$transaction_id, false);
		}

		private function sendRequest($method, $path, $data){
			$curl = curl_init();
			$url	=	env('FLIP_API_HOST')."".$path;
	 
			switch ($method){
				 case "POST":
						curl_setopt($curl, CURLOPT_POST, 1);
						if ($data)
							 curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
						break;
				 case "PUT":
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
						if ($data)
							 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
						break;
				 default:
						if ($data)
							 $url = sprintf("%s?%s", $url, http_build_query($data));
			}
	 
			// OPTIONS:
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

			$basic_credentials = base64_encode(env('FLIP_BASIC_USERNAME').":".env('FLIP_BASIC_PASSWORD'));
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				 "Authorization: Basic ".$basic_credentials,
				 'Content-Type: application/x-www-form-urlencoded'
			));

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	 
			// EXECUTE:
			$result = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if($httpcode == 522){ die("Connection to FLIP API Gateway Timed Out\n"); }
			if(!$result){ die("Connection to FLIP API Gateway Failure\n"); }
			curl_close($curl);
			return $result;
		}
	}
?>