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
	
	// Lookup user's last visit:
	try
	{
		$result = $pdo3->prepare("SELECT visitNo, scanin FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$visitNo = $row['visitNo'];
		$scanin = $row['scanin'];
		
	$visitTime = date('Y-m-d H:i:s');
	tzo();
	$visitTimeReadable = date('H:i');
	
	// Determine duration
	$minutesOfVisit = round(abs(strtotime($scanin) - strtotime($visitTime)) / 60,2);

	try
	{
		$result = $pdo3->prepare("UPDATE newvisits SET scanout = '$visitTime', duration = $minutesOfVisit, completed = 1 WHERE visitNo = $visitNo")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	
	if ($_GET['source'] == 'visits') {
		header("Location: ../visits.php");
	} else {
		$_SESSION['successMessage'] = $lang['member-left'] . ": " . $visitTimeReadable . ".";
		
		if ($_SESSION['domain'] == 'dabulance') {
 			header("Location: ../main.php");
		} else {
			header("Location: ../mini-profile.php?user_id={$user_id}");
		}
	}