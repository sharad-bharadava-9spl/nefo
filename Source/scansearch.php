<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$scanner = $_SESSION['scanner'];
	
	try
	{
		$result = $pdo3->prepare("SELECT chip FROM newscan WHERE type = '$scanner' ORDER BY scanid DESC LIMIT 1");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();

	if ($row == '') {
		
		echo 'false';
		
	} else {
		
		$chip = $row['chip'];
		
		try
		{
			$result = $pdo3->prepare("DELETE FROM newscan WHERE type = '$scanner'")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}		
			
		try
		{
			$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid = '$chip'");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			
			
		if ($row == '') {

			// Return with error: Card not registered
			echo 'notregistered';
			exit();

		} else {
			
			$user_id = $row['user_id'];
			echo $user_id;
			
		}

	}