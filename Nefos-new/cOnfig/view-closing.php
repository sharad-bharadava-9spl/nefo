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

// Delete dispense no funciona!!


	// Create HTML <head> section
	function displayHead($pageTitle = "", $meta = NULL, $embeddedJS = NULL) {
		
		global $siteroot;
		
		echo <<<EOD
<!DOCTYPE html> 
<html>
 <head>
  <title>{$pageTitle}</title>
  <link href="{$siteroot}css/styles15.css" rel="stylesheet" type="text/css" />
  <link href="{$siteroot}css/jquery-ui.css" rel="stylesheet" type="text/css" />
  <script src="{$siteroot}scripts/jquery-1.10.2.min.js"></script>
  <script src="{$siteroot}scripts/jquery.validate.min.js"></script>
  <script src="{$siteroot}scripts/additional-methods.min.js"></script>
  <script src="{$siteroot}scripts/jquery-ui.js"></script>
  <script src="{$siteroot}scripts/webcam.js"></script>
  <script src="{$siteroot}scripts/SigWebTablet.js"></script>
  


<script type="text/javascript">
window.onunload = window.onbeforeunload = (function(){
closingSigWeb()
})

function closingSigWeb()
{
   ClearTablet();
   SetTabletState(0, tmr);
}

</script>

  

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
		
	// Only run the login menu if there's a user actually logged in!
	if (isset($_SESSION['user_id'])) {
			
	$loggedInUser = $_SESSION['user_id'];
	tzo();
	$insertTime = date('jS F Y H:i');
	$selectUsers = "SELECT user_id, memberno, first_name, last_name, userGroup FROM users WHERE userGroup < 4 AND user_id <> $loggedInUser";
	$resultX = mysql_query($selectUsers)
		or handleError("Error loading users from database.","Error loading users from db: " . mysql_error());

		$y = 0;
while ($user = mysql_fetch_array($resultX)) {
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
			}
			
			$langswitch = "<a href='?lang=en&$href'><img src='{$siteroot}images/es.png' /></a>";
			
		} else {
			
			if ($_GET) {
			    $href .= strpos($href, '?') === false ? '?' : '&';
			    $href .= http_build_query($_GET);
			    $href = substr($href, 1);
			    $href = str_replace('lang=es&', '', $href);
			    $href = str_replace('lang=en&', '', $href);
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
	
		$loginbox = <<<EOD
    <div id="loginbox">
     <a href="{$siteroot}admin.php"><img src="{$siteroot}images/administration.png" style="margin-left: 29px; margin-bottom: 4px;" /></a>
     {$changeWorkstation}
     
     <br />
     <img src="{$siteroot}images/user-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;<span id="loggedinname"><strong>{$_SESSION['first_name']}</strong></span> <a href="{$siteroot}uTil/logout.php" class="logout">[Logout]</a> $langswitch
     <br />
	 <img src="{$siteroot}images/time-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;$insertTime
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
     <div id="messagelist">
      <table>
       <tr>
        <td style="vertical-align: middle; padding-bottom: 10px;"><img src="images/face1.png" style="margin-right: 5px;" /></td>
        <td style="vertical-align: top; padding-bottom: 10px;"><span  style="color: #5aa242;"><strong>Andy N.</strong><br />
        Por favor, puedes chequear el peso de White Widow?</span></td>
       </tr>
       <tr>
        <td style="vertical-align: middle; padding-bottom: 10px;"><img src="images/face2.png" style="margin-right: 5px;" /></td>
        <td style="vertical-align: top; padding-bottom: 10px;"><strong>Mar&iacute;a L.</strong><br />
        Hola, necesitamos ayuda en el recepcion!</td>
       </tr>
       <tr>
        <td style="vertical-align: middle;"><img src="images/face3.png"  style="margin-right: 5px;"/></td>
        <td style="vertical-align: top;"><strong>Santos D.</strong><br />
        Ma&ntilde;ana llega el tecnico de Movistar a las 11:00.</td>
       </tr>
      </table>
     </div>-->
     $loginbox
     <a href="{$siteroot}{$currSite}" id="logo"><img src="{$siteroot}images/logo.png" /></a><br />
     <h2>$pageName</h2>
      <script>
    	$("#loggedinname").click(function () {
		$('#stafflist').toggle();
		});	
	 </script>
    </div> <!-- end HEADER -->
EOD;


}
/*
	// Create Header element - inc. menu
	function displayHeader($bodyID, $bodyClass = NULL) {
		echo <<<EOD
 <body id="{$bodyID}" class="{$bodyClass}">
  <div id="wrapper">
    <div id="header">
EOD;
	if (isset($_SESSION['user_id'])) {
		
		// Admin menu visible?
    	if (userInGroup($_SESSION['user_id'], "Administrators")) {
			echo <<<EOD
     <ul>
     <li>Admin menu:</li>
      <a href="users.php" id="lusers"><li>Users</li></a>
     </ul><br />
     <div class="clearFloat"></div>
EOD;
		}

		echo <<<EOD
     <ul>
     <li>My user:</li>
      <a href="main.php" id="lindex"><li>Home</li></a>
      <a href="profile.php" id="lprofile"><li>Profile</li></a>
      <a href="schedule.php" id=""><li>Schedule</li></a>
      <a href="uTil/logout.php"><li>Logout</li></a>
     </ul><br />
     <div class="clearFloat"></div>
     <ul>
     <li>My company:</li>
      <a href="company.php" id="linvoices"><li>Company</li></a>
      <a href="invoices.php" id="linvoices"><li>Invoices</li></a>
      <a href="customers.php" id="lcustomers"><li>Customers</li></a>
      <a href="commenting.php" id=""><li>Comments</li></a>
     </ul><br />
     <div class="clearFloat"></div>
     <ul>
     <li>[ Non-menu links:</li>
      <a href="register.php"><li>Register</li></a>
      <a href="#"><li>Forgot password</li></a>
      <a href="#"><li>Save</li></a>
      <a href="#"><li>Mail</li></a> ]
     </ul>
EOD;
}
	echo <<<EOD
     <a href="main.php"><h1>Language Cloud</h1></a>
     
    </div> <!-- end HEADER -->
EOD;
}
*/

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

 
?>
