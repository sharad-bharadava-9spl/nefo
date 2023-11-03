<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	pageStart("CCS", NULL, $testinput, "pindex", "notSelected", NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	// Types: 0 = text, 1 = contact update, 2 = calendar invite, 3 = New invoice, 4 = Software update, 5 = help center ticket, 6 = Stock notification

		
?>

<center>
<strong style='font-size: 18px;'>What type of notification do you wish to send?</strong><br /><br /><br />
 <span class="ctalinks">
 <a href="notification-send-2.php?type=0"><span id=""></span><br /><br />TEXT ONLY</a>
 <a href="notification-send-2.php?type=1"><span id=""></span><br /><br />CONTACT UPDATE</a>
<!-- <a href="notification-send-2.php?type=3"><span id=""></span><br />NEW INVOICE<BR />&nbsp;</a>
 <a href="notification-send-2.php?type=4"><span id=""></span><br />SW UPDATE<BR />&nbsp;</a>-->
 </span>
</center>