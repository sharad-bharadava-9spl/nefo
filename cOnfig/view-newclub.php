<?php

	require_once 'functions.php';
	
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
  <link rel="stylesheet" href="{$siteroot}css/stylesv6.css" type="text/css" />
  <link rel="stylesheet" href="{$siteroot}css/dev.css" type="text/css" />
  <script type="text/javascript" src="{$siteroot}scripts/jquery-1.10.2.min.js"></script>
  <script src="{$siteroot}scripts/jquery.validate.min.js"></script>
  <script src="{$siteroot}scripts/additional-methods.min.js"></script>
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
		$lang = $_SESSION['lang']; 
			$http_protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			 
			  $url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 

			 if($_SERVER['HTTP_HOST'] == '192.168.0.41'){
			 	 $protocol = 'http://';
			 }else{
			 	$protocol = "https://";
			 }
			  $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 

			  $club_launch_arr = array($siteroot.'launch/', $siteroot.'launch/index.php', $siteroot.'launch/new-club-1.php?lang='.$lang, $siteroot.'launch/new-club-6.php' );

			  $demo_pos  = strpos($url, "demo.php");
			  $demo1_pos  = strpos($url, "demo-1.php");
			  $demo2_pos  = strpos($url, "demo-2.php");
			 
			if(!isset($_SESSION['temp_id']) && !in_array($url, $club_launch_arr) && $demo_pos == '' && $demo1_pos == '' && $demo2_pos == ''){
				
				header("location:".$siteroot."launch/");
				exit();
			}
		echo <<<EOD
 <body id="{$bodyID}" class="{$bodyClass}">
  <div id="wrapper">
    <div id="header">

     <div id="pagecontrols">
     	 <center><img src="{$siteroot}images/logo.png" style="margin-top: -22px;" /></center><br />
     </div>

     $loginbox
     <br />
    
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