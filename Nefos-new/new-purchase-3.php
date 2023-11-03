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
	
	$purchaseid = $_SESSION['purchaseid'];

	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", NULL, $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	if ($_SESSION['cropOrNot'] == '1') {

	echo "<center>
	       <a class='cta' href='new-purchase-upload-nocrop.php'>{$lang['upload-photo']}</a>
	       <a class='cta' href='new-purchase-photo.php'>{$lang['use-webcam']}</a>
	       <a class='cta red' href='purchase.php?purchaseid=$purchaseid'>{$lang['skip']}</a>
	      </center>";
	      
	} else {

	echo "<center>
	       <a class='cta' href='new-purchase-upload-nocrop.php'>{$lang['upload-photo']}</a>
	       <a class='cta' href='new-purchase-photo.php'>{$lang['use-webcam']}</a>
	       <a class='cta red' href='purchase.php?purchaseid=$purchaseid'>{$lang['skip']}</a>
	      </center>";
	      
	}



 displayFooter();