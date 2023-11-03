<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$source = $_GET['source'];
	$expenseid = $_REQUEST['expenseid'];
	
	// Look up sale details (amount)
	$selectSale = "SELECT amount, userid FROM expenses where expenseid = $expenseid";

	try
	{
		$result = $pdo3->prepare("$selectSale");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$userid = $row['userid'];
		$amount = $row['amount'];
		
	// Build the delete statement
	$deleteExpense = sprintf("DELETE FROM expenses WHERE expenseid = '%d';", $expenseid);
	try
	{
		$result = $pdo3->prepare("$deleteExpense")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	// Write to log
	$logTime = date('Y-m-d H:i:s');

	$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount) VALUES ('%d', '%s', '%d', '%d', '%f');",
	15, $logTime, $userid, $_SESSION['user_id'], $amount);
	
	try
	{
		$result = $pdo3->prepare("$query")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$_SESSION['successMessage'] = $lang['expense-deleted'];
	
	if ($source == 'closing') {
		header("Location: ../close-day-reception.php?addexpense");
	} else if ($source == 'shiftclose') {
		header("Location: ../close-shift-reception.php?addexpense");
	} else if ($source == 'expenses') {
		header("Location: ../expenses.php");
	}