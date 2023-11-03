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
						} else if ($_SESSION['lang'] == 'ca') {
							$errorMsg .= "Avui no s'ha obert! <br /> Per desfer-vos d'aquest missatge, <a href='open-day-pre.php' class='yellow'> obriu el dia </a> o canvieu el tipus de responsabilitat a <a href='sys-settings.php' class='yellow'> Configuració del sistema </a>";
						} else if ($_SESSION['lang'] == 'fr') {
							$errorMsg .= "La journée d'aujourd'hui n'a pas été ouverte! <br /> Pour vous débarrasser de ce message, veuillez <a href='open-day-pre.php' class='yellow'> ouvrir la journée </a> ou modifier votre type de responsabilité dans <a href='sys-settings.php' class='yellow'> Paramètres système </a>";
						} else if ($_SESSION['lang'] == 'it') {
							$errorMsg .= "Oggi non è stato aperto! <br /> Per sbarazzarsi di questo messaggio, per favore <a href='open-day-pre.php' class='yellow'> apri il giorno </a> o modifica la tua responsabilità Digita in <a href='sys-settings.php' class='yellow'> Impostazioni di sistema </a>";
						} else if ($_SESSION['lang'] == 'nl') {
							$errorMsg .= "Vandaag is niet geopend! <br /> Om van dit bericht af te komen, <a href='open-day-pre.php' class='yellow'> open de dag </a> of wijzig uw Accountability Type in <a href='sys-settings.php' class='yellow'> Systeeminstellingen </a>";
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
						} else if ($_SESSION['lang'] == 'ca') {
							$errorMsg = "El procés de Tancar Dia està en curs. <br /> Si us plau <a href='close-day-pre.php' class='yellow'> finalitza el Tancament </a> abans de continuar!";
						} else if ($_SESSION['lang'] == 'fr') {
							$errorMsg = "Le processus de clôture est en cours. <br /> Veuillez <a href='close-day-pre.php' class='yellow'> terminer la clôture </a> avant de continuer!";
						} else if ($_SESSION['lang'] == 'it') {
							$errorMsg = "Il processo Close Day è in corso. <br /> Per favore <a href='close-day-pre.php' class='yellow'> termina la Chiusura </a> prima di continuare!";
						} else if ($_SESSION['lang'] == 'nl') {
							$errorMsg = "Het Close Day-proces is aan de gang. <br /> Please <a href='close-day-pre.php' class='yellow'> voltooi de afsluiting </a> voordat u doorgaat!";
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
						} else if ($_SESSION['lang'] == 'ca') {
							$errorMsg = "El procés de Tancar Dia està en curs. <br /> Si us plau <a href='close-day-pre.php' class='yellow'> finalitza el Tancament </a> abans de continuar!";
						} else if ($_SESSION['lang'] == 'fr') {
							$errorMsg = "Le processus de clôture est en cours. <br /> Veuillez <a href='close-day-pre.php' class='yellow'> terminer la clôture </a> avant de continuer!";
						} else if ($_SESSION['lang'] == 'it') {
							$errorMsg = "Il processo Close Day è in corso. <br /> Per favore <a href='close-day-pre.php' class='yellow'> termina la Chiusura </a> prima di continuare!";
						} else if ($_SESSION['lang'] == 'nl') {
							$errorMsg = "Het Close Day-proces is aan de gang. <br /> Please <a href='close-day-pre.php' class='yellow'> voltooi de afsluiting </a> voordat u doorgaat!";
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
						} else if ($_SESSION['lang'] == 'ca') {
							$errorMsg = "El procés d'Obrir Dia està en curs. <br /> Si us plau <a href='open-day-pre.php' class='yellow'> finalitza l'Obertura </a> abans de continuar!";
						} else if ($_SESSION['lang'] == 'fr') {
							$errorMsg = "Le processus de la journée portes ouvertes est en cours. <br /> Veuillez <a href='open-day-pre.php' class='yellow'> mettre fin à l’ouverture </a> avant de continuer!";
						} else if ($_SESSION['lang'] == 'it') {
							$errorMsg = "Il processo Open Day è in corso. <br /> Per favore <a href='open-day-pre.php' class='yellow'> termina l'apertura </a> prima di continuare!";
						} else if ($_SESSION['lang'] == 'nl') {
							$errorMsg = "Het open dagproces is aan de gang. <br /> Please <a href='open-day-pre.php' class='yellow'> beëindigt de opening </a> voordat u doorgaat!";
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
								} else if ($_SESSION['lang'] == 'ca') {
									$errorMsg = "El procés de Tancar Dia està en curs. <br /> Si us plau <a href='close-day-pre.php' class='yellow'> finalitza el Tancament </a> abans de continuar!";
								} else if ($_SESSION['lang'] == 'fr') {
									$errorMsg = "Le processus de clôture est en cours. <br /> Veuillez <a href='close-day-pre.php' class='yellow'> terminer la clôture </a> avant de continuer!";
								} else if ($_SESSION['lang'] == 'it') {
									$errorMsg = "Il processo Close Day è in corso. <br /> Per favore <a href='close-day-pre.php' class='yellow'> termina la Chiusura </a> prima di continuare!";
								} else if ($_SESSION['lang'] == 'nl') {
									$errorMsg = "Het Close Day-proces is aan de gang. <br /> Please <a href='close-day-pre.php' class='yellow'> voltooi de afsluiting </a> voordat u doorgaat!";
								} else {
									$errorMsg = "A day is currently being closed.<br />Please <a href='close-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
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
						} else if ($_SESSION['lang'] == 'ca') {
							$errorMsg = "El procés d'Obrir Dia està en curs. <br /> Si us plau <a href='open-day-pre.php' class='yellow'> finalitza l'Obertura </a> abans de continuar!";
						} else if ($_SESSION['lang'] == 'fr') {
							$errorMsg = "Le processus de la journée portes ouvertes est en cours. <br /> Veuillez <a href='open-day-pre.php' class='yellow'> mettre fin à l’ouverture </a> avant de continuer!";
						} else if ($_SESSION['lang'] == 'it') {
							$errorMsg = "Il processo Open Day è in corso. <br /> Per favore <a href='open-day-pre.php' class='yellow'> termina l'apertura </a> prima di continuare!";
						} else if ($_SESSION['lang'] == 'nl') {
							$errorMsg = "Het open dagproces is aan de gang. <br /> Please <a href='open-day-pre.php' class='yellow'> beëindigt de opening </a> voordat u doorgaat!";
						} else {
							$errorMsg = "A day is currently being opened.<br />Please <a href='open-day-pre.php' class='yellow'>finish the opening</a> before continuing!";
						}
							
						} else {
	
						if ($_SESSION['lang'] == 'es') {
							$errorMsg = "El proceso de Cerrar Dia esta en curso.<br />Por favor <a href='close-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
						} else if ($_SESSION['lang'] == 'ca') {
							$errorMsg = "El procés de Tancar Dia està en curs. <br /> Si us plau <a href='close-day-pre.php' class='yellow'> finalitza el Tancament </a> abans de continuar!";
						} else if ($_SESSION['lang'] == 'fr') {
							$errorMsg = "Le processus de clôture est en cours. <br /> Veuillez <a href='close-day-pre.php' class='yellow'> terminer la clôture </a> avant de continuer!";
						} else if ($_SESSION['lang'] == 'it') {
							$errorMsg = "Il processo Close Day è in corso. <br /> Per favore <a href='close-day-pre.php' class='yellow'> termina la Chiusura </a> prima di continuare!";
						} else if ($_SESSION['lang'] == 'nl') {
							$errorMsg = "Het Close Day-proces is aan de gang. <br /> Please <a href='close-day-pre.php' class='yellow'> voltooi de afsluiting </a> voordat u doorgaat!";
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
						} else if ($_SESSION['lang'] == 'ca') {
							$errorMsg = "El procés d'Obrir Dia està en curs. <br /> Si us plau <a href='open-day-pre.php' class='yellow'> finalitza l'Obertura </a> abans de continuar!";
						} else if ($_SESSION['lang'] == 'fr') {
							$errorMsg = "Le processus de la journée portes ouvertes est en cours. <br /> Veuillez <a href='open-day-pre.php' class='yellow'> mettre fin à l’ouverture </a> avant de continuer!";
						} else if ($_SESSION['lang'] == 'it') {
							$errorMsg = "Il processo Open Day è in corso. <br /> Per favore <a href='open-day-pre.php' class='yellow'> termina l'apertura </a> prima di continuare!";
						} else if ($_SESSION['lang'] == 'nl') {
							$errorMsg = "Het open dagproces is aan de gang. <br /> Please <a href='open-day-pre.php' class='yellow'> beëindigt de opening </a> voordat u doorgaat!";
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
								} else if ($_SESSION['lang'] == 'ca') {
									$errorMsg = "El procés de Tancar Dia està en curs. <br /> Si us plau <a href='close-day-pre.php' class='yellow'> finalitza el Tancament </a> abans de continuar!";
								} else if ($_SESSION['lang'] == 'fr') {
									$errorMsg = "Le processus de clôture est en cours. <br /> Veuillez <a href='close-day-pre.php' class='yellow'> terminer la clôture </a> avant de continuer!";
								} else if ($_SESSION['lang'] == 'it') {
									$errorMsg = "Il processo Close Day è in corso. <br /> Per favore <a href='close-day-pre.php' class='yellow'> termina la Chiusura </a> prima di continuare!";
								} else if ($_SESSION['lang'] == 'nl') {
									$errorMsg = "Het Close Day-proces is aan de gang. <br /> Please <a href='close-day-pre.php' class='yellow'> voltooi de afsluiting </a> voordat u doorgaat!";
								} else {
									$errorMsg = "A day is currently being closed.<br />Please <a href='close-day-pre.php' class='yellow'>finish the closing</a> before continuing!";
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
							} else if ($_SESSION['lang'] == 'ca') {
								$errorMsg = "El procés d'Obrir Dia està en curs. <br /> Si us plau <a href='open-day-pre.php' class='yellow'> finalitza l'Obertura </a> abans de continuar!";
							} else if ($_SESSION['lang'] == 'fr') {
								$errorMsg = "Le processus de la journée portes ouvertes est en cours. <br /> Veuillez <a href='open-day-pre.php' class='yellow'> mettre fin à l’ouverture </a> avant de continuer!";
							} else if ($_SESSION['lang'] == 'it') {
								$errorMsg = "Il processo Open Day è in corso. <br /> Per favore <a href='open-day-pre.php' class='yellow'> termina l'apertura </a> prima di continuare!";
							} else if ($_SESSION['lang'] == 'nl') {
								$errorMsg = "Het open dagproces is aan de gang. <br /> Please <a href='open-day-pre.php' class='yellow'> beëindigt de opening </a> voordat u doorgaat!";
							} else {
								$errorMsg = "A day is currently being opened.<br />Please <a href='open-day-pre.php' class='yellow'>finish the opening</a> before continuing!";
							}
							
						} else {
	
							if ($_SESSION['lang'] == 'es') {
								$errorMsg = "El proceso de Cerrar Turno y Dia esta en curso.<br />Por favor <a href='close-shift-and-day-pre.php' class='yellow'>finaliza el Cierre</a> antes de continuar!";
							} else if ($_SESSION['lang'] == 'ca') {
									$errorMsg = "El procés de Tancar Torn i Dia està en curs. <br /> Si us plau <a href='close-shift-and-day-pre.php' class='yellow'> finalitza el Tancament </a> abans de continuar !";
							} else if ($_SESSION['lang'] == 'fr') {
									$errorMsg = "Le processus de fermeture du quart et du jour est en cours. <br /> Veuillez <a href='close-shift-and-day-pre.php 'class='yellow'> terminer la clôture </a> avant de poursuivre. !";
							} else if ($_SESSION['lang'] == 'it') {
									$errorMsg = "Il processo Chiudi turno e giorno è in corso. <br /> Per favore <a href='close-shift-and-day-pre.php' class='yellow'> termina la chiusura </a> prima di continuare !";
							} else if ($_SESSION['lang'] == 'nl') {
									$errorMsg = "Het proces Shift en dag sluiten is aan de gang. <br /> <a href='close-shift-and-day-pre.php' class='yellow'> beëindig de afsluiting </a> voordat u doorgaat !";
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
  <link rel="stylesheet" href="{$siteroot}css/dev.css" type="text/css" />
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

	
	if (isset($_SESSION['user_id'])) {
		
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
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/puesto-reception.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>"; 
		} else if ($_SESSION['workstation'] == 'bar') {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/puesto-bar.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>"; 
		} else if ($_SESSION['workstation'] == 'dispensary') {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/puesto-dispensary.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
		} else if ($_SESSION['workstation'] > 0) {
			$changeWorkstation = "<a href='{$siteroot}uTil/change-workstation.php'><img src='{$siteroot}images/puesto-custom.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
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
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t1.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 2) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t2.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 3) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t3.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 4) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t4.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 5) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t5.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 6) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t6.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 7) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t7.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 8) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t8.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 9) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t9.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 10) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t10.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 11) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t11.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 12) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t12.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 13) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t13.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 14) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t14.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
			} else if ($_SESSION['scanner'] == 15) {
				$scannerImg = "<a href='change-scanner.php?scannerid={$_SESSION['scanner']}'><img src='images/t15.png' style='margin-left: 9px; margin-bottom: 4px;' /></a>";
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
			} else if ($_SESSION['lang'] == 'ca') {
				$trialLeft = "<br /><img src='{$siteroot}images/trial.png' style='margin-bottom: -2px' /><span style='color: red;'>&nbsp;&nbsp;Mes sin compromiso:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Quedan <strong>$remainingTrial</strong> dies</span>";
			} else if ($_SESSION['lang'] == 'fr') {
				$trialLeft = "<br /><img src='{$siteroot}images/trial.png' style='margin-bottom: -2px' /><span style='color: red;'>&nbsp;&nbsp;Mes sin compromiso:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gauche <strong>$remainingTrial</strong> jours</span>";
			} else if ($_SESSION['lang'] == 'it') {
				$trialLeft = "<br /><img src='{$siteroot}images/trial.png' style='margin-bottom: -2px' /><span style='color: red;'>&nbsp;&nbsp;Mes sin compromiso:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Si tratta di <strong>$remainingTrial</strong> giorni</span>";
			} else if ($_SESSION['lang'] == 'nl') {
				$trialLeft = "<br /><img src='{$siteroot}images/trial.png' style='margin-bottom: -2px' /><span style='color: red;'>&nbsp;&nbsp;Mes sin compromiso:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Het is <strong>$remainingTrial</strong> dagen</span>";
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
			$hotWarning = "<a href='#'><img src='{$siteroot}images/flame.png' style='margin-left: 9px; margin-bottom: 4px;' id='hotIcon' /></a>";
		} else {
			$hotWarning = "";
		}
		
		
		if ($_SESSION['lang'] == 'es') {
			
			$contractWarning = "<strong>&iexcl;Aviso importante!</strong> Por favor revisa y firma el contrato actualizado de CCS.<br />Si no se ha firmado hasta el 31/12/2019 vas a perder temporalmente el acceso!<br /><center><a href='contract.php'>Ver contrato</a></center>";
			
		} else if ($_SESSION['lang'] == 'ca') {
			$contractWarning = "<strong>Avís important! </ Strong> Si us plau revisa i signa el contracte actualitzat de CCS. <br /> Si no s'ha signat fins al 31/12/2019 perdràs temporalment l'accés! <br /> <center> <a href = 'contract.php'> Veure contracte </a> </center>";
		} else if ($_SESSION['lang'] == 'fr') {
			$contractWarning = "<strong>Avis important! </ Strong> Veuillez réviser et signer le contrat de CCS mis à jour. <br /> S'il n'a pas été signé jusqu'au 31/12/2019, l'accès sera temporairement perdu! <br /> <center> <a href = 'contract.php'> Voir le contrat </a> </center>";
		} else if ($_SESSION['lang'] == 'it') {
			$contractWarning = "<strong>Avviso importante! </strong> Esamina e firma il contratto CCS aggiornato. <br /> Se non è stato firmato fino al 31/12/2019, perderai temporaneamente l'accesso! <br /> <center> <a href = 'contract.php'> Vedi contratto </a> </center>";
		} else if ($_SESSION['lang'] == 'nl') {
			$contractWarning = "<strong>Belangrijke mededeling! </strong> bekijk en onderteken het bijgewerkte CCS-contract. <br /> Als het pas op 31/12/2019 is ondertekend, verliest u tijdelijk de toegang! <br /> <center> <a href = 'contract.php'> Zie contract </a> </center>";
		} else {
			
			$contractWarning = "<strong>Important!</strong> Please revise and sign the updated CCS contract.<br />If you haven't signed by the 31/12/2019, you will temporarily lose access to your software!<br /><center><a href='contract-en.php'>View contract</a></center>";
			
		}
		
		if ($_SESSION['fingerprint'] == 1) {
			$fingerbutton = "<a href='scan-finger.php' style='margin-left: 12px; vertical-align: top;' ><img src='images/finger-icon.png' /></a>";
		}
		
		
		if ($_SESSION['domain'] == 'personal') {
			$dispName = $currStation;
		} else {
			$dispName = $_SESSION['first_name'];
		}
		

		$logoFile = "images/_{$_SESSION['domain']}/logo.png";
	
		if (file_exists($logoFile)) {
			$logoFile ="{$siteroot}images/_{$_SESSION['domain']}/logo.png";
		} else {
			$logoFile ="{$siteroot}images/logo.png";
		}

		if ($_SESSION['domain'] == 'kamehouse') {
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
		}

			$loginbox = <<<EOD
	<div id='loginbox'>
    
     <a href="{$siteroot}admin.php"><img src="{$siteroot}images/administration.png" style="margin-left: 29px; margin-bottom: 4px;" />asdf</a>
     {$changeWorkstation} $scannerImg $hotWarning $fingerbutton $fztoggle

     <br />
     <img src="{$siteroot}images/user-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;<span id="loggedinname"><strong>$dispName</strong></span> <a href="{$siteroot}uTil/logout-manual.php" class="logout">[Logout]</a> $langswitch
     <span style='position: relative;'>
     <div id="langlist" style='display: none; position: absolute;'>
     <a href='?lang=en&$href'><img src='{$siteroot}images/lang-en.png' style='margin-bottom: 1px;' /> English</a><br />
     <a href='?lang=es&$href'><img src='{$siteroot}images/es.png' style='margin-bottom: 1px;' /> Español</a><br />
     <a href='?lang=ca&$href'><img src='{$siteroot}images/ca.png' style='margin-bottom: 1px;' /> Catalá</a><br />
     <a href='?lang=fr&$href'><img src='{$siteroot}images/fr.png' style='margin-bottom: 1px;' /> Français</a><br />
     <a href='?lang=nl&$href'><img src='{$siteroot}images/nl.png' style='margin-bottom: 1px;' /> Nederlands</a><br />
     <a href='?lang=it&$href'><img src='{$siteroot}images/it.png' style='margin-bottom: 1px;' /> Italiano</a><br />
     </div>
     </span>
     <br />
	 <img src="{$siteroot}images/time-icon.png" style="margin-bottom: -2px" />&nbsp;&nbsp;$insertTime
	 $trialLeft
	</div>
EOD;
}
		
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
     <a href="index.php" id="logo"><img src="$logoFile" /></a>
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
								
								echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Tiene factura(s) pendiente(s) del pago. Para evitar la desactivaci&oacute;n de su servicio, realice el pago inmediatamente.<br />El acceso al sistema se desactivar&aacute; el <span style='color: #ffff00 !important;'>dia 04/11/2019</span>!<br /><br /><span style='color: red !important;'>IMPORTANTE:</span> Ahora puede comprobar que facturas son pendientes en su <a href='invoices.php' style='color: #ffff00 !important;'>pantalla Facturas</a>. <br /><br /> Para cualquier duda, contactenos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a><br /><br /><a href='?seenWarning' class='yellow'>&raquo; Cerrar alerta &laquo;</a></div></div></center>";
		
							} else {
					
								echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>You have (an) unpaid invoice(s). To avoid interruptions to your software access, please make the payment immediately.<br />Your system access will be disabled on the <span style='color: #ffff00 !important;'>04/11/2019</span>!<br /><br /><span style='color: red !important;'>IMPORTANT:</span> You can now see which invoices are outstanding in your <a href='invoices.php' style='color: #ffff00 !important;'>invoicing dashboard</a>. <br /><br /> If you have any questions, please contact us on <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a><br /><br /><a href='?seenWarning' class='yellow'>&raquo; Close warning &laquo;</a></div></div></center>";
														
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
							
							echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>Tiene factura(s) pendiente(s) del pago. Para evitar la desactivaci&oacute;n de su servicio, realice el pago inmediatamente.<br />El acceso al sistema se desactivar&aacute; el <span style='color: #ffff00 !important;'>dia 04/11/2019</span>!<br /><br /><span style='color: red !important;'>IMPORTANTE:</span> Ahora puede comprobar que facturas son pendientes en su <a href='invoices.php' style='color: #ffff00 !important;'>pantalla Facturas</a>. <br /><br /> Para cualquier duda, contactenos en <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a><br /><br /><a href='?seenWarning' class='yellow'>&raquo; Cerrar alerta &laquo;</a></div></div></center>";
	
						} else {
				
							echo "<div id='main'><center><div id='scriptMsg'><div style='color: red;' class='error'>You have (an) unpaid invoice(s). To avoid interruptions to your software access, please make the payment immediately.<br />Your system access will be disabled on the <span style='color: #ffff00 !important;'>04/11/2019</span>!<br /><br /><span style='color: red !important;'>IMPORTANT:</span> You can now see which invoices are outstanding in your <a href='invoices.php' style='color: #ffff00 !important;'>invoicing dashboard</a>. <br /><br /> If you have any questions, please contact us on <a href='mailto:facturacion@cannabisclub.systems' style='color: #ffff00 !important;'>facturacion@cannabisclub.systems</a><br /><br /><a href='?seenWarning' class='yellow'>&raquo; Close warning &laquo;</a></div></div></center>";
													
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
		
