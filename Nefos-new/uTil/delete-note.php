<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get note ID and user ID
	$noteid = $_GET['noteid'];
	$userid = $_GET['userid'];
	
	// Build the delete statement
	$deleteNote = "DELETE FROM usernotes WHERE noteid = $noteid";
		try
		{
			$result = $pdo3->prepare("$deleteNote")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	$_SESSION['successMessage'] = "Note has been deleted.";
	header("Location: ../customer.php?deleted=yes&user_id=$userid");
	
?>