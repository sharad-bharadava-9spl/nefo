<?php

	require_once 'functions.php';
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

		if (isset($_SESSION['user_id'])) {
			
			if ($_SESSION['userGroup'] == 1) {
				
				if (isset($_GET['readInv'])) {
					
				try
				{
					$result = $pdo3->prepare("UPDATE systemsettings SET autologout = 6")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					$result = $pdo3->prepare("UPDATE systemsettings SET autologout = 9");
					$result->execute();
					
					// Update systemsettings SET autologout = 0 WHERE trialMode = 0

				}
				
				try
				{
					$result = $pdo3->prepare("SELECT autologout FROM systemsettings");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				$row = $result->fetch();
					$autologout = $row['autologout'];
				
				if ($autologout != 9) {
					
					if ($_SESSION['lang'] == 'en') {
						
						echo "<div id='main'><center><div id='scriptMsg'><div style='color: black; background-color: white; border: 4px solid #a80082; padding: 20px; position: relative;'><a href='?readInv'><img src='images/delete.png' width='22' style='position: absolute; top: 10px; right: 10px;' /></a><br />You can now access your invoices directly from the software.<br />Open the administration panel and click 'Facturación' to access it.<br /><br /><a href='?readInv' style='color: #a80082; text-decoration: underline;'>Close</a></div></div></center>";
						
					} else {
						
						echo "<div id='main'><center><div id='scriptMsg'><div style='color: black; background-color: white; border: 4px solid #a80082; padding: 20px; position: relative;'><a href='?readInv'><img src='images/delete.png' width='22' style='position: absolute; top: 10px; right: 10px;' /></a><br />Ya tienes disponible una nueva factura, la puedes descargar entrando al panel de Administración,<br />casilla Administración, pinchando botón FACTURACIÓN.<br /><br /><a href='?readInv' style='color: #a80082; text-decoration: underline;'>Cerrar</a></div></div></center>";
						
					}
					
				}
				
			}
		
			// Nobody has opened the day today! To get rid of this warning, either open the day or go to Settings and change 'Accountability' type.
			if ($_SESSION['openAndClose'] > 2) {
				
				// Check: If last open/close was NOT a closing, do NOT display this message!!
				try
				{
					$result = $pdo3->prepare("SELECT 'opening' AS optype, openingtime AS optime FROM opening UNION ALL SELECT 'closing' AS optype, closingtime AS optime FROM closing ORDER BY optime DESC LIMIT 1");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
	
				$row = $result->fetch();
					$optype = $row['optype'];
					$openingtime = $row['optime'];
					
					
				if ($optype == 'closing') {
					
					$openingtime = date("d-m-Y", strtotime($openingtime));
					$timenow = date("d-m-Y");
	
					if (strtotime($openingtime) < strtotime($timenow)) {
						
						if ($_SESSION['lang'] == 'es') {
							$errorMsg .= "El día de hoy no esta abierto! <br />Para ocultar esta alerta, por favor <a href='open-day-pre.php' class='yellow'>abre el día</a> o cambia Tipo de contabilidad en <a href='sys-settings.php' class='yellow'>Ajustes</a>.";
						} else {
							$errorMsg .= "Today has not been opened!<br />To get rid of this message, please <a href='open-day-pre.php' class='yellow'>open the day</a> or change your Accountability Type in <a href='sys-settings.php' class='yellow'>System Settings</a>.";
						}
						
					}
					
				}
				
			// Check FirstDayOpen vs Open, in case it's the first ever opening!
			}
			
			if ($_SESSION['openAndClose'] == 2) {
				
				// Look for closings, dayClosed and dayOpened
				// If just 1 line, check dayClosed
				// If more, check dayOpened
				
				$rowCount = $pdo3->query("SELECT count(closingid) FROM closing")->fetchColumn();
	
				if ($rowCount == 1) {
					
					try
					{
						$result = $pdo3->prepare("SELECT dayClosed, dayOpened FROM closing ORDER BY closingtime DESC");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$dayClosed = $row['dayClosed'];
						
					if ($dayClosed < 2) {
							
						if ($_SESSION['lang'] == 'es') {
							$errorMsg = "El proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
						} else {
							$errorMsg = "A day is currently being closed.<br />Please <a href='close-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
						}
								
					}
					
				} else if ($rowCount > 1) {
					
					try
					{
						$result = $pdo3->prepare("SELECT dayClosed, dayOpened FROM closing ORDER BY closingtime DESC LIMIT 1,1");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$dayOpened = $row['dayOpened'];
						
					if ($dayOpened < 2) {
							
						if ($_SESSION['lang'] == 'es') {
							$errorMsg = "El proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
						} else {
							$errorMsg = "A day is currently being closed.<br />Please <a href='close-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
						}
	
					}
					
				}
				
			} else if ($_SESSION['openAndClose'] == 3) {
				
				$rowCount1 = $pdo3->query("SELECT count(closingid) FROM closing")->fetchColumn();
				$rowCount2 = $pdo3->query("SELECT count(openingid) FROM opening")->fetchColumn();
				$rowCount = $rowCount1 + $rowCount2;
				
				if ($rowCount == 1) {
					
					try
					{
						$result = $pdo3->prepare("SELECT closingtime, dayOpened, 'closing' AS type, '' AS firstDayOpen FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' AS type, firstDayOpen FROM opening ORDER BY closingtime DESC");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$dayOpened = $row['dayOpened'];
						$type = $row['type'];
						$firstDayOpen = $row['firstDayOpen'];
						
					if ($firstDayOpen < 2) {
							
						if ($_SESSION['lang'] == 'es') {
							$errorMsg = "El proceso de Abrir Dia esta en curso.<br />Por favor <a href='open-day-pre.php' class='yellow'>finaliza la Apertura</a> antes de continuar!";
						} else {
							$errorMsg = "A day is currently being opened.<br />Please <a href='open-day-pre.php' class='yellow'>finish the opening</a> before continuing!";
						}
								
					} else {
						
						// Check if someone has started closing the day
						try
						{
							$result = $pdo3->prepare("SELECT recClosed, disClosed, dis2Closed, dayClosed FROM opening ORDER BY openingtime DESC");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
				
						$row = $result->fetch();
							$recClosed = $row['recClosed'];
							$disClosed = $row['disClosed'];
							$dis2Closed = $row['dis2Closed'];
							$dayClosed = $row['dayClosed'];
						
						if ($dayClosed < 2) {
							
							$checkClose = $recClosed + $disClosed + $dis2Closed + $dayClosed;
							
							if ($checkClose > 0 && $dayClosed != 2) {
								
								if ($_SESSION['lang'] == 'es') {
									$errorMsg = "El proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
								} else {
									$errorMsg = "The day is currently being closed.<br />Please <a href='close-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
								}
							
							}
							
						}
						
					}
					
				} else if ($rowCount > 1) {
					
						try
						{
							$result = $pdo3->prepare("SELECT closingtime, dayOpened, 'closing' AS type FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' as type FROM opening ORDER BY closingtime DESC LIMIT 2 OFFSET 1");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
				
						$row = $result->fetch();
							$dayOpened = $row['dayOpened'];
							$type = $row['type'];
						
					if ($dayOpened != 2) {
						
						if ($type == 'closing') {
							
							if ($_SESSION['lang'] == 'es') {
								$errorMsg = "El proceso de Abrir Dia esta en curso.<br />Por favor <a href='open-day-pre.php' class='yellow'>finaliza la Apertura</a> antes de continuar!";
							} else {
								$errorMsg = "A day is currently being opened.<br />Please <a href='open-day-pre.php' class='yellow'>finish the opening</a> before continuing!";
							}
							
						} else {
	
							if ($_SESSION['lang'] == 'es') {
								$errorMsg = "El proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
							} else {
								$errorMsg = "A day is currently being closed.<br />Please <a href='close-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
							}
	
						}
	
					}
	
				}
				
			} else if ($_SESSION['openAndClose'] == 4) {
				
				$rowCount1 = $pdo3->query("SELECT count(closingid) FROM closing")->fetchColumn();
				$rowCount2 = $pdo3->query("SELECT count(openingid) FROM opening")->fetchColumn();
				$rowCount = $rowCount1 + $rowCount2;
				
				if ($rowCount == 1) {
					
					try
					{
						$result = $pdo3->prepare("SELECT closingtime, dayOpened, 'closing' AS type, '' AS firstDayOpen FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' AS type, firstDayOpen FROM opening ORDER BY closingtime DESC");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
			
					$row = $result->fetch();
						$dayOpened = $row['dayOpened'];
						$type = $row['type'];
						$firstDayOpen = $row['firstDayOpen'];
						
					if ($firstDayOpen < 2) {
							
						if ($_SESSION['lang'] == 'es') {
							$errorMsg = "El proceso de Abrir Dia esta en curso.<br />Por favor <a href='open-day-pre.php' class='yellow'>finaliza la Apertura</a> antes de continuar!";
						} else {
							$errorMsg = "The day is currently being opened.<br />Please <a href='open-day-pre.php' class='yellow'>finish the opening</a> before continuing!";
						}
								
					} else {
						
						// Check if someone has started closing the day
						try
						{
							$result = $pdo3->prepare("SELECT recClosed, disClosed, dis2Closed, dayClosed FROM opening ORDER BY openingtime DESC");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
				
						$row = $result->fetch();
							$recClosed = $row['recClosed'];
							$disClosed = $row['disClosed'];
							$dis2Closed = $row['dis2Closed'];
							$dayClosed = $row['dayClosed'];
							
						if ($dayClosed < 2) {
							
							$checkClose = $recClosed + $disClosed + $dis2Closed + $dayClosed;
							
							if ($checkClose > 0 && $dayClosed != 2) {
								
								if ($_SESSION['lang'] == 'es') {
									$errorMsg = "El proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
								} else {
									$errorMsg = "The day is currently being closed.<br />Please <a href='close-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
								}
							
							}
							
						}
						
					}
					
				} else if ($rowCount > 1) {
					
					try
					{
						$result = $pdo3->prepare("SELECT closingtime, dayOpened, 'closing' AS type FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' as type FROM opening ORDER BY closingtime DESC LIMIT 2 OFFSET 1");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$dayOpened = $row['dayOpened'];
						$type = $row['type'];
						
					if ($dayOpened != 2) {
						
						if ($type == 'closing') {
							
							if ($_SESSION['lang'] == 'es') {
								$errorMsg = "El proceso de Abrir Dia esta en curso.<br />Por favor <a href='open-day-pre.php' class='yellow'>finaliza la Apertura</a> antes de continuar!";
							} else {
								$errorMsg = "The day is currently being opened.<br />Please <a href='open-day-pre.php' class='yellow'>finish the opening</a> before continuing!";
							}
								
							} else {
		
							if ($_SESSION['lang'] == 'es') {
								$errorMsg = "El proceso de Cerrar Turno y Dia esta en curso.<br />Por favor <a href='close-shift-and-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
							} else {
								$errorMsg = "The shift & day are currently being closed.<br />Please <a href='close-shift-and-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
							}
		
						}
	
					}
	
				}
				
			}
			
		}

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
		
		if ($domain == 'tresor') {
			$cssfile = "<link rel='stylesheet' href='{$siteroot}css/stylesv5-tresor.css' type='text/css' />";
		} else if ($domain == 'choko') {
			$cssfile = "<link rel='stylesheet' href='{$siteroot}css/stylesv5-choko.css' type='text/css' />";
		} else if ($domain == 'abuelitamaria') {
			$cssfile = "<link rel='stylesheet' href='{$siteroot}css/stylesv5-abuelitamaria.css' type='text/css' />";
		} else {
			$cssfile = "<link rel='stylesheet' href='{$siteroot}css/stylesv6.css' type='text/css' />";
		}
		
		echo <<<EOD
<!DOCTYPE html> 
<html>
 <head>
  <title>{$pageTitle}</title>
  $cssfile
  <link rel="stylesheet" href="{$siteroot}css/jquery-ui.css" type="text/css" />
  <link rel="stylesheet" href="{$siteroot}css/dd_signature_pad.css" type="text/css" />	
  <link rel="stylesheet" href="{$siteroot}css/select2.min.css" type="text/css" />
  <link rel="shortcut icon" href="{$siteroot}favicon.ico">
  <link rel="stylesheet" href="{$siteroot}css/jquery.keypad.css" type="text/css">
  <link rel="stylesheet" href="{$siteroot}css/jquery.ui.timepicker.css" type="text/css">  
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
		$domain = $_SESSION['domain'];
		
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
		} else if ($_SESSION['workstation'] > 0) {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/status-custom.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
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
			try
			{
				$result = $pdo->prepare("SELECT time FROM logins WHERE domain = '$domain' ORDER BY time ASC LIMIT 1");
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
		
		// Check if contract has been signed
		$rowCount = $pdo3->query("SELECT count(*) FROM contract")->fetchColumn();
		
		if ($rowCount == 0) {
			$hotWarning = "<a href='#'><img src='{$siteroot}images/icon-flame.png' style='margin-left: 9px; margin-bottom: 4px;' id='hotIcon' /></a>";
		} else {
			$hotWarning = "";
		}
		
		
		if ($_SESSION['lang'] == 'en') {
			
			$contractWarning = "<strong>Important!</strong> Please revise and sign the updated CCS contract.<br />If you haven't signed by the 30/04/2019, you will temporarily lose access to your software!<br /><center><a href='contract.php'>View contract</a></center>";
			
		} else {
			
			$contractWarning = "<strong>&iexcl;Aviso importante!</strong> Por favor revisa y firma el contrato actualizado de CCS.<br />Si no se ha firmado hasta el 30/04/2019 vas a perder temporalmente el acceso!<br /><a href='contract.php'>Ver contrato</a></center>";
			
		}
		
		if ($_SESSION['fingerprint'] == 1) {
			$fingerbutton = "<a href='scan-finger.php' style='margin-left: 12px; vertical-align: top;' ><img src='images/finger.png' /></a>";
		}
		
		
		if ($_SESSION['domain'] == 'personal') {
			$dispName = $currStation;
		} else {
			$dispName = $_SESSION['first_name'];
		}
		
			$loginbox .= <<<EOD
    
     <a href="{$siteroot}admin.php"><img src="{$siteroot}images/administration.png" style="margin-left: 29px; margin-bottom: 4px;" /></a>
     {$changeWorkstation} $scannerImg $hotWarning $fingerbutton

     <br />
     <img src="{$siteroot}images/user-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;<span id="loggedinname"><strong>$dispName</strong></span> <a href="{$siteroot}uTil/logout-manual.php" class="logout">[Logout]</a> $langswitch
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

		echo <<<EOD
 <body id="{$bodyID}" class="{$bodyClass}">
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
  <div id="wrapper">
    <div id="header">
     <div id="stafflist">
     {$listcontent}
     </div>
     <div id="messagelist">
      <table>
       <tr>
        <td style="vertical-align: middle;"><img src="images/bell.png" style="margin-right: 5px;" /></td>
        <td style="vertical-align: top;">$contractWarning</td>
       </tr>
      </table>
     
     </div>
     <!--<div id="messagelist2">
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
     $loginbox
     <a href="{$siteroot}{$currSite}" id="logo"><img src="$logoFile" /></a><br />
     <h2>$pageName</h2>
      <script>
    	$("#loggedinname").click(function () {
		$('#stafflist').toggle();
		});	
		
    	$("#messageSwitch").click(function () {
		$("#messagelist2").toggle();
		$("#msgIcon").css("opacity", "0.3");
		});	
		
    	$("#hotIcon").click(function () {
		$("#messagelist").toggle();
		});	
	 </script>
    </div> <!-- end HEADER -->
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
					
					if ($_SESSION['lang'] == 'es') {
						
						echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Acceso deshabilitado debido a factura(s) pendiente(s).<br />Contactanos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a> o teléfono +34 644 441 092 para recuperar vuestro acceso.<br /><br />";
						
						if ($_SESSION['userGroup'] == 1 && $_SESSION['domain'] != 'highclassbcn') {
							
							echo "
						Facturas pendiente(s) - pincha para ver:<br />
						$invoices
						</div></div></center>";
						
						}
						
					} else {
						
						echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Access disabled due to outstanding invoice(s).<br />Contact us on <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a> or telephone +34 644 441 092 to enable your access.<br /><br />";
						
						if ($_SESSION['userGroup'] == 1 && $_SESSION['domain'] != 'highclassbcn') {
							
							echo "
						Outstanding invoice(s) - click to view:<br />
						$invoices
						</div></div></center>";
						
						}
						
					}
					
					exit();
				
				} else {
					
						echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Access disabled due to outstanding invoice(s).<br />Contact us on <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a> or telephone +34 644 441 092 to enable your access.<br /><br />";
						
						if ($_SESSION['userGroup'] == 1) {
							
							echo "
						Outstanding invoice(s) - click to view:<br />
						$invoices
						</div></div></center>";
						
						}
						
				}
				
			// Show LAST WARNING
			} else if ($warning == 2) {
				
				if ($_SESSION['domain'] == 'choko' || $_SESSION['domain'] == 'bettyboop' || $_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'relax' || $_SESSION['domain'] == 'manali' || $_SESSION['domain'] == 'terpsarmy' || $_SESSION['domain'] == 'personal') {
					
					if ($_SESSION['userGroup'] == 1) {
						
						if ($_SESSION['lang'] == 'es') {
							echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'><span style='font-size: 26px; color: yellow;'>***** &iexcl;&Uacute;ltimo aviso! *****</span><br /><br />Tiene factura(s) pendiente(s) del pago. Si no recibimos justificante de pago hoy, vas a perder acceso al programa!<br />Para cualquier duda, contactenos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a> o tel&eacute;fono +34 644 441 092.</div></div></center>";
						} else {
							echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'><span style='font-size: 26px; color: yellow;'>***** Last warning! *****</span><br /><br />You have (an) unpaid invoice(s). If we do not receive proof of payment today, you will lose your system access!<br />If you have any doubts or questions, contact us on <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a> or telephone +34 644 441 092.</div></div></center>";
						}
					
					} else {
						
						echo "<div id='main'>";
						
					}
					
				} else {
						
						if ($_SESSION['lang'] == 'es') {
							echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'><span style='font-size: 26px; color: yellow;'>***** &iexcl;&Uacute;ltimo aviso! *****</span><br /><br />Tiene factura(s) pendiente(s) del pago. Si no recibimos justificante de pago hoy, vas a perder acceso al programa!<br />Para cualquier duda, contactenos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a> o tel&eacute;fono +34 644 441 092.</div></div></center>";
						} else {
							echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'><span style='font-size: 26px; color: yellow;'>***** Last warning! *****</span><br /><br />You have (an) unpaid invoice(s). If we do not receive proof of payment today, you will lose your system access!<br />If you have any doubts or questions, contact us on <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a> or telephone +34 644 441 092.</div></div></center>";
						}
					
				}
				
			// Show soft (closeable) warning
			} else if ($warning == 1) {
				
				if ($_SESSION['domain'] == 'choko' || $_SESSION['domain'] == 'bettyboop' || $_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'relax' || $_SESSION['domain'] == 'manali' || $_SESSION['domain'] == 'terpsarmy' || $_SESSION['domain'] == 'personal') {
					
					if ($_SESSION['userGroup'] == 1) {
						
						if (isset($_GET['seenWarning'])) {
							
							$_SESSION['seenWarning'] = 'yes';
			   				echo "<div id='main'>";
							
						} else if ($_SESSION['seenWarning'] != 'yes') {
							
							if ($_SESSION['lang'] == 'es') {
								
								echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Tiene factura(s) pendiente(s) del pago. Para evitar la desactivaci&oacute;n de su servicio, realice el pago inmediatamente.<br />El acceso al sistema se desactivar&aacute; el <span style='color: #ffff00 !important;'>dia 03/06/2019</span>!<br /><br /><span style='color: red !important;'>IMPORTANTE:</span> Ahora puede comprobar que facturas son pendientes en su <a href='invoices.php' style='color: #ffff00 !important;'>pantalla Facturas</a>. <br /><br /> Para cualquier duda, contactenos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a><br /><br /><a href='?seenWarning' class='yellow'>&raquo; Cerrar alerta &laquo;</a></div></div></center>";
		
							} else {
					
								echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>You have (an) unpaid invoice(s). To avoid interruptions to your software access, please make the payment immediately.<br />Your system access will be disabled on the <span style='color: #ffff00 !important;'>03/06/2019</span>!<br /><br /><span style='color: red !important;'>IMPORTANT:</span> You can now see which invoices are outstanding in your <a href='invoices.php' style='color: #ffff00 !important;'>invoicing dashboard</a>. <br /><br /> If you have any questions, please contact us on <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a><br /><br /><a href='?seenWarning' class='yellow'>&raquo; Close warning &laquo;</a></div></div></center>";
														
							}
							
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
						
						if ($_SESSION['lang'] == 'es') {
							
							echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Tiene factura(s) pendiente(s) del pago. Para evitar la desactivaci&oacute;n de su servicio, realice el pago inmediatamente.<br />El acceso al sistema se desactivar&aacute; el <span style='color: #ffff00 !important;'>dia 03/06/2019</span>!<br /><br /><span style='color: red !important;'>IMPORTANTE:</span> Ahora puede comprobar que facturas son pendientes en su <a href='invoices.php' style='color: #ffff00 !important;'>pantalla Facturas</a>. <br /><br /> Para cualquier duda, contactenos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a><br /><br /><a href='?seenWarning' class='yellow'>&raquo; Cerrar alerta &laquo;</a></div></div></center>";
	
						} else {
				
							echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>You have (an) unpaid invoice(s). To avoid interruptions to your software access, please make the payment immediately.<br />Your system access will be disabled on the <span style='color: #ffff00 !important;'>03/06/2019</span>!<br /><br /><span style='color: red !important;'>IMPORTANT:</span> You can now see which invoices are outstanding in your <a href='invoices.php' style='color: #ffff00 !important;'>invoicing dashboard</a>. <br /><br /> If you have any questions, please contact us on <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a><br /><br /><a href='?seenWarning' class='yellow'>&raquo; Close warning &laquo;</a></div></div></center>";
													
						}
						
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