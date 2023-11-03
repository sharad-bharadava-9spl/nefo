<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the user ID
	$user_id = $_GET['user_id'];
	
	$visitTime = date('Y-m-d H:i:s');
	tzo();
	$visitTimeReadable = date('H:i');
		
	try
	{
		$result = $pdo3->prepare("INSERT INTO newvisits (userid, scanin) VALUES ($user_id, '$visitTime')")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	// Lookup usergroup
	try
	{
		$result = $pdo3->prepare("SELECT userGroup FROM users WHERE user_id = $user_id");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$userGroup = $row['userGroup'];
		
	if ($userGroup == 9) {
		
		try
		{
			$result = $pdo3->prepare("UPDATE users SET userGroup = 5 WHERE user_id = $user_id")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	}

	
	$_SESSION['successMessage'] = $lang['member-entered'] . ": " . $visitTimeReadable . ".";
		header("Location: ../mini-profile.php?user_id={$user_id}");