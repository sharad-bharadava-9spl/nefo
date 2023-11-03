<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the sale ID
	$user_id = $_GET['user_id'];
	$friend = $_GET['a'];
	
	if ($friend == 1) {
		$updateCredit = "UPDATE users SET friend = '' WHERE user_id = $user_id";
	} else {
		$updateCredit = "UPDATE users SET friend2 = '' WHERE user_id = $user_id";
	}
			
	try
	{
		$result = $pdo3->prepare("$updateCredit")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
			

	$_SESSION['successMessage'] = $lang['guardian-deleted'];
	header("Location: ../profile.php?user_id=$user_id");