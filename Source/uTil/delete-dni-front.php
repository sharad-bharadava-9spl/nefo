<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
	$domain = $_SESSION['domain'];
	
	$query = "SELECT photoext, dniext1, dniext2 FROM users WHERE user_id = $user_id";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$photoext = $row['photoext'];
		$dniext1 = $row['dniext1'];
		$dniext2 = $row['dniext2'];
		$sigext = $row['sigext'];
	

	if ($sigext == '') {
		$sigext = 'png';
	}

	
	$filename = "../images/_$domain/ID/$user_id-front.$dniext1";
	
	unlink($filename);
	
	$_SESSION['successMessage'] = $lang['dni-deleted'];
	
	header("Location: ../profile.php?user_id=$user_id");
