<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$visitNo = $_GET['visitNo'];	
	$source = $_GET['source'];
		
	// Build the delete statement
	$deleteVisit = "DELETE FROM newvisits WHERE visitNo = $visitNo";
	
	try
	{
		$result = $pdo3->prepare("$deleteVisit")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	$_SESSION['successMessage'] = $lang['visit-deleted'];
	
	if ($source == 'visits') {
		header("Location: ../visits.php");
	} else {
		header("Location: ../visits.php?userid=$userid");
	}
?>
