<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	// fetch invoice credit applied
	$invoiceId =  $_GET['id'];
	// check if any payment attached on this imnvoice 
		
	$selectPayment = "SELECT id,allocate_payment,invoices FROM invoice_payments WHERE invoices REGEXP ('($invoiceId)') ";
	try
	{
		$inv_pay_result = $pdo->prepare("$selectPayment");
		$inv_pay_result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$inv_pay_row = $inv_pay_result->fetch();
	$payment_count = $inv_pay_result->rowCount();
		$payment_id = $inv_pay_row['id'];
	// check if any write off attached

	$selectWriteOff = "SELECT id,invoices FROM invoice_writeoffs WHERE invoices REGEXP ('($invoiceId)') ";
	try
	{
		$inv_write_result = $pdo->prepare("$selectWriteOff");
		$inv_write_result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$inv_write_row = $inv_write_result->fetch();
	$write_count = $inv_write_result->rowCount();
		$write_id = $inv_write_row['id'];


	// check added credit to this invoice 
	if($payment_count > 0){
		$_SESSION['errorMessage'] = "This invoice has been settled by payment ".$payment_id.", and as such you can not delete it. If you want to delete it, you first have to delete the payment connected to the invoice. You can do so by clicking <a href='invoice-payments.php'>here</a>.";
		header("Location: ../invoices.php");
		exit();
	}	


	if($write_count > 0){
		$_SESSION['errorMessage'] = "This invoice has been settled by payment write off ".$write_id.", and as such you can not delete it. If you want to delete it, you first have to delete the payment connected to the invoice. You can do so by clicking <a href='invoice-write-offs.php'>here</a>.";
		header("Location: ../invoices.php");
		exit();
	}



	$selectInvoice = "SELECT customer, client_credit, brand FROM invoices2 WHERE invno = '".$invoiceId."'";

		try
		{
			$inv_result = $pdo->prepare("$selectInvoice");
			$inv_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$inv_row = $inv_result->fetch();
			$inv_customer = $inv_row['customer'];
			$inv_used_credit = $inv_row['client_credit'];
			$inv_type = $inv_row['brand'];

			

			// delete credit from customer credit

			$checkCredit = "SELECT credit FROM customers WHERE number =".$inv_customer;

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
						$client_credit = $fetch_credit['credit'] + $inv_used_credit;

				// update credit in customers table

			$updateCredit = "UPDATE customers SET credit = '$client_credit' WHERE number =".$inv_customer;

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

			// fetch credits from credit table	
			
			$selectinCredit = "SELECT * from credits WHERE invoice_no= '".$invoiceId."'";

				try
				{
					$inv_credit_results = $pdo3->prepare("$selectinCredit");
					$inv_credit_results->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$creditCount = $inv_credit_results->rowCount();
				if($creditCount > 0){
					while($creditDetails = $inv_credit_results->fetch()){
						$customer_number = $creditDetails['customer'];
						$credit_reason = $creditDetails['reason_id'];
						$amount = $creditDetails['amount'];
						$comment = $creditDetails['comment'];
						$created_at = date("Y-m-d H:i:s");
						if($credit_reason == ''){
							$credit_reason = 0;
						}
						// update in credit movements for credits

						$insertMovement = "INSERT INTO credit_movements SET invoice_id = '$invoiceId', customer = '$customer_number', credit_status = 'Added', amount = '$inv_used_credit', movement_at = '$created_at', credit_reason ='$credit_reason', comment = '$comment'";

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
					}
				}

				// delete credit from table

				$deleteCredit = "DELETE FROM credits WHERE invoice_no = '".$invoiceId."'";
					try
					{
						$pdo3->prepare("$deleteCredit")->execute();
						
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}

				// remove invoice files from folder

				// get the customer domain name
				$getDomain = "SELECT domain from db_access WHERE customer =".$inv_customer;
				try
				{
					$domainDetails = $pdo->prepare("$getDomain");
					$domainDetails->execute();
					
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$invoice = $inv_customer."-".$invoiceId."-".$inv_type;
				$domainCount = $domainDetails->rowCount();
				$invoice_club_path = '';
				$invoice_club_dir = '';
				if($domainCount > 0){
					$domainRow = $domainDetails->fetch();
					$domain = $domainRow['domain'];
					$invoice_club_path = '../../_club/_'.$domain.'/invoices/'.$invoice.'.pdf';
					$invoice_club_dir = '../../_club/_'.$domain.'/invoices';
				}
				$invoice_root_path = "../../invoices/".$invoice.".pdf";
				$invoice_root_dir = "../../invoices";

				if(file_exists($invoice_club_path)){
					unlink($invoice_club_path);
				}				
				if(file_exists($invoice_root_path)){
					unlink($invoice_root_path);
				}

				// Build the delete statement
				$deleteUser = sprintf("UPDATE invoices SET deleteFlag = 1  WHERE invno = '%s';",$invoiceId);
					try
					{
						$result = $pdo->prepare("$deleteUser")->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					
				$_SESSION['successMessage'] = "invoice deleted succesfully!";
				header("Location: ../invoices.php");
?>