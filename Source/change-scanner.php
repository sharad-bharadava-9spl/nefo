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
	
	// Check if a chip was scanned
	if (isset($_GET['newscanner'])) {
		
		$_SESSION['scanner'] = $_GET['newscanner'];
		
		// On success: redirect.
		header("Location: main.php");
		exit();


	}

	pageStart("CCS", NULL, $testinput, "pindex", "notSelected", $lang['scanner-choose'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	$readers = $_SESSION['iPadReaders'];
	
	echo "<center>";
	
	for ($i = 1; $i <= $readers; $i++) {
		
		echo "<a href='?newscanner=$i'><img src='images/t$i.png' /></a>&nbsp;&nbsp;&nbsp;&nbsp;";
		
	}
	
	if ($_SESSION['lang'] == 'es') {
		echo "<a href='?newscanner=$i'><img src='images/sin-lector.png' /></a>&nbsp;&nbsp;&nbsp;&nbsp;";
	} else {
		echo "<a href='?newscanner=$i'><img src='images/no-reader.png' /></a>&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	echo "</center>";
	
displayFooter();