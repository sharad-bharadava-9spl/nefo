<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteQueue = sprintf("DELETE FROM member_queue WHERE id = '%d'", $_GET['queueid']);
		try
		{
			$result = $pdo3->prepare("$deleteQueue")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

	$_SESSION['successMessage'] = "Member removed from the queue!";	
	header("Location: ../queue.php");
?>
