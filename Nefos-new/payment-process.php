<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	authorizeUser($accessLevel);

	if (isset($_POST['save_payment'])) {
		
	    $amount = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['amount']))); 
	    $currency = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['currency']))); 
	    $bank_id = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['bank_id']))); 
	    $settled_date = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['settled_date']))); 
	    $settled_date = date("Y-m-d h:i:s", strtotime($settled_date));
	    $bank_lodgement_date = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['bank_lodgement_date']))); 
	    $bank_lodgement_date = date("Y-m-d h:i:s", strtotime($bank_lodgement_date));
	    $payment_type = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['payment_type']))); 
	    $invoices = $_POST['invoices']; 
	    $invoice_str = implode(",",  $invoices);
	    $allocate_payment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['allocate_payment']))); 
	    $delta = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['delta']))); 
	    $comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment']))); 
		$custNumber = $_REQUEST['end_customer_num'];
		
	   	$created_date = date("Y-m-d H:i:s");
	   	if($allocate_payment == ''){
	   		 $allocate_payment = 'no';
	   	}
	   	$_SESSION['invoices'] = $invoices;

	   	$paid_invoice_arr = $_POST['paid_invoice'];
	   	// update delta to the invoice
	   	$last_inovice_id = end($invoices);

	   	if($delta == ''){
	   		$delta = 0;
	   	}
	   	
	   	if ($payment_type != 3) {
		   	$online_verified = 1; 
	   	}
	   	
/*	   	$selectlastInvoiceAmount = "SELECT  amount FROM invoices WHERE invno = '".$last_inovice_id."'";
	   	try
		{
			$lastInv_results = $pdo->prepare("$selectlastInvoiceAmount")
			$lastInv_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$lastinv_row = $lastInv_results->fetch();
			$lastInv_amount = $lastinv_row['amount'];
			$updatedInv_amount =  parseFloat($lastInv_amount) + parseFloat($delta);
			$updatedInv_amount = number_format($updatedInv_amount, 2);*/

	   	$updateDelta = "UPDATE invoices SET delta = '$delta'  WHERE invno = '".$last_inovice_id."'"; 

	   	try
		{
			$pdo->prepare("$updateDelta")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
		if ($_REQUEST['end_customer_num'] == '') {
			
			$custNumber = 0;
			
		} else {
			
		    foreach ($invoices as $indInvoice)  {
			    
			    $query = "SELECT customer FROM invoices WHERE invno = '$indInvoice'";
				try
				{
					$result = $pdo->prepare("$query");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$customer = $row['customer'];
					
					
				if (strpos($custNumber, $customer) !== false) {
						
					
				} else {
				
					$custNumber = $custNumber . ',' . $customer;
	
				}
				
	        }
			
		}
        
        	
		// Query to update user - 28 arguments
		 $insertPayment = sprintf("INSERT INTO invoice_payments (amount, currency, bank_id, settled_date, bank_lodgement_date, payment_type, invoices, allocate_payment, comment, created_at, customer, online_verified, delta) VALUES ('%f', '%s', '%d', '%s','%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%f')",
					$amount,
					$currency,
					$bank_id,
					$settled_date,
					$bank_lodgement_date,
					$payment_type,
					$invoice_str,
					$allocate_payment,
					$comment,
					$created_date,
					$custNumber,
					$online_verified,
					$delta
					);
		try
		{
			$result = $pdo->prepare("$insertPayment")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$payment_id = $pdo->lastInsertId();

		// update payment id fro invoices

		foreach ($invoices as $invoice) {

				$updateInvoice = "UPDATE invoices SET payment = '$payment_id' WHERE invno = '".$invoice."'";

			try
			{
				$pdo->prepare("$updateInvoice")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

		}

		// update paid invoices
		if(!empty($paid_invoice_arr)){
			foreach($paid_invoice_arr as $paid_invoice){

				$updatePaidInvoice = "UPDATE invoices SET paid = 'Paid' WHERE invno = '".$paid_invoice."'";

				try
				{
					$pdo->prepare("$updatePaidInvoice")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

			}
		}

		// update client credit if allocate

		if($allocate_payment == 'yes'){
			$delta_credit = $_REQUEST['use_delta_hidden'];
			$customer_number = $_REQUEST['end_customer_num'];
			$invoice_id = $_REQUEST['end_invoice_id'];
			$created_at = date('Y-m-d H:i:s');
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
				 $updateUser = "INSERT into credits SET invoice_no = '$invoice_id', customer = '$customer_number', payment_number = '$payment_id', reason_id = '$credit_reason', amount = '$delta_credit', credit_balance = '$client_credit', comment = '', credit_type = 2, created_at = '$created_at'";  
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
		}

		if($payment_type == 3){
			header("Location: regenerate-invoice.php");
			die;
		}else{
			$_SESSION['successMessage'] = "Payment added successfully!";
			header("Location: invoice-payments.php");
			die;
		}

	}