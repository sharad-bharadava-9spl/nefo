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
	
	$closingtime = date('Y-m-d H:i:s');
	$_SESSION['closingtime'] = $closingtime;
	$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
	
	// Find opening details 
	$openingid = $_SESSION['openingid'];
	$openingtime = $_SESSION['openingtime'];
	$tillBalance = $_SESSION['tillBalance'];
	$bankBalance = $_SESSION['bankBalance'];
	$dayopeningid = $_SESSION['dayopeningid'];
	$dayopeningtime = $_SESSION['dayopeningtime'];
	$daytillBalance = $_SESSION['daytillBalance'];
	$daybankBalance = $_SESSION['daybankBalance'];
	
	$dayOpeningtimeView = date('d-m-Y H:i', strtotime($dayopeningtime . "+$offsetSec seconds"));
	$shiftOpeningtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));

	// Find opening time of day + closing id
	$openingLookup = "SELECT disClosed, dis2Closed, recClosed, dayClosed, dayClosedNo FROM opening WHERE openingid = $dayopeningid";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$disClosed = $row['disClosed'];
		$dis2Closed = $row['dis2Closed'];
		$recClosed = $row['recClosed'];
		$dayClosed = $row['dayClosed'];
		$dayClosedNo = $row['dayClosedNo'];
		
	// Find shiftclose id
	$openingLookup = "SELECT shiftClosedNo FROM shiftopen WHERE openingid = $openingid";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$shiftClosedNo = $row['shiftClosedNo'];
	
		
		
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

	// Determine day duration
	$datetime1 = new DateTime($dayopeningtime);
	$datetime2 = new DateTime($closingtime);
	$interval = $datetime1->diff($datetime2);
	
	$noOfMonths = $interval->format('%m');
	$noOfDays = $interval->format('%d');
	$noOfHours = $interval->format('%h');
	$noOfMins = $interval->format('%i');
	
	if ($noOfMonths == 0) {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	} else if ($noOfMonths == 1) {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	} else {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	}
	

	$pageHeader = <<<EOD
<div class="textInset">
 <center><strong>{$lang['close-day-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$dayOpeningtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-opened']}:</td>
   <td style='text-align: left;'>{$shiftOpeningtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td colspan='2'><br /></td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-duration']}:</td>
   <td style='text-align: left;'>{$shiftDuration}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-duration']}:</td>
   <td style='text-align: left;'>{$shiftDurationDay}</td>
  </tr>
 </table>
</div>
EOD;


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
	
	pageStart($lang['close-shift-and-day'], NULL, $validationScript, "pcloseday", "step1", $lang['close-shift-and-day'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo $_SESSION['pageHeader'];
	
	if ($dayClosed == '2') {
		
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
	
		if ($recClosed == '2') {
			$recClass = 'closed';
			$recText = $lang['closeday-main-closed'];
			$reclinkVar = "close-shift-and-day-reception.php";
		} else if ($recClosed == 1) {
			$recClass = 'inprocess';
			$recText = $lang['closeday-main-inprocess'];
			$reclinkVar = "close-shift-and-day-reception.php";
		} else {
			$recClass = 'notclosed';
			$recText = $lang['closeday-main-notstarted'];
			$reclinkVar = "close-shift-and-day-reception.php";
		}
		
		if ($disClosed == '2') {
			$disClass = 'closed';
			$disText = $lang['closeday-main-closed'];
			$dislinkVar = "close-shift-and-day-dispensary.php";
		} else if ($disClosed == 1) {
			$disClass = 'inprocess';
			$disText = $lang['closeday-main-inprocess'];
			$dislinkVar = "close-shift-and-day-dispensary.php";
		} else {
			$disClass = 'notclosed';
			$disText = $lang['closeday-main-notstarted'];
			$dislinkVar = "close-shift-and-day-dispensary.php";
		}
		
		if ($dis2Closed == '2') {
			$dis2Class = 'closed';
			$dis2Text = $lang['closeday-main-closed'];
			$dis2linkVar = "close-shift-and-day-dispensary-units.php";
		} else if ($dis2Closed == 1) {
			$dis2Class = 'inprocess';
			$dis2Text = $lang['closeday-main-inprocess'];
			$dis2linkVar = "close-shift-and-day-dispensary-units.php";
		} else {
			$dis2Class = 'notclosed';
			$dis2Text = $lang['closeday-main-notstarted'];
			$dis2linkVar = "close-shift-and-day-dispensary-units.php";
		}
		
		if (($dayClosed == '1') && ($recClosed < 2 || $disClosed < 2 || $dis2Closed < 2)) {
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
		} else if ($dayClosed == '1') {
			$dayClass = 'inprocess';
			$dayText = $lang['closeday-main-inprocess'];
			$linkVar = "close-shift-and-day-summary.php?cid=$dayClosedNo&oid=$dayopeningid&csid=$shiftClosedNo&osid=$openingid";
		} else if ($disClosed == '2' && $recClosed == '2' && $dis2Closed == '2') {
			$dayClass = 'notclosed';
			$dayText = $lang['closeday-main-notstarted'];
			$linkVar = "close-shift-and-day-summary.php?cid=$dayClosedNo&oid=$dayopeningid&csid=$shiftClosedNo&osid=$openingid";
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
  <td><strong><?php echo $lang['summary']; ?>:</strong></td>
  <td><a href="<?php echo $linkVar; ?>" class="<?php echo $dayClass; ?>"><?php echo $dayText; ?></a></td>
 </tr>
</table>

<?php displayFooter(); ?>

