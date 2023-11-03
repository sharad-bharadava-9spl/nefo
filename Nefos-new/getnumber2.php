<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	getSettings();
	
	$selectNumber = "SELECT MAX(number) FROM customers WHERE number LIKE ('9%')";
	try
	{
		$result = $pdo3->prepare("$selectNumber");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$newNumber = $row['MAX(number)'] + 1;	

  	  echo $newNumber;