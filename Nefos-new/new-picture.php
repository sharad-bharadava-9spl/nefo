<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	// Lookup the memerno for the user in querstion
	$userDetails = "SELECT memberno FROM users WHERE user_id = '{$user_id}'";
	
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
		$memberno = $row['memberno'];
	

	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", NULL, $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


	if ($_SESSION['cropOrNot'] == '1') {

		echo "<center>
		       <a class='cta' href='new-picture-upload.php?user_id=$user_id&memberno=$memberno'>{$lang['upload-photo']}</a>
		       <a class='cta' href='new-picture-photo.php?user_id=$user_id'>{$lang['use-webcam']}</a>
		      </center>";
		      
	} else {

		echo "<center>
		       <a class='cta' href='new-picture-upload-nocrop.php?user_id=$user_id&memberno=$memberno'>{$lang['upload-photo']}</a>
		       <a class='cta' href='new-picture-photo.php?user_id=$user_id'>{$lang['use-webcam']}</a>
		      </center>";
		      
	}

 displayFooter();