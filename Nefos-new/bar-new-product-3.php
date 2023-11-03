<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$productid = $_SESSION['productid'];

	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", NULL, $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	if ($_SESSION['cropOrNot'] == '1') {

		echo "<center>
	           <a class='cta' href='bar-new-product-upload.php'>{$lang['upload-photo']}</a>
	           <a class='cta' href='bar-new-product-photo.php'>{$lang['use-webcam']}</a>
	           <a class='cta red' href='bar-products.php'>{$lang['skip']}</a>
	          </center>";
          
	} else {
	
		echo "<center>
	           <a class='cta' href='bar-new-product-upload-nocrop.php'>{$lang['upload-photo']}</a>
	           <a class='cta' href='bar-new-product-photo.php'>{$lang['use-webcam']}</a>
	           <a class='cta red' href='bar-products.php'>{$lang['skip']}</a>
	          </center>";
          
	}

 displayFooter();