<?php
	namespace Model;

	class FlipResponseLog {

		public static function Log($disbursements_id, $external_disbursement_id, $request_path, $response) {
			$current_time = date("Y-m-d H:i:s");
			$dbh = \Lib\Database::getInstance();
			$statement = $dbh->prepare("INSERT INTO `flip_response_logs` ".
					"(`disbursements_id`, `external_disbursement_id`, ".
					"`request_path`, `response`, `created_at`) ".
					"VALUES (?, ?, ?, ?, ?)");

			$statement->execute([
				$disbursements_id,
				$external_disbursement_id,
				$request_path,
				$response,
				$current_time
			]);
		}

	}
?>