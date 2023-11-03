<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
	
	pageStart("Subir certificado", NULL, NULL, "pprofile", NULL, "Subir certificado", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>

<center><a class="cta" href="med-upload-new.php?user_id=<?php echo $user_id; ?>"><?php echo $lang['upload-photo']; ?></a> <a class="cta" href="med-photo-new.php?user_id=<?php echo $user_id; ?>"><?php echo $lang['use-webcam']; ?></a><a class="cta" href="profile.php?user_id=<?php echo $user_id; ?>" style='background-color: red;'><?php echo $lang['skip']; ?></a></center>

<?php
 displayFooter();
?>
