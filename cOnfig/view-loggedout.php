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
  <link rel="stylesheet" href="{$siteroot}css/jquery-ui.css" type="text/css" />
  <link rel="stylesheet" href="{$siteroot}css/dd_signature_pad.css" type="text/css" />	
  <link rel="stylesheet" href="{$siteroot}css/select2.min.css" type="text/css" />
  <link rel="shortcut icon" href="{$siteroot}favicon.ico">
  <link rel="stylesheet" href="{$siteroot}css/jquery.keypad.css" type="text/css">
  <script type="text/javascript" src="{$siteroot}scripts/jquery-1.10.2.min.js"></script>
  <script src="{$siteroot}scripts/select2.min.js"></script>
  <script src="{$siteroot}scripts/jquery.validate.min.js"></script>
  <script src="{$siteroot}scripts/additional-methods.min.js"></script>
  <script src="{$siteroot}scripts/jquery-ui.js"></script>
  <script src="{$siteroot}scripts/webcam.js"></script>
  <script src="{$siteroot}scripts/jquery.tablesorter.min.js"></script>
  <script src="{$siteroot}scripts/jquery.table2excel.js"></script>
  <script type="text/javascript" src="{$siteroot}scripts/jquery.plugin.js"></script> 
  <script type="text/javascript" src="{$siteroot}scripts/jquery.keypad.js"></script>

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
     <br />
     <h2>$pageName</h2>
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