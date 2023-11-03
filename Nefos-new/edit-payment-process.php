<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	authorizeUser($accessLevel);

	if (isset($_POST['id'])) {
		$id = $_POST['id'];
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
	   	$update_date = date("Y-m-d H:i:s");
	   	if($allocate_payment == ''){
	   		 $allocate_payment = 'no';
	   	}
	   	$_SESSION['payment_type'] = $payment_type;
	   	$_SESSION['invoices'] = $invoices;
	   	
	   	if ($delta == '') {
		   	$delta = 0;
	   	}

	   	$paid_invoice_arr = $_POST['paid_invoice'];
	   	// update delta to the invoice
	   	$last_inovice_id = end($invoices);
	   	
	   	$query = "SELECT customer FROM invoices WHERE invno = '$last_inovice_id'";
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
			$custNumber = $row['customer'];
	   

	   	// get old data of payments

	   	$selectOldInvoices =  "SELECT invoices, payment_type FROM invoice_payments WHERE id = ".$id;

	   	try
		{
			$old_results =  $pdo->prepare("$selectOldInvoices");
			$old_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$old_invoice_row = $old_results->fetch();
			$old_invoices = $old_invoice_row['invoices'];
			$old_payment_type = $old_invoice_row['payment_type'];
			$old_invoice_arr = explode(",", $old_invoices);

			$_SESSION['old_invoices'] = $old_invoice_arr;
			$_SESSION['old_payment_type'] = $old_payment_type;

		// get old invoice credit

		$oldInvoiceCredit = "SELECT customer, amount from credits WHERE payment_number =".$id;
		try
			{
				$old_credit_results=  $pdo3->prepare("$oldInvoiceCredit");
				$old_credit_results->execute();
			}
		catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		$oldinvoiceCredit_row = $old_credit_results->fetch();
			$old_customer = $oldinvoiceCredit_row['customer'];
			$old_credit = $oldinvoiceCredit_row['amount'];
		if($old_customer != ''){
			$checkOldCredit = "SELECT credit FROM customers WHERE number =".$old_customer; 

				try
				{
					$old_credit_result = $pdo3->prepare("$checkOldCredit");
					$old_credit_result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching credit: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$old_fetch_credit = $old_credit_result->fetch();
					//$client_credit = 0;
					//if(!empty($fetch_credit['credit'])){
						$old_client_credit =  $old_fetch_credit['credit'] - $old_credit;
			// update credit in customers table

			$updateOldCredit = "UPDATE customers SET credit = '$old_client_credit' WHERE number =".$old_customer;

			try
			{
				$update_old_credit = $pdo3->prepare("$updateOldCredit");
				$update_old_credit->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

			// DELETE credit from credit table

			$deleteOldcredit = "DELETE FROM credits WHERE payment_number =".$id;

			try
			{
				$pdo3->prepare("$deleteOldcredit")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

		}
		if(!in_array($last_inovice_id, $old_invoice_arr)){
			$updateOldDelta = "UPDATE invoices SET delta = '0' WHERE invno = '".end($old_invoice_arr)."'"; 

		   	try
			{
				$pdo->prepare("$updateOldDelta")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		}

		// update old data

		foreach ($old_invoice_arr as $old_invoice) {

				$updateOldInvoice = "UPDATE invoices SET payment = '0', delta = '0', paid =''  WHERE invno = '".$old_invoice."'";

			try
			{
				$pdo->prepare("$updateOldInvoice")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

		}

	   	$updateDelta = "UPDATE invoices SET delta = '$delta' WHERE invno = '".$last_inovice_id."'"; 

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

		$payment_id = $id;

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
		
		$updatePyament = "UPDATE invoice_payments SET amount = '$amount', currency = '$currency', bank_id = '$bank_id', settled_date = '$settled_date', bank_lodgement_date= '$bank_lodgement_date', payment_type = '$payment_type', invoices = '$invoice_str', allocate_payment = '$allocate_payment' , comment = '$comment', updated_at = '$update_date', customer = '$custNumber', online_verified = 1, delta = '$delta'  WHERE id='$id'";
		try
		{
			$result = $pdo->prepare("$updatePyament")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
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
			$delta_credit= $_REQUEST['use_delta_hidden'];
			$customer_number = $_REQUEST['end_customer_num'];
			$invoice_id = $_REQUEST['end_invoice_id'];
			$created_at = date('Y-m-d H:i:s');
			$credit_reason = 5;
			if($delta_credit == ''){
				$delta_credit = 0;
			}


				$checkCredit = "SELECT credit FROM customers WHERE number =".$customer_number; 

				try
				{
					$credit_result = $pdo3->prepare("$checkCredit");
					$credit_result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching credit: ' . $e->getMessage();
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

		
			header("Location: recreate-edit-invoice.php");
			die;
		
	}