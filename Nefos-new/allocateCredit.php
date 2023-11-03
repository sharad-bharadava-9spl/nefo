<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';


	$delta_credit = $_REQUEST['credit'];
	$customer_number = $_REQUEST['customer'];
	$invoice_id = $_REQUEST['invoice_id'];
	$created_at = date('Y-m-d h:i:s');
	$credit_reason = 5;

		$checkCredit = "SELECT credit FROM customers WHERE number =".$customer_number;

		try
		{
			$credit_result = $pdo3->prepare("$checkCredit");
			$credit_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$fetch_credit = $credit_result->fetch();
			//$client_credit = 0;
			//if(!empty($fetch_credit['credit'])){
				$client_credit =  $fetch_credit['credit'] + $delta_credit;
			//}

		// update in credit movements for credits

		$insertMovement = "INSERT INTO credit_movements SET customer = '$customer_number', credit_status = 'Added', amount = '$delta_credit', invoice_id = '$invoice_id', movement_at = '$created_at', credit_reason ='$credit_reason', comment = ''";

		try
		{
			$insert_movement = $pdo3->prepare("$insertMovement");
			$insert_movement->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		

		// update credit in customers table

		$updateCredit = "UPDATE customers SET credit = '$client_credit' WHERE number =".$customer_number;

		try
		{
			$update_credit = $pdo3->prepare("$updateCredit");
			$update_credit->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		// insert into credit

		// Query to update user - 28 arguments
		 $updateUser = "INSERT into credits SET customer = '$customer_number', reason_id = '$credit_reason', amount = '$delta_credit', credit_balance = '$client_credit', comment = '', created_at = '$created_at'";  
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$response['client_credit'] = $client_credit;

	header('Content-Type: application/json');
		echo json_encode($response);
		die;		