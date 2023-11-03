<?php

	//require_once 'functions.php';
	//getSettings();
	
/******* HIGH-LEVEL FUNCTIONS ********/
	
	function displayMessages($successmsg, $errormsg) {
		
		if (($errormsg) || ($successmsg)) {
			
		if (isset($_SESSION['user_id']) || isset($_POST['email'])) {
				
			echo "<center><div id='scriptMsg'>\n";
			if (!is_null($successmsg) && (strlen($successmsg) > 0)) {
				displayMessage($successmsg, MESSAGESUCCESS);
			}
			if (!is_null($errormsg) && (strlen($errormsg) > 0)) {
				displayMessage($errormsg, MESSAGEERROR);
			}
			echo "</div></center>\n\n";
		
		}
		$_SESSION['successMessage'] = '';
		$_SESSION['errorMessage'] = '';
		
		
		// $_SESSION['errorMessage'] = 'Hemos observado que no estais usando algunas funcionalidades del software. Asimismo quer?amos aclarar la facturación.<br />Para evitar posibles interrupciones en el servicio, por favor pónganse en contacto con<br />nosotros <u>lo antes posible</u> en <a href="mailto:andreas@cannabisclub.systems" style="color: #ffff00 !important;">andreas@cannabisclub.systems</a> o 644 744 497.';

		// $_SESSION['errorMessage'] = 'Tiene factura(s) pendiente(s) del pago. Para evitar la desactivación de su servicio, realice el pago inmediatamente.<br />El acceso al sistema se desactivar&aacute; el <span style="color: #ffff00 !important;">16/02/2018</span>!<br />Para cualquier duda, contactenos en <a href="mailto:facturacion@cannabisclub.systems" style="color: #ffff00 !important;">facturacion@cannabisclub.systems</a>';
		
		// $_SESSION['errorMessage'] = 'You have an unpaid invoice! If this invoice is not paid immediately,<br />you will <u>lose access</u> to the CCS servers on <span style="color: #ffff00 !important;">15/11/2016</span>!<br />Please get in touch with us at <a href="mailto:facturacion@cannabisclub.systems" style="color: #ffff00 !important;">facturacion@cannabisclub.systems</a>';

		// $_SESSION['errorMessage'] = '<span style="font-size: 26px; color: yellow;">***** &Uacute;ltimo aviso! *****</span><br /><br />Tiene factura(s) pendiente(s) del pago. Si no recibimos justificante de pago hoy, vas a perder acceso al programa!<br />Para cualquier duda, contactenos en <a href="mailto:facturacion@cannabisclub.systems" style="color: #ffff00 !important;">facturacion@cannabisclub.systems</a>';

}

	}
	
	function pageStart($pageTitle, $meta = NULL, $embeddedJS = NULL, $bodyID, $bodyClass = NULL, $pageName, $successMsg = NULL, $errorMsg = NULL) {
		displayHead($pageTitle, $meta, $embeddedJS);
		displayHeader($bodyID, $bodyClass, $pageName, $_SESSION['user_id']);
		displayMain($pageName, $successMsg, $errorMsg);
	}

	
/******* INDIVIDUAL FUNCTIONS ********/
	function displayMessage($msg, $msgType) {
		echo "<div class='{$msgType}'>\n";
		echo " <p>{$msg}</p>\n";
		echo "</div>\n";
	}
	
	
/******* HEREDOC FUNCTIONS ********/

	// Create HTML <head> section
	function displayHead($pageTitle = "", $meta = NULL, $embeddedJS = NULL) {
		
		global $siteroot;
		
		echo <<<EOD
<!DOCTYPE html> 
<html>
 <head>
  <title>{$pageTitle}</title>
  <link rel="stylesheet" href="{$siteroot}css/stylesv4.css" type="text/css" />
  <link rel="stylesheet" href="{$siteroot}css/jquery-ui.css" type="text/css" />
  <link rel="stylesheet" href="{$siteroot}css/dd_signature_pad.css" type="text/css" />	
  <link rel="stylesheet" href="{$siteroot}css/select2.min.css" type="text/css" />
  <link rel="shortcut icon" href="{$siteroot}favicon.ico">
  <link rel="stylesheet" href="{$siteroot}css/jquery.numpad.css" type="text/css">
  <script type="text/javascript" src="{$siteroot}scripts/jquery-1.10.2.min.js"></script>
  <script src="{$siteroot}scripts/select2.min.js"></script>
  <script type="text/javascript" src="{$siteroot}scripts/jquery.numpad.js"></script>
  <script src="{$siteroot}scripts/jquery.validate.min.js"></script>
  <script src="{$siteroot}scripts/additional-methods.min.js"></script>
  <script src="{$siteroot}scripts/jquery-ui.js"></script>
  <script src="{$siteroot}scripts/webcam.js"></script>
  <script src="{$siteroot}scripts/jquery.tablesorter.min.js"></script>
  <script src="{$siteroot}scripts/jquery.table2excel.js"></script>
  

  <script>
  jQuery(document).ready(function($) {
	  
      $(".clickableRow").click(function() {
            window.document.location = $(this).attr("href");
      });
      
      $(".clickableRowNew").click(function() {
		window.open(
		  $(this).attr("href"),
		  '_blank' // <- This is what makes it open in a new window.
		);
      });
      
      



      
$('.noEnterSubmit').keypress(function(e){
    if ( e.which == 13 ) return false;
    //or...
    if ( e.which == 13 ) e.preventDefault();
});
      
});
  </script>
  
EOD;
	if (!is_null($meta)) {
		echo $meta;
	}
	if (!is_null($embeddedJS)) {
		echo "<script>" . $embeddedJS . "</script>";
	}
	echo "</head>";
	}
		// Create Header element - inc. menu
	function displayHeader($bodyID, $bodyClass = NULL, $pageName) {
		
		global $siteroot;
		

	

		echo <<<EOD
 <body id="{$bodyID}" class="{$bodyClass}">

  <div id="wrapper">
    <div id="header">
     <div id="stafflist">
     {$listcontent}
     </div>

     $loginbox
     <a href="{$siteroot}{$currSite}" id="logo"><img src="{$siteroot}images/logo.png" /></a><br />
     <h2>$pageName</h2>
      <script>
    	$("#loggedinname").click(function () {
		$('#stafflist').toggle();
		});	
		
    	$("#messageSwitch").click(function () {
		$("#messagelist2").toggle();
		$("#msgIcon").css("opacity", "0.3");
		});	
		
    	$("#warningSwitch").click(function () {
		$("#messagelist").toggle();
		$("#wrnIcon").css("opacity", "0.3");
		});	
	 </script>
    </div> <!-- end HEADER -->
EOD;


}

	function displayMain($pageName, $successMsg = NULL, $errorMsg = NULL) {

		
   		echo "<div id='main'>";
   		

		displayMessages($successMsg, $errorMsg);
		
	}

	
	function displayFooter() {
		echo <<<EOD
		<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;
   </div> <!-- end MAIN -->
  </div> <!-- end WRAPPER -->
 </body>
</html>
EOD;
	}