<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$scanSearch = "SELECT chip FROM newscan WHERE type = 1 ORDER BY scanid DESC LIMIT 1";
	
	$result = mysql_query($scanSearch)
		or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());

	if (mysql_num_rows($result) == 0) {
		
		echo 'false';
		
	} else {
		
		$row = mysql_fetch_array($result);
			$chip = $row['chip'];
			
		$userLookup = "SELECT user_id FROM users WHERE cardid = $chip";
		
		$result = mysql_query($userLookup)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$user_id = $row['user_id'];
			
		$deleteScans = "DELETE FROM newscan WHERE type = 1";
		
		mysql_query($deleteScans)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());

		echo $user_id;
		
	}