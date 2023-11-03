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
	
	unset($_SESSION['aval']);
	unset($_SESSION['aval2']);


?>

<div id='progress'>
 <div id='progressinside1'>
 </div>
</div>
<br />
 <div id='progresstext1'>
 1. <?php echo $lang['avalista']; ?>
 </div>
 
 <div id='mainbox'>
  <div id='mainboxheader'>
  Añadir avalista para el nuevo socio (1 / 3)
  </div>
 <br />
<center>
<a class="cta1" href="aval-check.php">1 <?php echo $lang['avalista']; ?></a>
<a class="cta2" href="aval-check.php?twoavals">2 <?php echo $lang['avalista']; ?>s</a>
<a class="cta3" href="new-member-new.php?noaval"><?php echo $lang['no-avalista']; ?></a></center>
 <br />
 
 </div>



<?php
 displayFooter();
?>
