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
	$openingid = $_SESSION['openingid'];
	$openingtime = $_SESSION['openingtime'];
	$tillBalance = $_SESSION['tillBalance'];
		
	if ($_SESSION['type'] == 'opening') {
		
		$closingLookup = "SELECT bankBalance, openedby, dayClosed, recShiftClosed, disShiftClosed, shiftClosedNo FROM opening WHERE openingid = '$openingid'";
		
		$result = mysql_query($closingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$dayOpened = $row['dayClosed'];
			$openedby = $row['openedby'];
			$dayOpenedNo = $row['shiftClosedNo'];
			$recOpened = $row['recShiftClosed'];
			$disOpened = $row['disShiftClosed'];		
			
	} else {

		$closingLookup = "SELECT bankBalance, openedby, shiftClosed, recClosed, disClosed, shiftClosedNo FROM shiftopen WHERE openingid = '$openingid'";

		$result = mysql_query($closingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$dayOpened = $row['shiftClosed'];
			$openedby = $row['openedby'];
			$dayOpenedNo = $row['shiftClosedNo'];
			$recOpened = $row['recClosed'];
			$disOpened = $row['disClosed'];		
			
	}
	
	$_SESSION['bankBalance'] = $row['bankBalance'];

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
 <center><strong>{$lang['close-shift-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['shift-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-duration']}:</td>
   <td style='text-align: left;'>{$shiftDuration}</td>
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
	
	pageStart($lang['close-shift'], NULL, $validationScript, "pcloseday", "step1", $lang['close-shift'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
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
			$reclinkVar = "close-shift-reception.php";
		} else if ($recOpened == 1) {
			$recClass = 'inprocess';
			$recText = $lang['closeday-main-inprocess'];
			$reclinkVar = "close-shift-reception.php";
		} else {
			$recClass = 'notclosed';
			$recText = $lang['closeday-main-notstarted'];
			$reclinkVar = "close-shift-reception.php";
		}
		
		if ($disOpened == '2') {
			$disClass = 'closed';
			$disText = $lang['closeday-main-closed'];
			$dislinkVar = "close-shift-dispensary.php";
		} else if ($disOpened == 1) {
			$disClass = 'inprocess';
			$disText = $lang['closeday-main-inprocess'];
			$dislinkVar = "close-shift-dispensary.php";
		} else {
			$disClass = 'notclosed';
			$disText = $lang['closeday-main-notstarted'];
			$dislinkVar = "close-shift-dispensary.php";
		}
		
		if ($dayOpened == '1') {
			$dayClass = 'inprocess';
			$dayText = $lang['closeday-main-inprocess'];
			$linkVar = "close-shift-summary.php?cid=$dayOpenedNo&oid=$openingid";
		} else if ($disOpened == '2' && $recOpened == '2') {
			$dayClass = 'notclosed';
			$dayText = $lang['closeday-main-notstarted'];
			$linkVar = "close-shift-summary.php?cid=$dayOpenedNo&oid=$openingid";
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

