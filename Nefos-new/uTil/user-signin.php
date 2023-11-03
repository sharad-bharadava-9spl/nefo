<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the user ID
	$user_id = $_GET['user_id'];
	
	$visitTime = date('Y-m-d H:i:s');
	tzo();
	$visitTimeReadable = date('H:i');
		
		// No previous visit. Sign in user.
		$query = sprintf("INSERT INTO newvisits (userid, scanin) VALUES ('%d', '%s');",
		  $user_id, $visitTime);
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$_SESSION['successMessage'] = "Socio entrada: $visitTimeReadable.";
 		header("Location: ../mini-profile.php?user_id={$user_id}");
		exit();
		
