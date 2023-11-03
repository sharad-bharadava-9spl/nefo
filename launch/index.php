<?php
	
	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/languages/common.php';
	require_once '../cOnfig/view-newclub.php';
	
	session_start();

	pageStart("CCS", NULL, NULL, "pprofile", NULL, "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

<div id='mainbox-new-club'>
 <div id='mainboxheader'>
  <center>
   Elige idioma / Choose language<br />
  </center>
 </div>
 <div class='boxcontent'>
  <center>
   <a class="cta1" href="new-club-1.php?lang=es">Español</a>
   <a class="cta1" href="new-club-1.php?lang=en">English</a>
  </center>
 </div>
</div>



<?php
 displayFooter();
?>
