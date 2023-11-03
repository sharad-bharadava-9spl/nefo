<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	if (isset($_GET['productid'])) {
		$_SESSION['productid'] = $_GET['productid'];
	} else {
		echo $lang['error-noproductspecified'];
		exit();
	}
	
	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", NULL, $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	if ($_SESSION['cropOrNot'] == '1') {
		
		echo "<center>
		       <a class='cta' href='bar-change-image-upload.php'>{$lang['upload-photo']}</a>
		       <a class='cta' href='bar-change-image-photo.php'>{$lang['use-webcam']}</a>
		      </center>";
		      
	} else {
		
		echo "<center>
		       <a class='cta' href='bar-change-image-upload-nocrop.php'>{$lang['upload-photo']}</a>
		       <a class='cta' href='bar-change-image-photo.php'>{$lang['use-webcam']}</a>
		      </center>";
		      
	}
	
 displayFooter();