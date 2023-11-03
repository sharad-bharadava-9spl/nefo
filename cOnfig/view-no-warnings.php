<?php

	require_once 'functions.php';
	getSettings();
	
/******* HIGH-LEVEL FUNCTIONS ********/
	
	function displayMessages($successmsg, $errormsg) {
		
		if (($errormsg) || ($successmsg)) {
		echo "<center><div id='scriptMsg'>\n";
		if (!is_null($successmsg) && (strlen($successmsg) > 0)) {
			displayMessage($successmsg, MESSAGESUCCESS);
		}
		if (!is_null($errormsg) && (strlen($errormsg) > 0)) {
			displayMessage($errormsg, MESSAGEERROR);
		}
		echo "</div></center>\n\n";
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
		global $pdo3;
		
		echo <<<EOD
<!DOCTYPE html> 
<html>
 <head>
  <title>{$pageTitle}</title>
  <link rel="stylesheet" href="{$siteroot}css/stylesv6.css" type="text/css" />
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
		global $pdo3;
		
	// Only run the login menu if there's a user actually logged in!
	if (isset($_SESSION['user_id'])) {
		
	$loggedInUser = $_SESSION['user_id'];
	tzo();
	$insertTime = date('jS F Y H:i');
		try
		{
			$result = $pdo3->prepare("SELECT user_id, memberno, first_name, last_name, userGroup FROM users WHERE userGroup < 4 AND user_id <> :loggedInUser");
			$result->bindValue(':loggedInUser', $loggedInUser);
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			

		$y = 0;
while ($user = $result->fetch()) {
	$name = $user['first_name'] . " " . $user['last_name'];
	$userid = $user['user_id'];
	
    $listcontent .= "<a href='{$siteroot}change-user.php?loggedinuser=$userid'>$name</a><br />";
}
}
	

	if (isset($_SESSION['user_id'])) {
		
		if ($_SESSION['lang'] == 'es') {
			
			if ($_GET) {
			    $href .= strpos($href, '?') === false ? '?' : '&';
			    $href .= http_build_query($_GET);
			    $href = substr($href, 1);
			    $href = str_replace('lang=es&', '', $href);
			    $href = str_replace('lang=en&', '', $href);
			    $href = str_replace('lang=es', '', $href);
			    $href = str_replace('lang=en', '', $href);
			}
			
			$langswitch = "<a href='?lang=en&$href'><img src='{$siteroot}images/es.png' /></a>";
			
		} else {
			
			if ($_GET) {
			    $href .= strpos($href, '?') === false ? '?' : '&';
			    $href .= http_build_query($_GET);
			    $href = substr($href, 1);
			    $href = str_replace('lang=es&', '', $href);
			    $href = str_replace('lang=en&', '', $href);
			    $href = str_replace('lang=es', '', $href);
			    $href = str_replace('lang=en', '', $href);
			}
			
			$langswitch = "<a href='?lang=es&$href'><img src='{$siteroot}images/gb.png' /></a>";
			
		}
		
		if ($_SESSION['workstation'] == 'reception') {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/status-reception.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>"; 
		} else if ($_SESSION['workstation'] == 'bar') {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/status-bar.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>"; 
		} else if ($_SESSION['workstation'] == 'dispensary') {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/status-dispensary.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
		} else {
			$changeWorkstation = "";
		}
		
		// Look for Tablet readers
		if ($_SESSION['iPadReaders'] > 0) {
			
			if ($_SESSION['scanner'] == 1) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/1.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 2) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/2.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 3) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/3.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 4) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/4.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 5) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/5.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 6) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/6.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 7) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/7.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 8) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/8.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 9) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/9.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 10) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/10.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			}
			
		} else {
			
			$scannerImg = "";
			
		}
			
		// Trial trigger
		if ($_SESSION['trialMode'] == 1) {
			
			// Calculate trial time left
			$loginLookup = "SELECT time FROM logins WHERE success = 1 ORDER BY time ASC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$loginLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$loginTime = date("Y-m-d", strtotime($row['time']));
			
			$now = date("Y-m-d");
			
			$datediff = round((strtotime($now) - strtotime($loginTime)) / (60 * 60 * 24));
			
			$remainingTrial = 30 - $datediff;
			
			if ($_SESSION['lang'] == 'es') {
				$trialLeft = "<br /><img src='{$siteroot}images/trial.png' style='margin-bottom: -2px' /><span style='color: red;'>&nbsp;&nbsp;Mes sin compromiso:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Quedan <strong>$remainingTrial</strong> días</span>";
			} else {
				$trialLeft = "<br /><img src='{$siteroot}images/trial.png' style='margin-bottom: -2px' /><span style='color: red;'>&nbsp;&nbsp;Trial: <strong>$remainingTrial</strong> days remaining</span>";
			}
			$loginbox = "<div id='loginbox' style='top: 0;'>";
			
		} else {
			
			$loginbox = "<div id='loginbox'>";
			
		}
		
		
			$loginbox .= <<<EOD
    
     <a href="{$siteroot}admin.php"><img src="{$siteroot}images/administration.png" style="margin-left: 29px; margin-bottom: 4px;" /></a>
     {$changeWorkstation} $scannerImg 

     <br />
     <img src="{$siteroot}images/user-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;<span id="loggedinname"><strong>{$_SESSION['first_name']}</strong></span> <a href="{$siteroot}uTil/logout-manual.php" class="logout">[Logout]</a> $langswitch
     <br />
	 <img src="{$siteroot}images/time-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;$insertTime
	 $trialLeft
	</div>
EOD;
}

		echo <<<EOD
 <body id="{$bodyID}" class="{$bodyClass}">

  <div id="wrapper">
    <div id="header">
     <div id="stafflist">
     {$listcontent}
     </div>
     <!--<div id="messagelist">
      <table>
       <tr>
        <td style="vertical-align: middle;"><img src="images/bell.png" style="margin-right: 5px;" /></td>
        <td style="vertical-align: top;">Solo quedan 10 gramos de Amnesia Haze!<br />
     Contacta <a href="#">el distribudor</a> ahora.</td>
       </tr>
      </table>
     
     </div>
     <div id="messagelist2">
      <table>
       <tr>
        <td style="vertical-align: middle; padding-bottom: 10px;"><img src="images/face1.png" style="margin-right: 5px;" /></td>
        <td style="vertical-align: top; padding-bottom: 10px;"><span  style="color: #5aa242;"><strong>Andy N.</strong> @ 11:35<br />
        Por favor, puedes chequear el peso de White Widow?</span></td>
       </tr>
       <tr>
        <td style="vertical-align: middle; padding-bottom: 10px;"><img src="images/face2.png" style="margin-right: 5px;" /></td>
        <td style="vertical-align: top; padding-bottom: 10px;"><strong>Mar&iacute;a L.</strong> @ Sab. 20:18<br />
        Hola, necesitamos ayuda en el recepcion!</td>
       </tr>
       <tr>
        <td style="vertical-align: middle;"><img src="images/face3.png"  style="margin-right: 5px;"/></td>
        <td style="vertical-align: top;"><strong>Santos D.</strong> @ Sab. 16:55<br />
        Ma&ntilde;ana llega el tecnico de Movistar a las 11:00.</td>
       </tr>
      </table>
     </div>-->
     <a href='reg.php'><img src="{$siteroot}images/logo.png" /></a><br /><br />
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
    </div> <!-- end HEADER --><br /><br /><br /><br />
EOD;

// Auto-logout

if ($_SESSION['logouttime'] > 0) {
	
	$logoutAfter = $_SESSION['logouttime'] * 60000;
	
	if ($_SESSION['logoutredir'] == 1) {
		
		$logoutLink = "{$siteroot}uTil/logout-redir.php";
		
	} else {
		
		$logoutLink = "{$siteroot}uTil/logout-manual.php";
		
	}
	// logout code goes here
	
if (isset($_SESSION['user_id'])) {
	
	echo <<<EOD
<script>
idleTimer = null;
idleState = false;
idleWait = {$logoutAfter};

(function ($) {

    $(document).ready(function () {
    
        $('*').bind('mousemove keydown scroll', function () {
        
            clearTimeout(idleTimer);
                    
            idleState = false;
            
            idleTimer = setTimeout(function () { 
                
                // Idle Event
                window.location.replace("{$logoutLink}");

                idleState = true; }, idleWait);
        });
        
        $("body").trigger("mousemove");
    
    });
}) (jQuery)
</script>
EOD;
}
}
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