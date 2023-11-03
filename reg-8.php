<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-no-warnings.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$user_id = $_GET['user_id'];

	$query = "UPDATE users SET userGroup = 5 WHERE user_id = $user_id";
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
	
	// On success: redirect.
	$_SESSION['successMessage'] = "Socio registrado con éxito!";
	header("Location: profile.php?user_id={$user_id}");
	exit();
