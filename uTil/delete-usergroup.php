<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$groupid = $_GET['groupid'];
		
	// Build the delete statement
	$deleteEmail = "DELETE FROM usergroups2 WHERE id = '$groupid'";
	
	try
	{
		$result = $pdo3->prepare("$deleteEmail")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
		$_SESSION['successMessage'] = $lang['usergroup-deleted'];
	
		header("Location: ../usergroups.php");