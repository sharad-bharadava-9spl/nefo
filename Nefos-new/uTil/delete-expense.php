<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$source = $_GET['source'];	
		
	// Build the delete statement
	$deleteExpense = sprintf("DELETE FROM expenses WHERE expenseid = '%d';", $_REQUEST['expenseid']);
	mysql_query($deleteExpense)
		or handleError("Error deleting expense.","Error deleting expense: " . mysql_error());
	$_SESSION['successMessage'] = "Expense has been deleted.";
	
	if ($source == 'closing') {
		header("Location: ../close-day-reception.php?addexpense");
	} else if ($source == 'shiftclose') {
		header("Location: ../close-shift-reception.php?addexpense");
	} else if ($source == 'expenses') {
		header("Location: ../expenses.php");
	} 
?>