<?php

	require_once 'functions.php';
	require_once 'languages/common.php';
	
	getSettings();

	
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
		
		global $pdo3;
		global $lang;



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
		
		$domain = $_SESSION['domain'];
		
		$ctime = time();
		
		$cssfile = "<link rel='stylesheet' href='{$siteroot}css/stylesv6.css?t=$ctime' type='text/css' />";
		
		$ctime = time();
		echo <<<EOD
<!DOCTYPE html> 
<html>
 <head>
  <title>{$pageTitle}</title>
  $cssfile
  <link rel="stylesheet" href="{$siteroot}css/dev.css?t={$ctime}" type="text/css" />
  <link rel="stylesheet" href="{$siteroot}css/jquery-ui.css" type="text/css" />
  <link rel="stylesheet" href="{$siteroot}css/dd_signature_pad.css" type="text/css" />	
  <link rel="stylesheet" href="{$siteroot}css/select2.min.css" type="text/css" />
  <link rel="shortcut icon" href="{$siteroot}favicon.ico">
  <link rel="stylesheet" href="{$siteroot}css/jquery.keypad.css" type="text/css">
  <link rel="stylesheet" href="{$siteroot}css/jquery.ui.timepicker.css" type="text/css">  
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="{$siteroot}css/jquery-ui-timepicker-addon.min.css" type="text/css">  
  <script type="text/javascript" src="{$siteroot}scripts/jquery-1.10.2.min.js"></script>
  <script src="{$siteroot}scripts/select2.min.js"></script>
  <script src="{$siteroot}scripts/jquery.validate.min.js"></script>
  <script src="{$siteroot}scripts/additional-methods.min.js"></script>
  <script src="{$siteroot}scripts/jquery-ui.js"></script>
  <script type="text/javascript" src="{$siteroot}scripts/jquery-ui-timepicker-addon.min.js"></script>
  <script src="{$siteroot}scripts/webcam.js"></script>
  <script src="{$siteroot}scripts/jquery.tablesorter.min.js"></script>
  <script src="{$siteroot}scripts/jquery.table2excel.js"></script>
  <script type="text/javascript" src="{$siteroot}scripts/jquery.plugin.js"></script> 
  <script type="text/javascript" src="{$siteroot}scripts/jquery.keypad.js"></script>
  <script type="text/javascript" src="{$siteroot}scripts/jquery.ui.timepicker.js"></script>
  

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
		global $pdo;
		global $lang;
		
		$currentpage = basename($_SERVER['PHP_SELF']);
		
		$query = "INSERT INTO nefos_features (user_id, userGroup, feature) VALUES ('{$_SESSION['user_id']}', '{$_SESSION['userGroup']}', '{$currentpage}')";
		try
		{
			$result = $pdo->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$domain = $_SESSION['domain'];
		if (isset($_SESSION['saveDispense'])) {
		   if (basename($_SERVER['REQUEST_URI']) != $_SESSION['saveDispense']) {
		        unset($_SESSION['new-dispense-flag']);
		   }
		}

	// Only run the login menu if there's a user actually logged in!
	if (isset($_SESSION['user_id'])) {
		
		$loggedInUser = $_SESSION['user_id'];
		tzo();
		if ($_SESSION['domain'] == 'irena') {
			$insertTime = '1 Junio 2019 ' . date('H:i');			
		} else {
			$insertTime = date('jS F Y H:i');
		}
		
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
		
	if ($_GET) {
				
			    $href .= strpos($href, '?') === false ? '?' : '&';
			    $href .= http_build_query($_GET);
			    $href = substr($href, 1);
			    $href = str_replace('lang=es&', '', $href);
			    $href = str_replace('lang=en&', '', $href);
			    $href = str_replace('lang=ca&', '', $href);
			    $href = str_replace('lang=fr&', '', $href);
			    $href = str_replace('lang=nl&', '', $href);
			    $href = str_replace('lang=it&', '', $href);
			    $href = str_replace('lang=es', '', $href);
			    $href = str_replace('lang=en', '', $href);
			    $href = str_replace('lang=ca', '', $href);
			    $href = str_replace('lang=fr', '', $href);
			    $href = str_replace('lang=nl', '', $href);
			    $href = str_replace('lang=it', '', $href);
			    
			}

		if ($_SESSION['lang'] == 'es') {
			$langswitch = "<img src='{$siteroot}images/lang-es.png' id='langselect' style='vertical-align: bottom; margin-left: 15px;' />";
		} else if ($_SESSION['lang'] == 'ca') {
			$langswitch = "<img src='{$siteroot}images/lang-ca.png' id='langselect' style='vertical-align: bottom; margin-left: 15px;' />";
		} else if ($_SESSION['lang'] == 'it') {
			$langswitch = "<img src='{$siteroot}images/lang-it.png' id='langselect' style='vertical-align: bottom; margin-left: 15px;' />";
		} else if ($_SESSION['lang'] == 'nl') {
			$langswitch = "<img src='{$siteroot}images/lang-nl.png' id='langselect' style='vertical-align: bottom; margin-left: 15px;' />";
		} else if ($_SESSION['lang'] == 'fr') {
			$langswitch = "<img src='{$siteroot}images/lang-fr.png' id='langselect' style='vertical-align: bottom; margin-left: 15px;' />";
		} else {
			$langswitch = "<img src='{$siteroot}images/lang-en.png' id='langselect' style='vertical-align: bottom; margin-left: 15px;' />";
		}
		
		if ($_SESSION['workstation'] == 'reception') {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/puesto-reception.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>"; 
		} else if ($_SESSION['workstation'] == 'bar') {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/puesto-bar.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>"; 
		} else if ($_SESSION['workstation'] == 'dispensary') {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/puesto-dispensary.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
		} else if ($_SESSION['workstation'] > 0) {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/puesto-custom.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
		} else {
			$changeWorkstation = "";
		}
		
		if ($_SESSION['workstation'] == '') {
			$currStation = 'No definido';
		} else {
			$currStation = $_SESSION['workstation'];
		}

		
		// Look for Tablet readers
		if ($_SESSION['iPadReaders'] > 0) {
			
			if ($_SESSION['scanner'] == 1) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/1.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 2) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/2.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 3) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/3.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 4) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/4.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 5) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/5.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 6) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/6.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 7) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/7.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 8) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/8.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 9) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/9.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 10) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t10.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 11) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/11.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 12) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/12.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 13) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/13.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 14) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/14.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 15) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/15.png' style='margin-left: 14px; margin-bottom: 4px;' /></a>";
			}
			
		} else {
			
			$scannerImg = "";
			
		}
			

			
			$loginbox = "<div id='loginbox'>";
			

			$dispName = $_SESSION['first_name'];
		
			$loginbox .= <<<EOD
    
     <a href="{$siteroot}admin.php"><img src="{$siteroot}images/administration.png" style="margin-left: 29px; margin-bottom: 4px;" /></a>
     {$changeWorkstation} $scannerImg $hotWarning $fingerbutton $fztoggle

     <br />
     <img src="{$siteroot}images/user-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;<span id="loggedinName"><strong>$dispName</strong></span> <a href="{$siteroot}uTil/logout-manual.php" class="logout">[Logout]</a> $langswitch
     <br />
	 <img src="{$siteroot}images/time-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;$insertTime
	 $trialLeft
	</div>
EOD;
}

		$logoFile = "images/_{$_SESSION['domain']}/logo.png";
	
		if (file_exists($logoFile)) {
			$logoFile ="{$siteroot}images/_{$_SESSION['domain']}/logo.png";
		} else {
			$logoFile ="{$siteroot}images/logo.png";
		}

			$noScroll = <<<EOD
			
<script>
function handleScroll(e) {
  if (e.target.tagName.toLowerCase() === 'input'
    && (e.target.type === 'number')
    && (e.target === document.activeElement)
    && !e.target.readOnly
  ) {
      e.target.readOnly = true;
      setTimeout(function(el){ el.readOnly = false; }, 0, e.target);
  }
}
document.addEventListener('wheel', function(e){ handleScroll(e); });
</script>

EOD;


		$searchBox = <<<EOD
		
<style>
.arrow-up {
  width: 0; 
  height: 0; 
  border-left: 10px solid transparent;
  border-right: 10px solid transparent;
  
  border-bottom: 10px solid #4f7e39;
  position: absolute;
  top: 0;
  left: 100px;
  margin-top: -12px;
}
</style>
<div id='searchBox' style='display: none; position: absolute; top: 51px; right: 0; border: 3px solid #4f7e39; border-radius: 3px; height: 60px; width: 250px; background-color: #fff; text-align: left;'>
<div class='arrow-up'></div>

<form id="registerFormX" action="search.php" method="POST" target="_blank">

  <input type="text" name="searchfield" class='defaultinput' placeholder="" style="width: 120px; height: 20px; font-size: 12px;"/> <button type="submit" class='cta2' style="width: 70px; height: 18px; font-size: 12px; padding: 0; margin: 0; margin-top: -3px; display: inline-block;">Search</button>

</form>

</div>

EOD;
		

		echo <<<EOD
 <body id="{$bodyID}" class="{$bodyClass}">
$noScroll
<style>
@keyframes blink {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}
#hotIcon {
    animation: blink 1s;
    animation-iteration-count: infinite;
}
</style>


$fzt


  <div id="wrapper">
    <div id="header">
    <div id="pagecontrols">
     <div id="icons">
	  $changeWorkstation $scannerImg $fingerbutton $hotWarning $fztoggle $notification
     </div>
     <br />
     <div id="controls">
     $trialLeft
     <img src="{$siteroot}images/clock.png" style="margin-bottom: -2px;" />&nbsp;$insertTime
	 &nbsp;&nbsp;<span id="loggedinName"><img src="{$siteroot}images/user.png" style="margin-bottom: -2px;" /> $dispName <img src="{$siteroot}images/flecha.png"  style="margin-bottom: 2px; margin-left: 2px;" /></span> $helpCenter <a href="{$siteroot}new-call.php" target="_blank"><img src="{$siteroot}images/call-shortcut.png" style="vertical-align: bottom; margin-left: 15px;" /></a> <a href="#"  id="showSearch"><img src="{$siteroot}images/search-shortcut.png" style="vertical-align: bottom; margin-left: 15px;" /></a> $searchBox <a href='new-contact-attempt.php'><img src='{$siteroot}images/new-contact-attempt.png' style='vertical-align: bottom; margin-left: 15px;' /></a> <a href='admin.php?lang=en'><img src='{$siteroot}images/admin.png' style='vertical-align: bottom; margin-left: 15px;' /></a> <a href='uTil/logout-manual.php'><img src='{$siteroot}images/logout.png' style='vertical-align: bottom; margin-left: 15px;' /></a> 
	 	 <span style='position: relative;'>
     <div id="langlist">
     <a href='?lang=en&$href'><img src='{$siteroot}images/gb.png' style='margin-bottom: 1px;' /> English</a><br />
     <a href='?lang=es&$href'><img src='{$siteroot}images/es.png' style='margin-bottom: 1px;' /> Español</a><br />
     <a href='?lang=ca&$href'><img src='{$siteroot}images/ca.png' style='margin-bottom: 1px;' /> Catalá</a><br />
     <a href='?lang=fr&$href'><img src='{$siteroot}images/fr.png' style='margin-bottom: 1px;' /> Français</a><br />
     <a href='?lang=nl&$href'><img src='{$siteroot}images/nl.png' style='margin-bottom: 1px;' /> Nederlands</a><br />
     <a href='?lang=it&$href'><img src='{$siteroot}images/it.png' style='margin-bottom: 1px;' /> Italiano</a><br />
     </div>
     </span>
	 <div id="stafflist">
     {$listcontent}
     </div>
     </div>
     <a href="index.php" id="logo"><img src="$logoFile" /></a>$superClub
   </div>
        <div id="messagelist">
      <table>
       <tr>
        <td style="vertical-align: middle;"><img src="images/bell.png" style="margin-right: 5px;" /></td>
        <td style="vertical-align: top;">$contractWarning</td>
       </tr>
      </table>
     
     </div>

      <script>
    	$("#loggedinName").click(function () {
		$('#stafflist').toggle();
		});	
		
		$("#langselect").click(function () {
		$('#langlist').toggle();
		});

    	$("#messageSwitch").click(function () {
		$("#messagelist2").toggle();
		$("#msgIcon").css("opacity", "0.3");
		});	
		
    	$("#hotIcon").click(function () {
		$("#messagelist").toggle();
		});	
		
    	$("#closeTooltip").click(function () {
		$("#tooltip").css("display", "none");
		// Run ajax to update db to show they've already clicked the X.
		});	
    	$("#showNotifications").click(function () {
	    	
		    if ($("#notificationBox").css('display') == 'none') {
			    
				// Box is closed, show notifications - THEN mark as read
			    $("#notificationBox").css("display", "block");
				$.ajax({
			      type:"post",
			      url:"get-notifications.php",
			      datatype:"text",
			      success:function(data)
			      {
			        if( data == 'false' ) {
				        
				    } else {
					    
					    $(".notificationTable").html(data);

				    }
			      }
			    });
		    	
	    	} else {
		    	
		    	// Box is open, so run script to mark as read
			    $("#notificationBox").css("display", "none");
			    $.ajax({
			      type:"post",
			      url:"mark-read.php",
			      datatype:"text",
			      success:function(data)
			      {
			        if( data == 'false' ) {
				        
				    } else {
					    $("#noteCount").html(data);
				        
				    }
			      }
   				});	
		    	
	    	}
	    	
	    // If notificationbox is open, mark as read
	    // If closed, open box but don't perform any action
	    
		// $("#notificationBox").toggle();
		// Run ajax to mark messages as read + retrieve new number of unread

		
		});	
    	$("#showSearch").click(function () {
	    	
				// Box is closed, show notifications - THEN mark as read
			    $("#searchBox").toggle();
		
		});
	 </script>
    </div> <!-- end HEADER -->
     <div id="titlearea">
     <h2>$pageName</h2>    
    </div> <!-- end titlearea -->
EOD;

// Auto-logout

if ($_SESSION['logouttime'] > 0) {
	
	$logoutAfter = $_SESSION['logouttime'] * 60000;
	
	if ($_SESSION['logoutredir'] == 1) {
		
		$logoutLink = "{$siteroot}uTil/logout-redir.php";
		
	} else {
		
		$logoutLink = "{$siteroot}uTil/logout.php";
		
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
		
		global $pdo;
		global $pdo3;
		global $lang;
	
		// Only look up warnings if a user is logged in
		if (isset($_SESSION['user_id'])) {
			
			try
			{
				$result = $pdo->prepare("SELECT warning, cutoff FROM db_access WHERE domain = '{$_SESSION['domain']}'");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$warning = $row['warning'];
				$cutoff = date("d/m/Y", strtotime($row['cutoff']));
			
			// Show cutoff
			if ($warning == 3) {
				
					
					// Look up unpaid invoices
					$domain = $_SESSION['domain'];
					
					// Look up customer number
					$invlookup = "SELECT customer FROM db_access WHERE domain = '$domain'";
					try
					{
						$result = $pdo->prepare("$invlookup");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$customer = $row['customer'];
					
					
					$invlookup = "SELECT invno FROM invoices WHERE customer = '$customer' AND paid = ''";
					try
					{
						$results = $pdo->prepare("$invlookup");
						$results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					
					while ($row = $results->fetch()) {
						$invno = $customer . "-" . $row['invno'];
						$invoices .= "<a href='_club/_$domain/invoices/$invno.pdf' class='yellow'>$invno</a><br/>";
					}
					
				if ($_SESSION['user_id'] != 999999) {
					
					echo $lang['access-disabled'];
					
					if ($_SESSION['userGroup'] == 1) {
						
						echo "
						{$lang['outstanding-invoices']}:<br />
					$invoices
					</div></div></center>";
					
					}

					
					exit();
				
				} else {
					
					echo $lang['access-disabled'];
						
						if ($_SESSION['userGroup'] == 1) {
							
							echo "
						{$lang['outstanding-invoices']}:<br />
						$invoices
						</div></div></center>";
						
						}
						
				}
				
			// Show LAST WARNING
			} else if ($warning == 2) {
				
				if ($_SESSION['domain'] == 'choko' || $_SESSION['domain'] == 'bettyboop' || $_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'relax' || $_SESSION['domain'] == 'manali' || $_SESSION['domain'] == 'terpsarmy' || $_SESSION['domain'] == 'personal') {
					
					if ($_SESSION['userGroup'] == 1) {
						
						echo str_replace("[cutoff]",$cutoff,$lang['last-warning']);
					
					} else {
						
						echo "<div id='main'>";
						
					}
					
				} else {
						
						echo str_replace("[cutoff]",$cutoff,$lang['last-warning']);
					
				}
				
			// Show soft (closeable) warning
			} else if ($warning == 1) {
				
				if ($_SESSION['domain'] == 'choko' || $_SESSION['domain'] == 'bettyboop' || $_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'relax' || $_SESSION['domain'] == 'manali' || $_SESSION['domain'] == 'terpsarmy' || $_SESSION['domain'] == 'personal') {
					
					if ($_SESSION['userGroup'] == 1) {
						
						if (isset($_GET['seenWarning'])) {
							
							$_SESSION['seenWarning'] = 'yes';
			   				echo "<div id='main'>";
							
						} else if ($_SESSION['seenWarning'] != 'yes') {
							
							echo str_replace("[cutoff]",$cutoff,$lang['soft-warning']);
							
						} else {
							
			   				echo "<div id='main'>";
			   				
			   				
		   				}
	   				
					} else {
						
		   				echo "<div id='main'>";
		   				
					}
					
				} else {
				
					if (isset($_GET['seenWarning'])) {
						
						$_SESSION['seenWarning'] = 'yes';
		   				echo "<div id='main'>";
						
					} else if ($_SESSION['seenWarning'] != 'yes') {
						
						echo str_replace("[cutoff]",$cutoff,$lang['soft-warning']);

					} else {
						
		   				echo "<div id='main'>";
		   				
		   				
	   				}
				}
			
			// Don't show any warning
			} else {
				
   				echo "<div id='main'>";
				
			}
	   		
		// User not logged in, no warnings
		} else {
			
	   		echo "<div id='main'>";
	   		
		}
	
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
	