<?php 
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
		/* SIMPLE QUERY TO RUN */
		try
		{
			$result = $pdo3->prepare("UPDATE systemsettings SET setting4 = 1")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
