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
		$result = $pdo3->prepare("SELECT setting4 FROM systemsettings");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$s4 = $row['setting4'];

	if ($s4 == '0') {
		
		echo 'false';
		
	} else {
		
		echo 'true';

	}