<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
	$domain = $_SESSION['domain'];
	
	$query = "SELECT medext FROM users WHERE user_id = $user_id";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$medext = $row['medext'];

	
	$filename = "../images/_$domain/med/$user_id.$medext";
	
	unlink($filename);
	
	$_SESSION['successMessage'] = $lang['med-deleted'];
	
	header("Location: ../profile.php?user_id=$user_id");
