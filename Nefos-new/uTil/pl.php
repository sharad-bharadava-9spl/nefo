<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$customer = $_GET['customer'];
	
	$query = "SELECT shortName, pl FROM customers WHERE id = '$customer'";
	try
	{
		$result = $pdo2->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$shortName = $row['shortName'];
		$pl = $row['pl'];
		
	if ($pl == '') {
		
		$query = "UPDATE customers SET pl = 'L' WHERE id = '$customer'";
		try
		{
			$result = $pdo2->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
	} else if ($pl == 'L') {
		
		$query = "UPDATE customers SET pl = 'R' WHERE id = '$customer'";
		try
		{
			$result = $pdo2->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
	} else {
		
		$query = "UPDATE customers SET pl = '' WHERE id = '$customer'";
		try
		{
			$result = $pdo2->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
	}
	
	$_SESSION['successMessage'] = "Status changed succesfully!";
	header("Location: ../cutoff.php");
		
