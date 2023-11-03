<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-closing.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	
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
				
				
				
				/* Check the last one:
					If it's shiftclose, check opening
					If it's shiftopen, check shiftclose
					If it's 
					
					
					Maybe simplify it in View, to avoid server load stress, and avoid running 100 queries every sec:
					"There's an Opening or Closing currently in progress. Please click here to finalize it before continuing."
					--- And then leave all these calculations here on a new page such as 'check-open-and-close.php'
				*/
			
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
