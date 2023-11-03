<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$id = $_GET['id'];
	$pos = $_GET['pos'];
	$src = $_GET['src'];
	
	$now = date('Y-m-d H:i');
	
	if ($_GET['set'] == 'null') {
	
		$changeMenu = "UPDATE customers SET prospect_demo = NULL WHERE id = $id";
		try
		{
			$result = $pdo3->prepare("$changeMenu")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	} else {
	
		$changeMenu = "UPDATE customers SET prospect_demo = '$now' WHERE id = $id";
		try
		{
			$result = $pdo3->prepare("$changeMenu")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	}
		
	
	header("Location: ../prospects.php?pos=$pos&$src");
