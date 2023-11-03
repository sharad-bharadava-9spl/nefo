<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the purchase ID
	$id = $_GET['id'];
	
	// Delete the donation
	$query = "DELETE FROM customers WHERE id = '$id'";
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
	
	$_SESSION['successMessage'] = "Client deleted succesfully!";
	header("Location: ../index.php");
	exit();
?>