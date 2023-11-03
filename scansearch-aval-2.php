<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$scanner = $_SESSION['scanner'];
	
	$scanSearch = "SELECT chip FROM newscan WHERE type = '$scanner' ORDER BY scanid DESC LIMIT 1";
	
	try
	{
		$result = $pdo3->prepare("$scanSearch");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
			
	if (!$data) {
		
		echo 'false';
		
	} else {
		
		$row = $data[0];
			$chip = $row['chip'];
			
		$userLookup = "SELECT user_id FROM users WHERE cardid = $chip";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
			
			$deleteScans = "DELETE FROM newscan WHERE type = $scanner";
			try
			{
				$result = $pdo3->prepare("$deleteScans")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			// Return with error: Card not registered
			echo 'notregistered';
			exit();
			
		}
			
		$row = $data[0];
			$user_id = $row['user_id'];
			
		$deleteScans = "DELETE FROM newscan WHERE type = $scanner";
		try
		{
			$result = $pdo3->prepare("$deleteScans")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}


		echo $user_id;
		
	}
