<?php

	
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
  <link href="{$siteroot}css/stylesv6.css" rel="stylesheet" type="text/css" />
  <link href="{$siteroot}css/jquery-ui.css" rel="stylesheet" type="text/css" />
  <script src="{$siteroot}scripts/jquery-1.10.2.min.js"></script>
  <script src="{$siteroot}scripts/jquery.validate.min.js"></script>
  <script src="{$siteroot}scripts/additional-methods.min.js"></script>
  <script src="{$siteroot}scripts/jquery-ui.js"></script>
  <script src="{$siteroot}scripts/webcam.js"></script>
  <script src="{$siteroot}scripts/jquery-pack.js"></script>
  <script src="{$siteroot}scripts/jquery.imgareaselect.min.js"></script>
  
  <script>
  jQuery(document).ready(function($) {
      $(".clickableRow").click(function() {
            window.document.location = $(this).attr("href");
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

			
		while ($row = $result->fetch()) {
			$name = $row['first_name'] . " " . $row['last_name'];
			$userid = $row['user_id'];
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
			}
			
			$langswitch = "<a href='?lang=en&$href'><img src='{$siteroot}images/lang-es.png' style='vertical-align: bottom; margin-left: 15px;' /></a>";
			
		} else {
			
			if ($_GET) {
			    $href .= strpos($href, '?') === false ? '?' : '&';
			    $href .= http_build_query($_GET);
			    $href = substr($href, 1);
			    $href = str_replace('lang=es&', '', $href);
			    $href = str_replace('lang=en&', '', $href);
			}
			
			$langswitch = "<a href='?lang=es&$href'><img src='{$siteroot}images/lang-en.png' style='vertical-align: bottom; margin-left: 15px;' /></a>";
			
		}
			$logoFile = "images/_{$_SESSION['domain']}/logo.png";
	
		if (file_exists($logoFile)) {
			$logoFile ="{$siteroot}images/_{$_SESSION['domain']}/logo.png";
		} else {
			$logoFile ="{$siteroot}images/logo.png";
		}

		if ($_SESSION['domain'] == 'personal') {
			$dispName = $currStation;
		} else {
			$dispName = $_SESSION['first_name'];
		}
		$loginbox = <<<EOD
    <div id="loginbox">
     <a href="{$siteroot}admin.php"><img src="{$siteroot}images/administration.png" style="margin-left: 29px; margin-bottom: 4px;" /></a>
     <a href='#' id='messageSwitch'><img src="{$siteroot}images/icon-new-message.png" id='msgIcon' style="margin-left: 9px; margin-bottom: 4px; opacity: 0.3;" /></a>
     <a href='#' id='warningSwitch'><img src="{$siteroot}images/icon-flame.png" id='wrnIcon' style="margin-left: 9px; margin-bottom: 4px; opacity: 0.3;" /></a>

     <br />
     <img src="{$siteroot}images/user-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;<span id="loggedinname"><strong>{$_SESSION['first_name']}</strong></span> <a href="{$siteroot}uTil/logout-manual.php" class="logout">[Logout]</a> $langswitch
     <br />
	 <img src="{$siteroot}images/time-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;$insertTime
	</div>
EOD;
}

		echo <<<EOD
 <body id="{$bodyID}" class="{$bodyClass}">

  <div id="wrapper">
    <div id="header">
	    <div id="pagecontrols">
	     <div id="icons">
		  $changeWorkstation $scannerImg $fingerbutton $hotWarning
	     </div>
	     <br />
	     <div id="controls">
	     <img src="{$siteroot}images/clock.png" style="margin-bottom: -2px;" />&nbsp;$insertTime
		 $trialLeft
		 &nbsp;&nbsp;<span id="loggedinName"><img src="{$siteroot}images/user.png" style="margin-bottom: -2px;" /> $dispName <img src="{$siteroot}images/flecha.png"  style="margin-bottom: 2px; margin-left: 2px;" /></span> $langswitch <a href='admin.php'><img src='{$siteroot}images/admin.png' style='vertical-align: bottom; margin-left: 15px;' /></a> <a href='uTil/logout-manual.php'><img src='{$siteroot}images/logout.png' style='vertical-align: bottom; margin-left: 15px;' /></a>
		 <div id="stafflist">
	     {$listcontent}
	     </div>
	     </div>
	     <a href="index.php" id="logo"><img src="$logoFile" /></a>
	   </div>
    <!-- <div id="stafflist">
     {$listcontent}
     </div>
     <div id="messagelist">
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
     </div>
     $loginbox -->
    <!-- <a href="{$siteroot}{$currSite}" id="logo"><img src="{$siteroot}images/logo.png" /></a><br />
     <h2>$pageName</h2>  -->
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
    <div id="titlearea">
     <h2>$pageName</h2>    
    </div> <!-- end titlearea -->
EOD;


// Auto-logout
getSettings();

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
   </div> <!-- end MAIN -->
  </div> <!-- end WRAPPER -->
 </body>
</html>
EOD;
	}