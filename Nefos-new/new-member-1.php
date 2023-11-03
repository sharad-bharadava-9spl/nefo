<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);		

	pageStart("DNI / " . $lang['member-passport'], NULL, NULL, "pprofile", NULL, $lang['member-newmembercaps'] . " - DNI / " . $lang['member-passport'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>

<center><a class="cta" href="dni-upload-1.php?newmember=true"><?php echo $lang['upload-photo']; ?></a> <a class="cta" href="dni-photo-1.php?newmember=true"><?php echo $lang['use-webcam']; ?></a><a class="cta" href="new-member-2.php?skipDNI" style='background-color: red;'><?php echo $lang['skip']; ?></a></center>

<?php
 displayFooter();
?>
