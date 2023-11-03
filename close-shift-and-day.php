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
<div class="actionbox-np2">
  <div class='mainboxheader'><center>{$lang['close-day-details']}</center></div>
  <div class="boxcontent">
 <table class='purchasetable'>
  <tr>
   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
      {$dayOpeningtimeView}</td>
      </tr>
   <tr>   
    <td class='biggerFont'><strong>{$lang['shift-opened']}</strong>&nbsp;
   		{$shiftOpeningtimeView}</td>
   		</tr>
   <tr>		
   	<td class='biggerFont'><strong>{$lang['shift-opened']}</strong>&nbsp;
   		{$shiftOpeningtimeView}</td>
   		   <td style='text-align: left;'><strong>{$lang['day-closed']}</strong>&nbsp;
   		{$closingtimeView}</td>	
  </tr>
  <tr>
   <td style='text-align: left;'><strong>{$lang['shift-duration']}</strong>
   {$shiftDuration}</td>
   </tr>
   <tr>
   <td style='text-align: left;'><strong>{$lang['day-duration']}</strong>
      {$shiftDurationDay}</td>
  </tr>
 </table>
 </div>
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
	
	pageStart($lang['close-shift-and-day'], NULL, $validationScript, "pcloseday", "step1 dev-align-center", $lang['close-shift-and-day'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo $_SESSION['pageHeader'];
	
	if ($dayClosed == '2') {
		
		$dayClass = 'closed';
		$dayText = $lang['closeday-main-closed'];
		$linkVar = "#";
		$dayIcon = 'images/cartel-closed.svg';

		$recClass = 'closed';
		$recText = $lang['closeday-main-closed'];
		$reclinkVar = "#";
		$recIcon = 'images/cartel-closed.svg';
		
		$disClass = 'closed';
		$disText = $lang['closeday-main-closed'];
		$dislinkVar = "#";
		$disIcon = 'images/cartel-closed.svg';
		
	} else {
	
		if ($recClosed == '2') {
			$recClass = 'closed';
			$recText = $lang['closeday-main-closed'];
			$reclinkVar = "close-shift-and-day-reception.php";
			$recIcon = 'images/cartel-closed.svg';
		} else if ($recClosed == 1) {
			$recClass = 'inprocess';
			$recText = $lang['closeday-main-inprocess'];
			$reclinkVar = "close-shift-and-day-reception.php";
			$recIcon = 'images/in-progress.png';
		} else {
			$recClass = 'notclosed';
			$recText = $lang['closeday-main-notstarted'];
			$reclinkVar = "close-shift-and-day-reception.php";
			$recIcon = 'images/exclamationtriangle.svg';
		}
		
		if ($disClosed == '2') {
			$disClass = 'closed';
			$disText = $lang['closeday-main-closed'];
			$dislinkVar = "close-shift-and-day-dispensary.php";
			$disIcon = 'images/exclamationtriangle.svg';
		} else if ($disClosed == 1) {
			$disClass = 'inprocess';
			$disText = $lang['closeday-main-inprocess'];
			$dislinkVar = "close-shift-and-day-dispensary.php";
			$disIcon = 'images/in-progress.png';
		} else {
			$disClass = 'notclosed';
			$disText = $lang['closeday-main-notstarted'];
			$dislinkVar = "close-shift-and-day-dispensary.php";
			$disIcon = 'images/exclamationtriangle.svg';
		}
		
		if ($dis2Closed == '2') {
			$dis2Class = 'closed';
			$dis2Text = $lang['closeday-main-closed'];
			$dis2linkVar = "close-shift-and-day-dispensary-units.php";
			$dis2Icon = 'images/cartel-closed.svg';
		} else if ($dis2Closed == 1) {
			$dis2Class = 'inprocess';
			$dis2Text = $lang['closeday-main-inprocess'];
			$dis2linkVar = "close-shift-and-day-dispensary-units.php";
			$dis2Icon = 'images/in-progress.png';
		} else {
			$dis2Class = 'notclosed';
			$dis2Text = $lang['closeday-main-notstarted'];
			$dis2linkVar = "close-shift-and-day-dispensary-units.php";
			$dis2Icon = 'images/exclamationtriangle.svg';
		}
		
		if (($dayClosed == '1') && ($recClosed < 2 || $disClosed < 2 || $dis2Closed < 2)) {
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
			$dayIcon = 'images/exclamationtriangle.svg';
		} else if ($dayClosed == '1') {
			$dayClass = 'inprocess';
			$dayText = $lang['closeday-main-inprocess'];
			$linkVar = "close-shift-and-day-summary.php?cid=$dayClosedNo&oid=$dayopeningid&csid=$shiftClosedNo&osid=$openingid";
			$dayIcon = 'images/in-progress.png';
		} else if ($disClosed == '2' && $recClosed == '2' && $dis2Closed == '2') {
			$dayClass = 'notclosed';
			$dayText = $lang['closeday-main-notstarted'];
			$linkVar = "close-shift-and-day-summary.php?cid=$dayClosedNo&oid=$dayopeningid&csid=$shiftClosedNo&osid=$openingid";
			$dayIcon = 'images/exclamationtriangle.svg';
		} else {
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
			$dayIcon = 'images/exclamationtriangle.svg';
		}
	}


?>
<br /><br />
<!-- <center>
<div class="actionbox-np2">
	 <div class="mainboxheader"><center><?php echo $lang['closing-process']; ?></center></div>
	 <div class="boxcontent">
		 <a href="<?php echo $reclinkVar; ?>" class="<?php echo $recClass; ?> ctalink">
			<table>
				 <tr >
				  <td style="height: 70px;"><strong><?php echo $lang['reception']; ?></strong></td>
				</tr>
				<tr>
				  <td><?php echo $recText; ?></a></td>
				 </tr>
			</table>
		</a>		 
		<a href="<?php echo $dislinkVar; ?>" class="<?php echo $disClass; ?> ctalink">
			<table>
				 <tr >
				  <td style="height: 70px;"><strong><?php echo $lang['dispensary']; ?> (g)</strong></td>
				</tr>
				<tr>
				  <td><?php echo $disText; ?></a></td>
				 </tr>
			</table>
		</a>		 
		<a href="<?php echo $dis2linkVar; ?>" class="<?php echo $dis2Class; ?> ctalink">
			<table>
				 <tr >
				  <td style="height: 70px;"><strong><?php echo $lang['dispensary']; ?> (u)</strong></td>
				</tr>
				<tr>
				  <td><?php echo $dis2Text; ?></a></td>
				 </tr>
			</table>
		</a>		 
		<a href="<?php echo $linkVar; ?>" class="<?php echo $dayClass; ?> ctalink ctalink-white">
			<table>
				 <tr >
				  <td style="height: 70px;"><strong><?php echo $lang['summary']; ?></strong></td>
				</tr>
				<tr>
				  <td><?php echo $dayText; ?></a></td>
				 </tr>
			</table>
		</a>
</div>
</div>
</center> -->
<div class="actionbox-np2">
 <div class="mainboxheader"><?php echo $lang['closing-process']; ?></div>
	<div class="boxcontent" style="display: flex;">
		<div>
			<p><span class='customicon'><img src="images/recepcionicon.svg"></span>&nbsp;&nbsp;<strong><?php echo $lang['reception']; ?></strong></p><br>
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
			<p><span class='customicon'><img src="images/bolsaicon.svg"></span>&nbsp;&nbsp;<strong><?php echo $lang['dispensary']; ?>&nbsp; <span class='usergrouptext2'>gr.</span></strong></p><br>
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
			<p><span class='customicon'><img src="images/bolsaicon.svg"></span>&nbsp;&nbsp;<strong><?php echo $lang['dispensary']; ?>&nbsp; <span class='usergrouptext2'>Un.</span></strong></p><br> 
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

