<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Generate random temporary membership number, to use throughout the process.
	$tempNo = "_" . generateRandomString();
	$_SESSION['tempNo'] = $tempNo;
	
	if (isset($_GET['aval'])) {
		$_SESSION['aval'] = $_GET['aval'];
	}
	if (isset($_GET['aval2'])) {
		$_SESSION['aval2'] = $_GET['aval2'];
	}

	pageStart($lang['signature'], NULL, $validationScript, "pprofile", "statutes", $lang['signature'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	echo "<center>
           <a class='cta' href='new-member-upload.php'>{$lang['upload-photo']}</a>
           <a class='cta' href='new-member-new.php'>{$lang['sign-contract']}</a>
          </center>";
