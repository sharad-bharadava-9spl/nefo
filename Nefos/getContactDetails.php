<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

	$contact_id = $_POST['id'];

    $getContacts = "SELECT * from contacts WHERE id=".$contact_id; 

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
	
	$row = $results->fetch();

	$contact_number = $row['telephone'];
	$contact_email = $row['email'];
	
	$response = array("contact_number" => $contact_number, "contact_email" => $contact_email);
header('Content-Type: application/json');
	echo json_encode($response);

	die;