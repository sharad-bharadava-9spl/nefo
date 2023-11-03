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
	
	if (isset($_GET['noComp'])) {
		$_SESSION['noCompare'] = 'true';
	}
	
	if ($_SESSION['openAndClose'] == 2) {
		
		$closingtime = date('Y-m-d H:i:s');
		$_SESSION['closingtime'] = $closingtime;
		$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
			
		if ($_SESSION['noCompare'] != 'true') {
			
			// Closing only - WITH comparison
			$closingLookup = "SELECT closingid, closingtime, cashintill, bankBalance, closedby, dayOpened, recOpened, disOpened, dis2Opened, dayOpenedNo FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			$row = $result->fetch();
				$openingid = $row['closingid'];
				$dayOpened = $row['dayOpened'];
				$dayOpenedNo = $row['dayOpenedNo'];
				$recOpened = $row['recOpened'];
				$disOpened = $row['disOpened'];		
				$dis2Opened = $row['dis2Opened'];		
				$openingtime = $row['closingtime'];
			
			$_SESSION['openingid'] = $openingid;
			$_SESSION['closingid'] = $closingid;
			$_SESSION['openingtime'] = $openingtime;
			$_SESSION['tillBalance'] = $row['cashintill']; // Only do this line if Comparing is active!!
			$_SESSION['bankBalance'] = $row['bankBalance']; // Only do this line if Comparing is active!!

	
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
		
			// Determine shift duration	
			$datetime1 = new DateTime($openingtime);
			$datetime2 = new DateTime($closingtime);
			$interval = $datetime1->diff($datetime2);
			
			$noOfMonths = $interval->format('%m');
			$noOfDays = $interval->format('%d');
			$noOfHours = $interval->format('%h');
			$noOfMins = $interval->format('%i');
			
			if ($noOfMonths == 0) {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			} else if ($noOfMonths == 1) {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			} else {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			}
	

	$pageHeader = <<<EOD
<div class="actionbox-np2">
  <div class='mainboxheader'>{$lang['close-day-details']}</div>
  <div class="boxcontent">
 <table class='purchasetable'>
	 <tr>
		   <td class='biggerFont'><strong>{$lang['last-closing']}</strong>&nbsp;
		  		{$openingtimeView}
		   </td>
	</tr>
	<tr>
		     <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
		   			{$closingtimeView}
		   	</td>
	</tr>
	<tr>
		   	 <td class='biggerFont'><strong>{$lang['day-duration']}</strong>&nbsp;
		   		{$shiftDuration}
		   	</td>
	  </tr>
 </table>
 </div>
</div>
EOD;
		} else {
			
			// No comparison available
			$closingLookup = "SELECT closingid, dayClosed, recClosed, disClosed, dis2Closed FROM closing WHERE currentClosing = 1";	
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayOpenedNo = $row['closingid'];
				$dayOpened = $row['dayClosed'];
				$recOpened = $row['recClosed'];
				$disOpened = $row['disClosed'];		
				$dis2Opened = $row['dis2Closed'];		
				$pageHeader = <<<EOD
			<div class="actionbox-np2">
			  <div class='mainboxheader'>{$lang['close-day-details']}</div>
			  <div class="boxcontent">
				 <table class='purchasetable'>
					 <tr>
						   <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
						  		{$closingtimeView}
						   </td>
					  </tr>
				 </table>
			 </div>
			</div>
			EOD;
		}
		
	} else {
		
		$closingtime = date('Y-m-d H:i:s');
		$_SESSION['closingtime'] = $closingtime;
		$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));

		// Look up opening data for comparing
		$closingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance, openedby, dayClosed, recClosed, disClosed, dis2Closed, barClosed, dayClosedNo FROM opening ORDER BY openingtime DESC LIMIT 1";	
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$openingid = $row['openingid'];
			$dayOpened = $row['dayClosed'];
			$dayOpenedNo = $row['dayClosedNo'];
			$recOpened = $row['recClosed'];
			$disOpened = $row['disClosed'];		
			$dis2Opened = $row['dis2Closed'];		
			$barOpened = $row['barClosed'];		
			$openingtime = $row['openingtime'];
		
		$_SESSION['openingid'] = $openingid;
		$_SESSION['openingtime'] = $openingtime;
		$_SESSION['tillBalance'] = $row['tillBalance']; // Only do this line if Comparing is active!!
		$_SESSION['bankBalance'] = $row['bankBalance']; // Only do this line if Comparing is active!!

		$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
	
		// Determine shift duration
		$datetime1 = new DateTime($openingtime);
		$datetime2 = new DateTime($closingtime);
		$interval = $datetime1->diff($datetime2);
		
		$noOfMonths = $interval->format('%m');
		$noOfDays = $interval->format('%d');
		$noOfHours = $interval->format('%h');
		$noOfMins = $interval->format('%i');
		
		if ($noOfMonths == 0) {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		} else if ($noOfMonths == 1) {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		} else {
			
			if ($noOfDays == 0) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else if ($noOfDays == 1) {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			} else {
				if ($noOfHours > 1) {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
				} else {
					$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
				}
			}
			
		}

		$pageHeader = <<<EOD
					<div class="actionbox-np2">
					  <div class='mainboxheader'>{$lang['close-day-details']}</div>
					  <div class="boxcontent">
					 <table class='purchasetable'>
						 <tr>
							   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
							  		{$openingtimeView}
							   </td>
							   </tr>
							<tr>   
							   	<td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
							   			{$closingtimeView}
							   	</td>
							 </tr>
							 <tr>  	
							   <td class='biggerFont left'><strong>{$lang['day-duration']}</strong>&nbsp;
							   		{$shiftDuration}
							   	</td>
						  </tr>
						
					 </table>
					 </div>
					</div>
					EOD;
	}


	$_SESSION['pageHeader'] = $pageHeader;
	
	
	pageStart($lang['title-closeday'], NULL, NULL, "pcloseday", "step1 dev-align-center", $lang['closeday-main'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo $_SESSION['pageHeader'];
	
	if ($dayOpened == '2') {
		
		$dayClass = 'closed';
		$dayText = $lang['closeday-main-closed'];
		$linkVar = "#";
		$dayIcon = 'images/cartel-closed.svg';

		$recClass = 'closed';
		$recText = $lang['closeday-main-closed'];
		$reclinkVar = "#";
		$recIcon = 'images/cartel-closed.svg';

		
		$dis2Class = 'closed';
		$dis2Text = $lang['closeday-main-closed'];
		$dis2linkVar = "#";
		$dis2Icon = 'images/cartel-closed.svg';
		
		$barClass = 'closed';
		$barText = $lang['closeday-main-closed'];
		$barlinkVar = "#";
		$barIcon = 'images/cartel-closed.svg';
		
		$disClass = 'closed';
		$disText = $lang['closeday-main-closed'];
		$dislinkVar = "#";
		$disIcon = 'images/cartel-closed.svg';
		
	} else {
	
		if ($recOpened == '2') {
			$recClass = 'closed';
			$recText = $lang['closeday-main-closed'];
			$reclinkVar = "close-day-reception.php";
			$recIcon = 'images/cartel-closed.svg';
		} else if ($recOpened == 1) {
			$recClass = 'inprocess';
			$recText = $lang['closeday-main-inprocess'];
			$reclinkVar = "close-day-reception.php";
			$recIcon = 'images/in-progress.png';
		} else {
			$recClass = 'notclosed';
			$recText = $lang['closeday-main-notstarted'];
			$reclinkVar = "close-day-reception.php";
			$recIcon = 'images/exclamationtriangle.svg';
		}
		
		if ($disOpened == '2') {
			$disClass = 'closed';
			$disText = $lang['closeday-main-closed'];
			$dislinkVar = "close-day-dispensary.php";
			$disIcon = 'images/cartel-closed.svg';

		} else if ($disOpened == 1) {
			$disClass = 'inprocess';
			$disText = $lang['closeday-main-inprocess'];
			$dislinkVar = "close-day-dispensary.php";
			$disIcon = 'images/in-progress.png';
		} else {
			$disClass = 'notclosed';
			$disText = $lang['closeday-main-notstarted'];
			$dislinkVar = "close-day-dispensary.php";
			$disIcon = 'images/exclamationtriangle.svg';
		}
		
		if ($dis2Opened == '2') {
			$dis2Class = 'closed';
			$dis2Text = $lang['closeday-main-closed'];
			$dis2linkVar = "close-day-dispensary-units.php";
			$dis2Icon = "images/cartel-closed.svg";
		} else if ($dis2Opened == 1) {
			$dis2Class = 'inprocess';
			$dis2Text = $lang['closeday-main-inprocess'];
			$dis2linkVar = "close-day-dispensary-units.php";
			$dis2Icon = 'images/in-progress.png';
		} else {
			$dis2Class = 'notclosed';
			$dis2Text = $lang['closeday-main-notstarted'];
			$dis2linkVar = "close-day-dispensary-units.php";
			$dis2Icon = 'images/exclamationtriangle.svg';
		}
		
		if ($barOpened == '2') {
			$barClass = 'closed';
			$barText = $lang['closeday-main-closed'];
			$barlinkVar = "close-day-bar.php";
			$barIcon = "images/cartel-closed.svg";

		} else if ($barOpened == 1) {
			$barClass = 'inprocess';
			$barText = $lang['closeday-main-inprocess'];
			$barlinkVar = "close-day-bar.php";
			$barIcon = "images/in-progress.png";
		} else {
			$barClass = 'notclosed';
			$barText = $lang['closeday-main-notstarted'];
			$barlinkVar = "close-day-bar.php";
			$barIcon = "images/exclamationtriangle.svg";
		}
		
		if (($dayOpened == '1') && ($recOpened < 2 || $disOpened < 2 || $dis2Opened < 2)) {
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
			$dayIcon = "images/exclamationtriangle.svg";
		} else if ($dayOpened == '1') {
			$dayClass = 'inprocess';
			$dayText = $lang['closeday-main-inprocess'];
			$linkVar = "close-day-summary.php?cid=$dayOpenedNo&oid=$openingid";
			$dayIcon = "images/in-progress.png";
		} else if ($recOpened == '2' && $disOpened == '2' && $dis2Opened == '2') {
			$dayClass = 'notclosed';
			$dayText = $lang['closeday-main-notstarted'];
			$linkVar = "close-day-summary.php?cid=$dayOpenedNo&oid=$openingid";
			$dayIcon = "images/exclamationtriangle.svg";
		} else {
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
			$dayIcon = "images/exclamationtriangle.svg";
		}
	}


?>
<br /><br />

<!-- <h1><?php echo $lang['closing-process']; ?></h1>
<table class="closetable">
 <tr>
  <td><strong><?php echo $lang['reception']; ?>:</strong></td>
  <td><a href="<?php echo $reclinkVar; ?>" class="<?php echo $recClass; ?>"><?php echo $recText; ?></a></td>
 </tr>
 <tr>
  <td style="padding: 5px !important;" colspan="2"></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['dispensary']; ?> (g):</strong></td>
  <td><a href="<?php echo $dislinkVar; ?>" class="<?php echo $disClass; ?>"><?php echo $disText; ?></a></td>
 </tr>
 <tr>
  <td style="padding: 5px !important;" colspan="2"></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['dispensary']; ?> (u):</strong></td>
  <td><a href="<?php echo $dis2linkVar; ?>" class="<?php echo $dis2Class; ?>"><?php echo $dis2Text; ?></a></td>
 </tr>
 <tr>
  <td style="padding: 5px !important;" colspan="2"></td>
 </tr>

 <tr>
  <td style="padding: 5px !important;" colspan="2"></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['summary']; ?>:</strong></td>
  <td><a href="<?php echo $linkVar; ?>" class="<?php echo $dayClass; ?>"><?php echo $dayText; ?></a></td>
 </tr>
</table> -->
<div class="actionbox-np2">
 <div class="mainboxheader"><?php echo $lang['closing-process']; ?></div>
	<div class="boxcontent" style="display: flex;">
		<div>
			<p><span class='usergrouptext2'><img src="images/recepcionicon.svg"></span>&nbsp;&nbsp;<strong><?php echo $lang['reception']; ?></strong></p><br>
			 <a href="<?php echo $reclinkVar; ?>" class="<?php echo $recClass; ?> ctalink">
				<table>
					 <tr >
					  <td style="height: 70px;"><img src="<?php echo $recIcon; ?>" style="height: 70px;"></td>
					</tr>				 
					
					<tr>
					  <td><?php echo $recText; ?></td>
					 </tr>
				</table>
			</a>
		</div>
		<div>
			<p><span class='usergrouptext2'><img src="images/bolsaicon.svg"></span>&nbsp;&nbsp;<strong><?php echo $lang['dispensary']; ?>&nbsp; <span class='usergrouptext2'>gr.</span></strong></p><br>
			<a href="<?php echo $dislinkVar; ?>" class="<?php echo $disClass; ?> ctalink">
				<table>
					 <tr >
					  <td style="height: 70px;"><img src="<?php echo $disIcon; ?>" style="height: 70px;"></td>
					</tr>
					<tr>
					  <td><?php echo $disText; ?></td>
					 </tr>
				</table>
			</a>
		</div>
		<div>
			<p><span class='usergrouptext2'><img src="images/bolsaicon.svg"></span>&nbsp;&nbsp;<strong><?php echo $lang['dispensary']; ?>&nbsp; <span class='usergrouptext2'>Un.</span></strong></p><br> 
			<a href="<?php echo $dis2linkVar; ?>" class="<?php echo $dis2Class; ?> ctalink">
				<table>
					 <tr >
					  <td style="height: 70px;"><img src="<?php echo $dis2Icon; ?>" style="height: 70px;"></td>
					</tr>
					<tr>
					  <td><?php echo $dis2Text; ?></td>
					 </tr>
				</table>
			</a>
		</div>	
		<div>
			<p><img src="images/list-icon.svg">&nbsp;&nbsp;<strong><?php echo $lang['summary']; ?></strong></p><br>
			<a href="<?php echo $linkVar; ?>" class="<?php echo $dayClass; ?> ctalink">
				<table>
					 <tr >
					  <td style="height: 70px;"><img src="<?php echo $dayIcon; ?>" style="height: 70px;"></td>
					</tr>
					<tr>
					  <td><?php echo $dayText; ?></td>
					 </tr>
				</table>
			</a>
		</div>
	</div>
</div>
<?php displayFooter(); ?>

