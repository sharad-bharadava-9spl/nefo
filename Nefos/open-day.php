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
	
	if ($_SESSION['noCompare'] != 'true') {
			
		$openingLookup = "SELECT closingid, closingtime, dayOpened, dayOpenedBy, recOpened, disOpened, dayOpenedNo FROM closing ORDER BY closingtime DESC LIMIT 1";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$closingid = $row['closingid'];
			$_SESSION['closingid'] = $closingid;
			$closingtime = $row['closingtime'];
			$_SESSION['closingtime'] = $closingtime;
			$dayOpened = $row['dayOpened'];
			$recOpened = $row['recOpened'];
			$disOpened = $row['disOpened'];		
			$dayOpenedNo = $row['dayOpenedNo'];
			$dayOpenedBy = $row['dayOpenedBy'];
			
		if ($_SESSION['firstOpening'] == 'true') {
			
			$openingLookup = "SELECT openingid FROM opening ORDER BY openingtime DESC LIMIT 1";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$dayOpenedNo = $row['openingid'];
			
		}
		
			$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
					
		
			$pageHeader = <<<EOD
<div class="textInset">
 <center><strong>{$lang['close-day-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['last-closing']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
 </table>
</div>
EOD;

		
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
	
		
		pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step1", $lang['title-openday'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		echo $pageHeader;
		
		if ($dayOpened == '2') {
			
			$dayClass = 'closed';
			$dayText = $lang['openday-main-opened'];
			$linkVar = "#";
			
			$recClass = 'closed';
			$recText = $lang['openday-main-opened'];
			$reclinkVar = "#";
			
			$disClass = 'closed';
			$disText = $lang['openday-main-opened'];
			$dislinkVar = "#";
			
		} else {
		
			if ($recOpened == '2') {
				$recClass = 'closed';
				$recText = $lang['openday-main-opened'];
				$reclinkVar = "open-day-reception.php";
			} else if ($recOpened == 1) {
				$recClass = 'inprocess';
				$recText = $lang['closeday-main-inprocess'];
				$reclinkVar = "open-day-reception.php";
			} else {
				$recClass = 'notclosed';
				$recText = $lang['closeday-main-notstarted'];
				$reclinkVar = "open-day-reception.php";
			}
			
			if ($disOpened == '2') {
				$disClass = 'closed';
				$disText = $lang['openday-main-opened'];
				$dislinkVar = "open-day-dispensary.php";
			} else if ($disOpened == 1) {
				$disClass = 'inprocess';
				$disText = $lang['closeday-main-inprocess'];
				$dislinkVar = "open-day-dispensary.php";
			} else {
				$disClass = 'notclosed';
				$disText = $lang['closeday-main-notstarted'];
				$dislinkVar = "open-day-dispensary.php";
			}
			
			if (($dayOpened == '1') && ($recOpened < 2 || $disOpened < 2)) {
				$dayClass = 'notready';
				$dayText = $lang['closeday-main-notavailable'];
				$linkVar = "#";
			} else if ($dayOpened == '1') {
				$dayClass = 'inprocess';
				$dayText = $lang['closeday-main-inprocess'];
				$linkVar = "open-day-summary.php?oid=$dayOpenedNo&cid=$closingid";
			} else if ($disOpened == '2' && $recOpened == '2') {
				$dayClass = 'notclosed';
				$dayText = $lang['closeday-main-notstarted'];
				$linkVar = "open-day-summary.php?oid=$dayOpenedNo&cid=$closingid";
			} else {
				$dayClass = 'notready';
				$dayText = $lang['closeday-main-notavailable'];
				$linkVar = "#";
			}
		}
		
	} else {
		
		// First opening ever. Check opening table for these new variables.
		$query = "SELECT openingid, firstDisOpen, firstDisOpenBy, firstRecOpen, firstRecOpenBy, firstDayOpen, firstDayOpenBy FROM opening ORDER by openingtime DESC";
		
		$result = mysql_query($query)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$dayOpenedNo = $row['openingid'];
			$dayOpened = $row['firstDayOpen'];
			$recOpened = $row['firstRecOpen'];
			$disOpened = $row['firstDisOpen'];		
			$dayOpenedBy = $row['firstDayOpenBy'];
			
		if (mysql_num_rows($result) == 0) {
			
			$disClass = 'notclosed';
			$disText = $lang['closeday-main-notstarted'];
			$dislinkVar = "open-day-dispensary.php";
			$recClass = 'notclosed';
			$recText = $lang['closeday-main-notstarted'];
			$reclinkVar = "open-day-reception.php";
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
			
		} else {
			
			if ($dayOpened == '2') {
				
				$dayClass = 'closed';
				$dayText = $lang['openday-main-opened'];
				$linkVar = "#";
				
				$recClass = 'closed';
				$recText = $lang['openday-main-opened'];
				$reclinkVar = "#";
				
				$disClass = 'closed';
				$disText = $lang['openday-main-opened'];
				$dislinkVar = "#";
				
			} else {
			
				if ($recOpened == '2') {
					$recClass = 'closed';
					$recText = $lang['openday-main-opened'];
					$reclinkVar = "open-day-reception.php";
				} else if ($recOpened == 1) {
					$recClass = 'inprocess';
					$recText = $lang['closeday-main-inprocess'];
					$reclinkVar = "open-day-reception.php";
				} else {
					$recClass = 'notclosed';
					$recText = $lang['closeday-main-notstarted'];
					$reclinkVar = "open-day-reception.php";
				}
				
				if ($disOpened == '2') {
					$disClass = 'closed';
					$disText = $lang['openday-main-opened'];
					$dislinkVar = "open-day-dispensary.php";
				} else if ($disOpened == 1) {
					$disClass = 'inprocess';
					$disText = $lang['closeday-main-inprocess'];
					$dislinkVar = "open-day-dispensary.php";
				} else {
					$disClass = 'notclosed';
					$disText = $lang['closeday-main-notstarted'];
					$dislinkVar = "open-day-dispensary.php";
				}
				
				if (($dayOpened == '1') && ($recOpened < 2 || $disOpened < 2)) {
					$dayClass = 'notready';
					$dayText = $lang['closeday-main-notavailable'];
					$linkVar = "#";
				} else if ($dayOpened == '1') {
					$dayClass = 'inprocess';
					$dayText = $lang['closeday-main-inprocess'];
					$linkVar = "open-day-summary.php?oid=$dayOpenedNo";
				} else if ($disOpened == '2' && $recOpened == '2') {
					$dayClass = 'notclosed';
					$dayText = $lang['closeday-main-notstarted'];
					$linkVar = "open-day-summary.php?oid=$dayOpenedNo";
				} else {
					$dayClass = 'notready';
					$dayText = $lang['closeday-main-notavailable'];
					$linkVar = "#";
				}
			}
		}
		
		// Else, look for the new variables and their values to generate button colour
		
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
	
		
		pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step1", $lang['title-openday'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	}


?>
<br /><br />

<h1><?php echo $lang['opening-process']; ?></h1>
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

