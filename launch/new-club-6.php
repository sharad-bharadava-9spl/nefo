<?php
	
	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/view-newclub.php';
	require_once '../cOnfig/languages/common.php';
	
    session_start();

    if(!isset($_SESSION['step2']) || !isset($_SESSION['step3'])){
        header("location:new-club-1.php");
        die;
     }

	pageStart("CCS", NULL, NULL, "pprofile", NULL, "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

if ($_SESSION['lang'] == 'es') { ?>

<div id='mainbox-new-club'>
 <div id='mainboxheader'>
  <center>
   Gracias!<br />
  </center>
 </div>
 <div class='boxcontent'>
  <center>
   Gracias por tu solicitud!<br />
   Pongamos en contacto en breve.<br /><br />
   Saludos,<br />
   El equipo CCS
  </center>
 </div>
</div>



<?php } else {	?>

<div id='mainbox-new-club'>
 <div id='mainboxheader'>
  <center>
   Thank you!<br />
  </center>
 </div>
 <div class='boxcontent'>
  <center>
   Thank you for your application!<br />
   We will be in touch shortly.<br /><br />
   All the best,<br />
   The CCS team
  </center>
 </div>
</div>



<?php

}

 displayFooter();
?>
