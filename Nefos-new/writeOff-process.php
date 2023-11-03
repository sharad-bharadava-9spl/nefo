<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	authorizeUser($accessLevel);

	if (isset($_POST['save_writeoff'])) {

	    $settled_date = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['settled_date']))); 
	    $settled_date = date("Y-m-d h:i:s", strtotime($settled_date));
	    $invoices = $_POST['write_invoice']; 
	    $invoice_str = implode(",",  $invoices);
	    $comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment']))); 

	   	$created_date = date("Y-m-d H:i:s");
	   	// update delta to the invoice
	   	$last_inovice_id = end($invoices);
		// Query to update user - 28 arguments
		 $insertWriteOff = sprintf("INSERT INTO invoice_writeoffs (settled_date, invoices, comment, created_at) VALUES ('%s', '%s', '%s', '%s')",
					$settled_date,
					$invoice_str,
					$comment,
					$created_date,
					);
		try
		{
			$result = $pdo->prepare("$insertWriteOff")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$writeOff_id = $pdo->lastInsertId();

		// update payment id fro invoices

		foreach ($invoices as $invoice) {

				$updateInvoice = "UPDATE invoices2 SET writeOff = '$writeOff_id', paid = 'Write Off' WHERE invno = '".$invoice."'";

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

			$_SESSION['successMessage'] = "Write Off Payment added successfully!";
			header("Location: invoice-write-offs.php");
			die;
		

	}