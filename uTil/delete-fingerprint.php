<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteUser = sprintf("UPDATE users SET fptemplate1 = '', fptemplate2 = '', f_no1 = 0, f_no2 = 0 WHERE user_id = '%d';", $_GET['user_id']);
		try
		{
			$result = $pdo3->prepare("$deleteUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	header("Location: delete-finger-2.php?user_id={$_GET['user_id']}");
?>
