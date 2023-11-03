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
			$closingLookup = "SELECT closingid, closingtime, cashintill, bankBalance, closedby, dayOpened, recOpened, disOpened, dayOpenedNo FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$openingid = $row['closingid'];
				$dayOpened = $row['dayOpened'];
				$dayOpenedNo = $row['dayOpenedNo'];
				$recOpened = $row['recOpened'];
				$disOpened = $row['disOpened'];		
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
<div class="textInset">
 <center><strong>{$lang['close-day-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['last-closing']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-duration']}:</td>
   <td style='text-align: left;'>{$shiftDuration}</td>
  </tr>
 </table>
</div>
EOD;

		} else {
			
			// No comparison available
			$closingLookup = "SELECT closingid, dayClosed, recClosed, disClosed FROM closing WHERE currentClosing = 1";	
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$dayOpenedNo = $row['closingid'];
				$dayOpened = $row['dayClosed'];
				$recOpened = $row['recClosed'];
				$disOpened = $row['disClosed'];		

			$pageHeader = <<<EOD
<div class="textInset">
 <center><strong>{$lang['close-day-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
 </table>
</div>
EOD;
		}
		
	} else if ($_SESSION['openAndClose'] == 3) {
		
		$closingtime = date('Y-m-d H:i:s');
		$_SESSION['closingtime'] = $closingtime;
		$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
			
		
		// Closing only - WITH comparison
		$closingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance, openedby, dayClosed, recClosed, disClosed, dayClosedNo FROM opening ORDER BY openingtime DESC LIMIT 1";	
		
		$result = mysql_query($closingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$openingid = $row['openingid'];
			$dayOpened = $row['dayClosed'];
			$dayOpenedNo = $row['dayClosedNo'];
			$recOpened = $row['recClosed'];
			$disOpened = $row['disClosed'];		
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
<div class="textInset">
 <center><strong>{$lang['close-day-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-duration']}:</td>
   <td style='text-align: left;'>{$shiftDuration}</td>
  </tr>
 </table>
</div>
EOD;

	}


	$_SESSION['pageHeader'] = $pageHeader;
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    	  
document.querySelector('button').addEventListener("click", function() {
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;
	
	pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step1", $lang['closeday-main'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo $_SESSION['pageHeader'];
	
	if ($dayOpened == '2') {
		
		$dayClass = 'closed';
		$dayText = $lang['closeday-main-closed'];
		$linkVar = "#";
		
		$recClass = 'closed';
		$recText = $lang['closeday-main-closed'];
		$reclinkVar = "#";
		
		$disClass = 'closed';
		$disText = $lang['closeday-main-closed'];
		$dislinkVar = "#";
		
	} else {
	
		if ($recOpened == '2') {
			$recClass = 'closed';
			$recText = $lang['closeday-main-closed'];
			$reclinkVar = "close-day-reception.php";
		} else if ($recOpened == 1) {
			$recClass = 'inprocess';
			$recText = $lang['closeday-main-inprocess'];
			$reclinkVar = "close-day-reception.php";
		} else {
			$recClass = 'notclosed';
			$recText = $lang['closeday-main-notstarted'];
			$reclinkVar = "close-day-reception.php";
		}
		
		if ($disOpened == '2') {
			$disClass = 'closed';
			$disText = $lang['closeday-main-closed'];
			$dislinkVar = "close-day-dispensary.php";
		} else if ($disOpened == 1) {
			$disClass = 'inprocess';
			$disText = $lang['closeday-main-inprocess'];
			$dislinkVar = "close-day-dispensary.php";
		} else {
			$disClass = 'notclosed';
			$disText = $lang['closeday-main-notstarted'];
			$dislinkVar = "close-day-dispensary.php";
		}
		
		if ($dayOpened == '1') {
			$dayClass = 'inprocess';
			$dayText = $lang['closeday-main-inprocess'];
			$linkVar = "close-day-summary.php?cid=$dayOpenedNo&oid=$openingid";
		} else if ($disOpened == '2' && $recOpened == '2') {
			$dayClass = 'notclosed';
			$dayText = $lang['closeday-main-notstarted'];
			$linkVar = "close-day-summary.php?cid=$dayOpenedNo&oid=$openingid";
		} else {
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
		}
	}


?>
<br /><br />

<h1><?php echo $lang['closing-process']; ?></h1>
<table class="closetable">
 <tr>
  <td><strong><?php echo $lang['reception']; ?>:</strong></td>
  <td><a href="<?php echo $reclinkVar; ?>" class="<?php echo $recClass; ?>"><?php echo $recText; ?></a></td>
 </tr>
 <tr>
  <td style="padding: 5px !important;" colspan="2"></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['dispensary']; ?>:</strong></td>
  <td><a href="<?php echo $dislinkVar; ?>" class="<?php echo $disClass; ?>"><?php echo $disText; ?></a></td>
 </tr>
 <tr>
  <td style="padding: 5px !important;" colspan="2"></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['summary']; ?>:</strong></td>
  <td><a href="<?php echo $linkVar; ?>" class="<?php echo $dayClass; ?>"><?php echo $dayText; ?></a></td>
 </tr>


<?php displayFooter(); ?>

