<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	$scanIn = "SELECT visitNo, userid, scanin FROM newvisits WHERE completed = 0 ORDER BY scanin DESC";
	try
	{
		$results = $pdo3->prepare("$scanIn");
		$results->bindValue(':loggedInUser', $loggedInUser);
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($scaninData = $results->fetch()) {
		
		$visitNo = $scaninData['visitNo'];
		$userid = $scaninData['userid'];
		$scanin = $scaninData['scanin'];
		
		$visitTime = date('Y-m-d H:i:s');
		
		// Determine duration
		$minutesOfVisit = round(abs(strtotime($scanin) - strtotime($visitTime)) / 60,2);
	
		$query = "UPDATE newvisits SET scanout = '$visitTime', duration = $minutesOfVisit, completed = 1 WHERE visitNo = $visitNo";
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
	}
	
	$_SESSION['successMessage'] = $lang['all-signed-out'];
	
	header("Location: ../visits.php");