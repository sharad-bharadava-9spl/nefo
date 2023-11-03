<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$source = $_GET['source'];	
		
	// Build the delete statement
	$deleteExpense = sprintf("DELETE FROM expenses_mklnew WHERE expenseid = '%d';", $_REQUEST['expenseid']);
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
	$_SESSION['successMessage'] = "Expense has been deleted.";
	
	if ($source == 'closing') {
		header("Location: ../close-day-reception.php?addexpense");
	} else if ($source == 'shiftclose') {
		header("Location: ../close-shift-reception.php?addexpense");
	} else if ($source == 'expenses') {
		header("Location: ../expenses-mkl.php");
	} 
?>