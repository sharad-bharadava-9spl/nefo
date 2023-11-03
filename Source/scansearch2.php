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
			
		$currUser = $_SESSION['currUser'];
		
		try
		{
			$result = $pdo3->prepare("UPDATE users SET cardid = '$chip' WHERE user_id = $currUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
		unset($_SESSION['currUser']);
		
		echo $currUser;

	}