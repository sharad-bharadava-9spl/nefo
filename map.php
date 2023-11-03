<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';

	
	pageStart("Dabulance Map", NULL, $testinput, "pindex", "loggedIn", "Dabulance map", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center><iframe src="https://www.google.com/maps/d/u/3/embed?mid=1NeffOSaBeBK8uDUMFXrZei92qYR4qkeL" width="1024" height="480"></iframe></center>
