<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the purchase ID
	$donationid = $_GET['donationid'];
	$amount = $_GET['amount'];
	$userid = $_GET['userid'];
	
	// Delete the donation
	$deleteDonation = sprintf("DELETE FROM donations WHERE donationid = '%d';", $donationid);
	
	try
	{
		$result = $pdo3->prepare("$deleteDonation")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	// Delete the donation
	$deleteDonation = sprintf("DELETE FROM f_donations WHERE donationid = '%d';", $donationid);
	
	try
	{
		$result = $pdo3->prepare("$deleteDonation")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	// Look up user to find credit balance
	$userDetails = "SELECT credit FROM users WHERE user_id = {$userid}"; // 30
			
	try
	{
		$result = $pdo3->prepare("$userDetails");
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
	
	// Adjust the credit balance and save the new balance
	$newCredit = $credit - $amount;
	
	$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
		$newCredit,	$userid);
			
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

	// Write to log
	$logTime = date('Y-m-d H:i:s');

	$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
	2, $logTime, $userid, $_SESSION['user_id'], $amount, $credit, $newCredit);
	
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
	2, $logTime, $userid, $_SESSION['user_id'], $amount, $credit, $newCredit);
	
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
	
		$_SESSION['successMessage'] = $lang['donation-deleted'];
		
		if (isset($_GET['donscreen'])) {
			header("Location: ../donations.php");
		} else {
			header("Location: ../donation-management.php?userid=$userid");
		}