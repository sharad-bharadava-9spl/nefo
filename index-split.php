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
				
    			$query = "SELECT openingtime FROM recshiftopen ORDER BY openingtime DESC";
				try
				{
					$result = $pdo3->prepare("$query");
					$result->execute();
					$data = $result->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
				if ($data) {
			
					foreach ($data as $row) {
		
						$openingtime = date("d-m-Y H:i", strtotime($row['openingtime']."+$offsetSec seconds"));
						$openingtimeSQL = $row['openingtime'];
						
		    			$query = "SELECT closingid, closingtime FROM recshiftclose WHERE closingtime > '$openingtimeSQL'";
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
						
						if ($data2) {
						
							$clickable = 'clickableRow';
							$row2 = $data2[0];
								$closingtime = date("d-m-Y H:i", strtotime($row2['closingtime']."+$offsetSec seconds"));
								$closingtimeSQL = $row2['closingtime'];
								$closingid = $row2['closingid'];
								
								
						} else {
							
							$clickable = '';
							$closingtime = '';
							$closingid = '';
							
						}
													
						$output .= <<<EOD
   <tr>
    <td class='$clickable' href='index-split-2.php?ot=$openingtime&ots=$openingtimeSQL' style='padding: 10px; padding-right: 50px;'>$openingtime</td>
    <td class='$clickable' href='index-split-2.php?ot=$openingtime&ots=$openingtimeSQL' style='padding: 10px; color: #aaa;'>$closingtime</td>
   </tr>  
EOD;
						
					}
					
					pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
					echo "<h1>" . $lang['step'] . " 1: " . $lang['choose-rec-opening'] . "</h1>";
					
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
				
			} else {
			
				$_SESSION['errorMessage'] = $lang['no-closing-found-settings'];
				pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
			}
				
displayFooter();
