<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the sale ID
	$saleid = $_GET['saleid'];
	
	// Look up sale details (amount)
	$selectSale = "SELECT userid, amount, direct FROM b_sales where saleid = $saleid";

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
		$direct = $row['direct'];
		
		
	// Look up users credit
	$selectCredit = "SELECT credit FROM users WHERE user_id = $userid";

	try
	{
		$result = $pdo3->prepare("$selectCredit");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$credit = $row['credit'];
	
	if ($direct == 3) {
		
		// Adjust user credit
		$newcredit = $credit + $amount;
	
		$updateCredit = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			$newcredit, $userid);
				
		try
		{
			$result = $pdo3->prepare("$updateCredit")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
	} else {
		
		$newcredit = $credit;
		
	}
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM b_sales WHERE saleid = '%d';", $_REQUEST['saleid']);
	try
	{
		$result = $pdo3->prepare("$deleteUser")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	$deleteUser = sprintf("DELETE FROM f_b_sales WHERE saleid = '%d';", $_REQUEST['saleid']);
	try
	{
		$result = $pdo3->prepare("$deleteUser")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	$deleteUser = sprintf("DELETE FROM b_salesdetails WHERE saleid = '%d';", $_REQUEST['saleid']);
	try
	{
		$result = $pdo3->prepare("$deleteUser")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$deleteUser = sprintf("DELETE FROM f_b_salesdetails WHERE saleid = '%d';", $_REQUEST['saleid']);
	try
	{
		$result = $pdo3->prepare("$deleteUser")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	// Write to log
	$logTime = date('Y-m-d H:i:s');

	$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
	4, $logTime, $userid, $_SESSION['user_id'], $amount, $credit, $newcredit);
	
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
	
	$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
	4, $logTime, $userid, $_SESSION['user_id'], $amount, $credit, $newcredit);
	
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
	
	$_SESSION['successMessage'] = $lang['sale-deleted'];
	header("Location: ../bar-sales.php");