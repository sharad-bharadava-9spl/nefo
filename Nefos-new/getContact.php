<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

	$customer_number = $_GET['cust_num'];

	$getContacts = "SELECT * from contacts WHERE customer=".$customer_number;

	try
	{
		$results = $pdo3->prepare("$getContacts");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	echo "<option value=''>Select Contact Person</option>";
	while($row = $results->fetch()){
		echo "<option value='".$row['id']."'>".$row['name']."</option>";
	}