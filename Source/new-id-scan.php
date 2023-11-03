<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);		

	pageStart("DNI", NULL, NULL, "pprofile", NULL, "Actualizar DNI", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$user_id = $_GET['user_id'];

?>

<center><a class="cta" href="dni-update-1.php?user_id=<?php echo $user_id; ?>">Subir foto</a> <a class="cta" href="dni-photo-1.php?user_id=<?php echo $user_id; ?>">Usar webcam</a></center>

<?php
 displayFooter();
?>
