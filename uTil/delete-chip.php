<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
		
	try
	{
		$result = $pdo3->prepare("SELECT cardid FROM users WHERE user_id = '$user_id'");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
		$cardid = $row['cardid'];

	
	// Build the delete statement
	$deleteExpense = "UPDATE users SET cardid = '' WHERE user_id = '$user_id'";
	
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
		
	$_SESSION['successMessage'] = $lang['chip-deleted'];
	
		header("Location: ../duplicate-chip.php?cardid=$cardid");