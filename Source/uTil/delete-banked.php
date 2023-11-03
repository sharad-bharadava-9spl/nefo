<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$id = $_GET['id'];	
		
	// Build the delete statement
	$deleteExpense = "DELETE FROM banked WHERE id = $id";
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
	$_SESSION['successMessage'] = $lang['entry-deleted'];
	
		header("Location: ../bank-money.php");