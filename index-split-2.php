<?php
	
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	// Show reception openings and closings
	// Choose opening first
	// Then choose closing
	// Then show dispensary openings
	// Choose opening first
	// Then choose closing
	
	$openingReadable = $_GET['ot'];
	$openingSQL = $_GET['ots'];
	
	$_SESSION['recOpen'] = $openingReadable;
	$_SESSION['recOpenSQL'] = $openingSQL;



			$query = "SELECT openAndClose FROM systemsettings";
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$openAndClose = $row['openAndClose'];
				
				
			if ($openAndClose > 2) {
				
						
		    			$query = "SELECT closingid, closingtime FROM recshiftclose WHERE closingtime > '$openingSQL' ORDER by closingtime DESC";
						try
						{
							$result2 = $pdo3->prepare("$query");
							$result2->execute();
							$data2 = $result2->fetchAll();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						
						foreach ($data2 as $row) {

						$closingtime = date("d-m-Y H:i", strtotime($row['closingtime']."+$offsetSec seconds"));
						$closingtimeSQL = $row['closingtime'];
													
						$output .= <<<EOD
   <tr>
    <td class='clickableRow' href='index-split-3.php?ct=$closingtime&cts=$closingtimeSQL' style='padding: 10px; padding-right: 50px;  color: #aaa;'>$openingReadable</td>
    <td class='clickableRow' href='index-split-3.php?ct=$closingtime&cts=$closingtimeSQL' style='padding: 10px;'>$closingtime</td>
   </tr>
EOD;
						
					}
					
					pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
					echo "<h1>" . $lang['step'] . " 2: " . $lang['choose-rec-closing'] . "</h1>";
					
					echo <<<EOD
 <table class='default'>
  <thead>
   <tr>
    <th><center>{$lang['opening']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
    <th><center>{$lang['closing']}</center></th>
   </tr>
  </thead>
  <tbody>
   $output
  </tbody>
 </table>
EOD;
				
				// No closing found! Check your system settings

				
			} else {
			
				$_SESSION['errorMessage'] = $lang['no-closing-found-settings'];
				pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
			}
				
displayFooter();
