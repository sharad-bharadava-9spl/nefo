<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	$deleteUser = "DELETE FROM employees WHERE empno = '{$_GET['user_id']}'";
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

	$_SESSION['successMessage'] = "Fingerprint has been deleted.";
	header("Location: ../profile.php?f=y&user_id={$_GET['user_id']}");
