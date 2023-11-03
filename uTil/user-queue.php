<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$user_id = $_GET['user_id'];
	
	$inTime = date('Y-m-d H:i:s');
	tzo();
	$inTimeReadable = date('H:i');

	if(isset($_GET['user_id'])){
		try
		{
			$result = $pdo3->prepare("INSERT INTO member_queue (user_id, member_in) VALUES ($user_id, '$inTime')")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$_SESSION['successMessage'] =  "Member added to queue : " . $inTimeReadable . ".";
			header("Location: ../mini-profile.php?user_id={$user_id}");
			
		exit();
	}