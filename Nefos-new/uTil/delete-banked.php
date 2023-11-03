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
	
	mysql_query($deleteExpense)
		or handleError("Error deleting expense.","Error deleting expense: " . mysql_error());
		
	$_SESSION['successMessage'] = $lang['entry-deleted'];
	
		header("Location: ../bank-money.php");