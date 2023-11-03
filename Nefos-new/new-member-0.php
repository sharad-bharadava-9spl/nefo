<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);		

	pageStart($lang['avalista'], NULL, NULL, "pprofile", NULL, $lang['avalista'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>

<center><a class="cta" href="aval-check.php">1 <?php echo $lang['avalista']; ?></a> <a class="cta" href="aval-check.php?twoavals">2 <?php echo $lang['avalista']; ?>s</a><a class="cta" href="new-member-2.php?skipDNI" style='background-color: red;'><?php echo $lang['no-avalista']; ?></a></center>

<?php
 displayFooter();
?>
