<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the user ID
	$user_id = $_GET['user_id'];
	
	// Lookup user's last visit:
	$lastVisit = "SELECT visitNo, scanin FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
	
	$result = mysql_query($lastVisit)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$visitNo = $row['visitNo'];
		$scanin = $row['scanin'];
		
	$visitTime = date('Y-m-d H:i:s');
	tzo();
	$visitTimeReadable = date('H:i');
	
	// Determine duration
	$minutesOfVisit = round(abs(strtotime($scanin) - strtotime($visitTime)) / 60,2);

	$query = "UPDATE newvisits SET scanout = '$visitTime', duration = $minutesOfVisit, completed = 1 WHERE visitNo = $visitNo";
	
	mysql_query($query)
		or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());

	$_SESSION['errorMessage'] = "Solo has estado en el club 1 minuto! Tienes que estar minimo 15 minutos antes puedes salir.";
	header("Location: ../mini-profile.php?user_id={$user_id}");
	
	exit();