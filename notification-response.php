<?php

	// require_once '../cOnfig/connection.php';
	// require_once '../cOnfig/authenticate.php';
	// require_once '../cOnfig/languages/common.php';
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$subject = $_GET['s'];	
	$answer = $_GET['a'];	
		
	$deleteExpense = "INSERT INTO notification_response (user_id, domain, reply, subject) VALUES ('{$_SESSION['user_id']}', '{$_SESSION['domain']}', '$answer', '$subject')";
	try
	{
		$result = $pdo->prepare("$deleteExpense")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$query = "UPDATE notifications SET msgread = 1 WHERE user_id = {$_SESSION['user_id']}";
	try
	{
		$result = $pdo3->prepare("$query")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	if ($_SESSION['lang'] == 'es' || $_SESSION['lang'] == 'it') {
		$_SESSION['successMessage'] = "Gracias!";
	} else {
		$_SESSION['successMessage'] = "Thank you!";
	}
	
	header("Location: index.php");