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
		
		if (isset($_SESSION['user_id'])) {
			
			if ($_SESSION['userGroup'] == 1) {
				
				if (isset($_GET['readInv'])) {
					
					$closingLookup = "UPDATE systemsettings SET autologout = 9";
					
					$result = mysql_query($closingLookup)
						or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
					
				}
				
				// Display invoice notification (using variable autologout which is no longer in use)
				$closingLookup = "SELECT autologout FROM systemsettings";
				
				$result = mysql_query($closingLookup)
					or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
					
				$row = mysql_fetch_array($result);
					$autologout = $row['autologout'];
				
				if ($autologout != 9) {
					
					if ($_SESSION['lang'] == 'en') {
						
						echo "<div id='main'><center><div id='scriptMsg'><div style='color: black; background-color: white; border: 4px solid #a80082; padding: 20px; position: relative;'><a href='?readInv'><img src='images/delete.png' width='22' style='position: absolute; top: 10px; right: 10px;' /></a><br />You can now access your invoices directly from the software.<br />Open the administration panel and click 'Facturación' to access it.<br /><br /><a href='?readInv' style='color: #a80082; text-decoration: underline;'>Close</a></div></div></center>";
						
					} else {
						
						echo "<div id='main'><center><div id='scriptMsg'><div style='color: black; background-color: white; border: 4px solid #a80082; padding: 20px; position: relative;'><a href='?readInv'><img src='images/delete.png' width='22' style='position: absolute; top: 10px; right: 10px;' /></a><br />Ya tienes disponible una nueva factura, la puedes descargar entrando al panel de Administración,<br />casilla Administración, pinchando botón FACTURACIÓN.<br /><br /><a href='?readInv' style='color: #a80082; text-decoration: underline;'>Cerrar</a></div></div></center>";
						
					}
					
				}
				
			}
			
			
				
			
		
		// Nobody has opened the day today! To get rid of this warning, either open the day or go to Settings and change 'Accoutnability' type.
		if ($_SESSION['openAndClose'] > 2) {
			
			
			$closingLookup = "SELECT openingtime FROM opening ORDER BY openingtime DESC LIMIT 1";
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$openingtime = $row['openingtime'];
				$openingtime = date("d-m-Y", strtotime($openingtime));
				$timenow = date("d-m-Y");
				
			if (strtotime($openingtime) < strtotime($timenow)) {
				
				if ($_SESSION['lang'] == 'es') {
					$errorMsg = "El día de hoy no esta abierto! <br />Para ocultar esta alerta, por favor <a href='open-day-pre.php' class='yellow'>abre el día</a> o cambia Tipo de contabilidad en <a href='sys-settings.php' class='yellow'>Ajustes</a>.";
				} else {
					$errorMsg = "Today has not been opened!<br />To get rid of this message, please <a href='open-day-pre.php' class='yellow'>open the day</a> or change your Accountability Type in <a href='sys-settings.php' class='yellow'>System Settings</a>.";
				}
				
			}
			
		// Check FirstDayOpen vs Open, in case it's the first ever opening!
		}
		
		if ($_SESSION['openAndClose'] == 2) {
			
			// Look fopr closings, dayClosed and dayOpened
			// If just 1 line, chheck dayClosed
			// If more, check dayOpened
			
			$closingLookup = "SELECT dayClosed, dayOpened FROM closing ORDER BY closingtime DESC";
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			if (mysql_num_rows($result) == 1) {
				
				// Check for dayClosed
				$row = mysql_fetch_array($result);
					$dayClosed = $row['dayClosed'];
					
				if ($dayClosed < 2) {
						
					if ($_SESSION['lang'] == 'es') {
						$errorMsg = "El proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
					} else {
						$errorMsg = "A day is currently being closed.<br />Please <a href='close-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
					}
							
				}
				
			} else if (mysql_num_rows($result) > 1) {
				
			$closingLookup = "SELECT dayClosed, dayOpened FROM closing ORDER BY closingtime DESC LIMIT 1,1";
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
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
			
			$closingLookup = "SELECT closingtime, dayOpened, 'closing' AS type, '' AS firstDayOpen FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' AS type, firstDayOpen FROM opening ORDER BY closingtime DESC";
			
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			if (mysql_num_rows($result) == 1) {
				
				
				// Check for dayClosed
				$row = mysql_fetch_array($result);
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
					$closingLookup = "SELECT recClosed, disClosed, dis2Closed, dayClosed FROM opening ORDER BY openingtime DESC";
					
					$result = mysql_query($closingLookup)
						or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
						
					$row = mysql_fetch_array($result);
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
				
			} else if (mysql_num_rows($result) > 1) {
				
			
				$closingLookup = "SELECT closingtime, dayOpened, 'closing' AS type FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' as type FROM opening ORDER BY closingtime DESC LIMIT 2 OFFSET 1";
				
				$result = mysql_query($closingLookup)
					or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
					
				// Check for dayClosed
				$row = mysql_fetch_array($result);
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
			
			$closingLookup = "SELECT closingtime, dayOpened, 'closing' AS type, '' AS firstDayOpen FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' AS type, firstDayOpen FROM opening ORDER BY closingtime DESC";
			
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			if (mysql_num_rows($result) == 1) {
				
				
				// Check for dayClosed
				$row = mysql_fetch_array($result);
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
					$closingLookup = "SELECT recClosed, disClosed, dis2Closed, dayClosed FROM opening ORDER BY openingtime DESC";
					
					$result = mysql_query($closingLookup)
						or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
						
					$row = mysql_fetch_array($result);
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
				
			} else if (mysql_num_rows($result) > 1) {
				
			
				$closingLookup = "SELECT closingtime, dayOpened, 'closing' AS type FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' as type FROM opening ORDER BY closingtime DESC LIMIT 2 OFFSET 1";
				
				$result = mysql_query($closingLookup)
					or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
					
				// Check for dayClosed
				$row = mysql_fetch_array($result);
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
		/*
		// Here you check for uncompleted openings and closings
		if ($_SESSION['openAndClose'] == 2) {
			
			$closingLookup = "SELECT dayClosed FROM closing ORDER BY closingtime DESC";
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			if (mysql_num_rows($result) == 1) {
				
				// Check for dayClosed
				$row = mysql_fetch_array($result);
					$dayClosed = $row['dayClosed'];
					
				if ($dayClosed != 2) {
					
					// $_SESSION['errorMessage'] = "A closing procedure is in progress.<br />To avoid distorting data, please <a href='close-day-pre.php' class='yellow'>finish the Closing procedure</a> before continuing!";
						$errorMsg = "Un proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day.php' class='yellow'>finaliza el proceso de Cierre</a> antes continuar!";
					
				}
				
			
			// If more than 1 result appear, check the 'second most recent one' for flags
			} else if (mysql_num_rows($result) > 1) {
				
				$closingLookup = "SELECT closingid, dayOpened FROM closing ORDER BY closingtime DESC LIMIT 2 OFFSET 1";
			
				$result = mysql_query($closingLookup)
					or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$dayOpened = $row['dayOpened'];
					
				if ($dayOpened != 2) {
					
						$errorMsg = "Un proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day.php' class='yellow'>finaliza el Cierre</a> antes continuar!";
					
				}
				
			}
			
		} else if ($_SESSION['openAndClose'] == 3) {
			
			$closingLookup = "SELECT closingtime, dayOpened, 'closing' AS type, '' AS firstDayOpen FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' AS type, firstDayOpen FROM opening ORDER BY closingtime DESC";
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			if (mysql_num_rows($result) == 1) {
				
				// Check for dayClosed
				$row = mysql_fetch_array($result);
					$dayOpened = $row['dayOpened'];
					$type = $row['type'];
					$firstDayOpen = $row['firstDayOpen'];
					
				if ($dayOpened != 2) {
					
					if ($type == 'closing') {
						
						$errorMsg = "Un proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day.php' class='yellow'>finaliza el Cierre</a> antes continuar!";
						
					} else {
						
						if ($firstDayOpen != 2) {
						
							$errorMsg = "Un proceso de Abrir Dia esta en curso.<br />Por favor <a href='open-day.php' class='yellow'>finaliza la Apertura</a> antes continuar!";
							
						}
						
					}
					
					
				}
				
			} else if (mysql_num_rows($result) > 1) {
			
				$closingLookup = "SELECT closingtime, dayOpened, 'closing' AS type FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' as type FROM opening ORDER BY closingtime DESC LIMIT 2 OFFSET 1";
				
				$result = mysql_query($closingLookup)
					or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
					
				// Check for dayClosed
				$row = mysql_fetch_array($result);
					$dayOpened = $row['dayOpened'];
					$type = $row['type'];
					
				if ($dayOpened != 2) {
					
					if ($type == 'closing') {
						
						$errorMsg = "Un proceso de Abrir Dia esta en curso.<br />Por favor <a href='open-day.php' class='yellow'>finaliza la Apertura</a> antes continuar!";
						
					} else {

						$errorMsg = "Un proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day.php' class='yellow'>finaliza el Cierre</a> antes continuar!";

					}

				}

			}
			
		} else if ($_SESSION['openAndClose'] == 4) {
			
			$closingLookup = "SELECT closingtime, dayOpened, 'closing' AS type, '' AS firstDayOpen FROM closing UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' AS type, firstDayOpen FROM opening ORDER BY closingtime DESC";
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
				
			// Means there's only 1 entry ====> meaning it has to be an opening?
			if (mysql_num_rows($result) == 1) {
				
				$row = mysql_fetch_array($result);
					$dayOpened = $row['dayOpened'];
					$type = $row['type'];
					$firstDayOpen = $row['firstDayOpen'];
					
				if ($dayOpened != 2) {
					
					if ($type == 'closing') {
						
						$errorMsg = "Un proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day.php' class='yellow'>finaliza el Cierre</a> antes continuar! 0";
						
					} else {
						
						if ($firstDayOpen != 2) {
						
							$errorMsg = "Un proceso de Abrir Dia esta en curso.<br />Por favor <a href='open-day.php' class='yellow'>finaliza la Apertura</a> antes continuar! 0";
							
						}
						
					}
					
					
				}
				
			} else if (mysql_num_rows($result) > 1) {
				
				// Just remember... Even with this setting there will be days where there are no shifts!
				
				// First check open day
				
				// Second check close shift
				
				// Then check if next shift has been opened
				
				// Then check if shift has been closed
				
				// Check if day has been closed
				
				
				
					If it's shiftclose, check opening
					If it's shiftopen, check shiftclose
					If it's 
					
					
					Maybe simplify it in View, to avoid server load stress, and avoid running 100 queries every sec:
					"There's an Opening or Closing currently in progress. Please click here to finalize it before continuing."
					--- And then leave all these calculations here on a new page such as 'check-open-and-close.php'
			
				$closingLookup = "SELECT closingtime, dayOpened, 'closing' AS type FROM closing UNION ALL SELECT closingtime, shiftOpened AS dayOpened, 'shiftclose' AS type FROM shiftclose UNION ALL SELECT openingtime AS closingtime, dayClosed AS dayOpened, 'opening' AS type UNION ALL SELECT openingtime AS closingtime, shiftClosed AS dayOpened, 'shiftopen' AS type FROM shiftopen ORDER BY closingtime DESC LIMIT 2 OFFSET 1";
				
				$result = mysql_query($closingLookup)
					or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
					
				// Check for dayClosed
				$row = mysql_fetch_array($result);
					$dayOpened = $row['dayOpened'];
					$type = $row['type'];
					
				if ($type == 'opening') {
					
				}
				
					
				// If it's an Opening, check 
					
				// A shfit has been closed, but no shift opened! Pleas eopen shift before continuiing!!!
					
				if ($dayOpened != 2) {
					
					if ($type == 'closing') {
						
						$errorMsg = "Un proceso de Abrir Dia esta en curso.<br />Por favor <a href='open-day.php' class='yellow'>finaliza la Apertura</a> antes continuar! 1";
						
					} else if ($type == 'shiftclose') {
						
						$errorMsg = "Un proceso de Comenzar Turno esta en curso.<br />Por favor <a href='open-shift.php' class='yellow'>finaliza la Apertura</a> antes continuar! 2";
						
					} else if ($type == 'opening') {

						$errorMsg = "Un proceso de Cerrar Turno esta en curso.<br />Por favor <a href='close-shift.php' class='yellow'>finaliza el Cierre</a> antes continuar! 3";

					} else {

						// Find identifier for Close Shift vs Close Shift & Day!
						$errorMsg = "Un proceso de Cerrar Turno (o Cerrar Turno y Dia) esta en curso.<br />Por favor <a href='close-shift.php' class='yellow'>finaliza el Cierre</a> antes continuar! 4";

					}

				}

			}
			
		}		
		*/
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
  <link rel="stylesheet" href="{$siteroot}css/stylesv46.css" type="text/css" />
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
			
			$loginResult = mysql_query($loginLookup)
				or handleError($lang['error-crederror'],"Error loading user credentials from db: " . mysql_error());
			
			$row = mysql_fetch_array($loginResult);
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
     <img src="{$siteroot}images/user-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;<span id="loggedinname"><strong>{$_SESSION['first_name']}</strong></span> <a href="{$siteroot}uTil/logout.php" class="logout">[Logout]</a> $langswitch
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
     $loginbox
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
		
	if (isset($_SESSION['user_id'])) {
		
		$checkWarning = "SELECT warning, cutoff FROM systemsettings";
		
		$warningRes = mysql_query($checkWarning)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
		
		$rowW = mysql_fetch_array($warningRes);
			$warning = $rowW['warning'];
			$cutoff = date("d/m/Y", strtotime($rowW['cutoff']));
			
		if ($warning == 2) {
			
			echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Acceso deshabilitado debido a factura(s) pendiente(s).<br />Contactanos en facturacion@cannabisclub.systems para recuperar vuestro acceso.</div></div></center>";
			exit();
			
			// 			echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'><span style='font-size: 26px; color: yellow;'>***** &Uacute;ltimo aviso! *****</span><br /><br />Tiene factura(s) pendiente(s) del pago. Si no recibimos justificante de pago hoy, vas a perder acceso al programa!<br />Para cualquier duda, contactenos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a></div></div></center>";

//					echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Tiene factura(s) pendiente(s) del pago. Para evitar la desactivación de su servicio, realice el pago inmediatamente.<br />El acceso al sistema se desactivar&aacute; el <span style='color: #ffff00 !important;'>dia $cutoff</span>!<br />Para cualquier duda, contactenos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a></div></div></center>";
			
		} else {
		
   		echo "<div id='main'>";
   			
		}
   		
		
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