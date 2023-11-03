<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';
	
	

	$order_id = $_REQUEST['order_id'];


	$selectSale = "SELECT amount FROM sales WHERE saleid =".$order_id;

	try
	{
		$results = $pdo3->prepare("$selectSale");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$order_row = $results->fetch();
		$amount = $order_row['amount'];

		if($amount == '' ){
			$amount = 0;
		}
	$credit_card_fee = $amount * 0.015;

	$total_fee = number_format($amount + $credit_card_fee, 2);

	$response['amount'] = $total_fee;

	header('Content-Type: application/json');
		echo json_encode($response);
		die;	
	