<?php
	// created by konstant for Task-14980311 on 06-12-2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	


pageStart('Generate Members QR', NULL, $validationScript, "pprofile", "final", 'Generate Members QR', $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<center>
	<a href='members-qr-process.php?count=0&totalCount=0' class='cta1' style="width:auto;">Generate Members QR</a>
</center>

<br />
<br />


<?php displayFooter();