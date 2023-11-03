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
			
		$openingLookup = "SELECT closingid, closingtime, dayOpened, dayOpenedBy, recOpened, disOpened, dis2Opened, barOpened, dayOpenedNo FROM closing ORDER BY closingtime DESC LIMIT 1";
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
			$closingid = $row['closingid'];
			$_SESSION['closingid'] = $closingid;
			$closingtime = $row['closingtime'];
			$_SESSION['closingtime'] = $closingtime;
			$dayOpened = $row['dayOpened'];
			$recOpened = $row['recOpened'];
			$disOpened = $row['disOpened'];		
			$dis2Opened = $row['dis2Opened'];		
			$barOpened = $row['barOpened'];		
			$dayOpenedNo = $row['dayOpenedNo'];
			$dayOpenedBy = $row['dayOpenedBy'];
			
		if ($_SESSION['firstOpening'] == 'true') {
			
			$openingLookup = "SELECT openingid FROM opening ORDER BY openingtime DESC LIMIT 1";
			
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

		
		
		pageStart($lang['title-openday'], NULL, NULL, "pcloseday", "step1", $lang['title-openday'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		echo $pageHeader;
		
		if ($dayOpened == '2') {
			
			$recClass = 'closed';
			$recText = $lang['openday-main-opened'];
			$reclinkVar = "#";
			
			$disClass = 'closed';
			$disText = $lang['openday-main-opened'];
			$dislinkVar = "#";
			
			$dis2Class = 'closed';
			$dis2Text = $lang['openday-main-opened'];
			$dis2linkVar = "#";
			
			$barClass = 'closed';
			$barText = $lang['openday-main-opened'];
			$barlinkVar = "#";
				
			$dayClass = 'closed';
			$dayText = $lang['openday-main-opened'];
			$linkVar = "#";
			
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
			
			if ($dis2Opened == '2') {
				$dis2Class = 'closed';
				$dis2Text = $lang['openday-main-opened'];
				$dis2linkVar = "open-day-dispensary-units.php";
			} else if ($dis2Opened == 1) {
				$dis2Class = 'inprocess';
				$dis2Text = $lang['closeday-main-inprocess'];
				$dis2linkVar = "open-day-dispensary-units.php";
			} else {
				$dis2Class = 'notclosed';
				$dis2Text = $lang['closeday-main-notstarted'];
				$dis2linkVar = "open-day-dispensary-units.php";
			}
			
			if ($barOpened == '2') {
				$barClass = 'closed';
				$barText = $lang['openday-main-opened'];
				$barlinkVar = "open-day-bar.php";
			} else if ($barOpened == 1) {
				$barClass = 'inprocess';
				$barText = $lang['closeday-main-inprocess'];
				$barlinkVar = "open-day-bar.php";
			} else {
				$barClass = 'notclosed';
				$barText = $lang['closeday-main-notstarted'];
				$barlinkVar = "open-day-bar.php";
			}
			
			if (($dayOpened == '1') && ($recOpened < 2 || $disOpened < 2 || $dis2Opened < 2)) {
				$dayClass = 'notready';
				$dayText = $lang['closeday-main-notavailable'];
				$linkVar = "#";
			} else if ($dayOpened == '1') {
				$dayClass = 'inprocess';
				$dayText = $lang['closeday-main-inprocess'];
				$linkVar = "open-day-summary.php?oid=$dayOpenedNo&cid=$closingid";
			} else if ($recOpened == '2' && $disOpened == '2' && $dis2Opened == '2') {
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
		
		$selectUsers = "SELECT COUNT(openingid) FROM opening";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
		// First opening ever. Check opening table for these new variables.
		$query = "SELECT openingid, firstDisOpen, firstDisOpenBy, firstRecOpen, firstRecOpenBy, firstDayOpen, firstDayOpenBy, firstDis2Open, firstDis2OpenBy, firstBarOpen, firstBarOpenBy FROM opening ORDER by openingtime DESC";
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
			$dayOpenedNo = $row['openingid'];
			$dayOpened = $row['firstDayOpen'];
			$recOpened = $row['firstRecOpen'];
			$disOpened = $row['firstDisOpen'];		
			$dis2Opened = $row['firstDis2Open'];		
			$barOpened = $row['firstBarOpen'];		
			$dayOpenedBy = $row['firstDayOpenBy'];
			
		if ($rowCount == 0) {
			
			$recClass = 'notclosed';
			$recText = $lang['closeday-main-notstarted'];
			$reclinkVar = "open-day-reception.php";
			
			$disClass = 'notclosed';
			$disText = $lang['closeday-main-notstarted'];
			$dislinkVar = "open-day-dispensary.php";
			
			$dis2Class = 'notclosed';
			$dis2Text = $lang['closeday-main-notstarted'];
			$dis2linkVar = "open-day-dispensary-units.php";
			
			$barClass = 'notclosed';
			$barText = $lang['closeday-main-notstarted'];
			$barlinkVar = "open-day-bar.php";
			
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
			
		} else {
			
			if ($dayOpened == '2') {
				
				$recClass = 'closed';
				$recText = $lang['openday-main-opened'];
				$reclinkVar = "#";
				
				$disClass = 'closed';
				$disText = $lang['openday-main-opened'];
				$dislinkVar = "#";
				
				$dis2Class = 'closed';
				$dis2Text = $lang['openday-main-opened'];
				$dis2linkVar = "#";
				
				$barClass = 'closed';
				$barText = $lang['openday-main-opened'];
				$barlinkVar = "#";
				
				$dayClass = 'closed';
				$dayText = $lang['openday-main-opened'];
				$linkVar = "#";
				
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
				
				if ($dis2Opened == '2') {
					$dis2Class = 'closed';
					$dis2Text = $lang['openday-main-opened'];
					$dis2linkVar = "open-day-dispensary-units.php";
				} else if ($dis2Opened == 1) {
					$dis2Class = 'inprocess';
					$dis2Text = $lang['closeday-main-inprocess'];
					$dis2linkVar = "open-day-dispensary-units.php";
				} else {
					$dis2Class = 'notclosed';
					$dis2Text = $lang['closeday-main-notstarted'];
					$dis2linkVar = "open-day-dispensary-units.php";
				}
				
				if ($barOpened == '2') {
					$barClass = 'closed';
					$barText = $lang['openday-main-opened'];
					$barlinkVar = "open-day-bar.php";
				} else if ($barOpened == 1) {
					$barClass = 'inprocess';
					$barText = $lang['closeday-main-inprocess'];
					$barlinkVar = "open-day-bar.php";
				} else {
					$barClass = 'notclosed';
					$barText = $lang['closeday-main-notstarted'];
					$barlinkVar = "open-day-bar.php";
				}
				
				if (($dayOpened == '1') && ($recOpened < 2 || $disOpened < 2 || $dis2Opened < 2)) {
					$dayClass = 'notready';
					$dayText = $lang['closeday-main-notavailable'];
					$linkVar = "#";
				} else if ($dayOpened == '1') {
					$dayClass = 'inprocess';
					$dayText = $lang['closeday-main-inprocess'];
					$linkVar = "open-day-summary.php?oid=$dayOpenedNo";
				} else if ($recOpened == '2' && $disOpened == '2' && $dis2Opened == '2') {
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
<!--
 <tr>
  <td><strong>Bar:</strong></td>
  <td><a href="<?php echo $barlinkVar; ?>" class="<?php echo $barClass; ?>"><?php echo $barText; ?></a></td>
 </tr>
-->
 <tr>
  <td style="padding: 5px !important;" colspan="2"></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['summary']; ?>:</strong></td>
  <td><a href="<?php echo $linkVar; ?>" class="<?php echo $dayClass; ?>"><?php echo $dayText; ?></a></td>
 </tr>
</table>


<?php displayFooter(); ?>

