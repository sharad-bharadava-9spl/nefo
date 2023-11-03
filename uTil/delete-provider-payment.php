<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$paymentid = $_GET['paymentid'];
	$providerid = $_GET['providerid'];
	
	// Build the delete statement
	$deleteExpense = sprintf("DELETE FROM providerpayments WHERE paymentid = '%d';", $paymentid);
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
	
	$_SESSION['successMessage'] = $lang['payment-deleted'];
	header("Location: ../provider.php?providerid=$providerid");