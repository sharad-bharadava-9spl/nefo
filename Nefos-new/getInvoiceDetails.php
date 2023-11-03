<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';
	
	

	$invoice_ids = explode(",", $_REQUEST['ids']);

     $invoice_str = "'" . implode ( "', '", $invoice_ids ) . "'"; 



 	$selectInvoice = "SELECT * FROM invoices WHERE invno IN (".$invoice_str.")"; 
	try
	{
		$results = $pdo->prepare("$selectInvoice");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while($invoice_row = $results->fetch()){
		$amount_arr[] = $invoice_row['amount'];
		$paid_arr[] = $invoice_row['paid'];
		$delta_pay = $invoice_row['delta'];
		if($delta_pay == ''){
			$delta_pay = 0;
		}
		$delta_arr[] = $delta_pay;
	}
	$amount_str = implode(",", $amount_arr);
	$paid_str = implode(",", $paid_arr);
	$delta_str = implode(",", $delta_arr);
	$response['amount'] = $amount_str;
	$response['paid'] = $paid_str;
	$response['inv_delta'] = $delta_str;

	// fetch the details of last invoice id

	$getCustomer = "SELECT customer from invoices WHERE invno = '".end($invoice_ids)."'";
	try
	{
		$customer_results = $pdo->prepare("$getCustomer");
		$customer_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$customer_row = $customer_results->fetch();
		$customer_num = $customer_row['customer'];

	// get customer credits

	$getCustomerCredit = "SELECT credit, debit FROM customers WHERE number =".$customer_num;
	try
	{
		$credit_results = $pdo3->prepare("$getCustomerCredit");
		$credit_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$credit_row = $credit_results->fetch();
	$client_credit = 0;
	if($credit_row['credit'] != ''){
		$client_credit = $credit_row['credit'];
	}
	$client_debit = $credit_row['debit'];	
    $response['client_credit'] = number_format($client_credit, 2);
    $response['client_debit'] = number_format($client_debit, 2);
    $response['customer'] = $customer_num;
    $response['invoice_id'] = end($invoice_ids);

	header('Content-Type: application/json');
		echo json_encode($response);
		die;	
	