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
	
	if (isset($_GET['purchaseid'])) {
		$_SESSION['purchaseid'] = $_GET['purchaseid'];
	} else {
		echo $lang['error-nopurchaseid'];
		exit();
	}

	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", NULL, $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);



		echo "<center>
		       <a class='cta' href='change-image-upload-nocrop.php'>{$lang['upload-photo']}</a>
		       <a class='cta' href='change-image-photo.php'>{$lang['use-webcam']}</a>
		      </center>";
	      

	
 displayFooter();