<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	pageStart($lang['title-dispense'], NULL, $memberScript, "psales", "dispensepre", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

?>

<center>
 <span class="ctalinks">
  <a href="order-1.php?ot=1"><span id="dispenseCTA"></span><br /><?php if ($_SESSION['lang'] == 'es') { echo "Entrega"; } else { echo "Delivery"; } ?><br />&nbsp;</a>
  <a href="order-1.php?ot=2"><span id="dispenseCTA"></span><br /><?php if ($_SESSION['lang'] == 'es') { echo "Recogida"; } else { echo "Collection"; } ?><br />&nbsp;</a>
</center>
<?php  displayFooter();
