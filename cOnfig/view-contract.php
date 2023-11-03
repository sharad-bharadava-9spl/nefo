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
<html moznomarginboxes mozdisallowselectionprint>
 <head>
  <title>{$pageTitle}</title>
  <link href="{$siteroot}css/stylesv6.css" rel="stylesheet" type="text/css" />
  <link href="{$siteroot}css/jquery-ui.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="{$siteroot}scripts/jquery-1.10.2.min.js"></script>
  <script src="{$siteroot}scripts/jquery.validate.min.js"></script>
  <script src="{$siteroot}scripts/additional-methods.min.js"></script>
  <script src="{$siteroot}scripts/jquery-ui.js"></script>
  <script src="{$siteroot}scripts/webcam.js"></script>
  <script src="{$siteroot}scripts/jquery.tablesorter.min.js"></script>
  <script src="{$siteroot}scripts/jquery.table2excel.js"></script>
  <style>
   @page { margin: 0; }
   body { margin: 5px; }
  </style>
  

EOD;
		// Create Header element - inc. menu
	function displayHeader($bodyID, $bodyClass = NULL, $pageName) {
		
		global $siteroot;
		
}
	


		echo <<<EOD
 <body id="{$bodyID}" class="{$bodyClass}">

  <div id="wrapper">
    <div id="header">
     <div id="stafflist">
     {$listcontent}
     </div>
     <center><img src="{$siteroot}images/logo.png" /></center><br />
    </div> <!-- end HEADER -->
EOD;

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