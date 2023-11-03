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
	$deleteDonation = sprintf("DELETE FROM card_purchase WHERE id = '%d';", $donationid);
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
		$_SESSION['successMessage'] = $lang['chip-purchase-deleted'];
		
			header("Location: ../card-purchases.php");