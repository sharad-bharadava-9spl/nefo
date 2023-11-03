<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	pageStart("CCS", NULL, $testinput, "pindex", "notSelected", NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

		
?>

<center>
 <span class="ctalinks">
 <a href="bar-new-sale.php"><span id="hwCTA"></span><br />HW SALE</a>
 <a href="new-call.php"><span id="callCTA"></span><br />NEW CALL</a><br />
 <a href="clients.php"><span id="membersCTA"></span><br />CLIENTS</a>
 <a href="new-client.php"><span id="newmemberCTA"></span><br />NEW CLIENT</a><br />
 </span>
</center>