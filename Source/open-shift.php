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
	
	$openingLookup = "SELECT closingid, closingtime, shiftOpened, shiftOpenedBy, recOpened, disOpened, dis2Opened, shiftOpenedNo FROM shiftclose ORDER BY closingtime DESC LIMIT 1";
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
		$closingtime = $row['closingtime'];
		$dayOpened = $row['shiftOpened'];
		$recOpened = $row['recOpened'];
		$disOpened = $row['disOpened'];		
		$dis2Opened = $row['dis2Opened'];		
		$dayOpenedNo = $row['shiftOpenedNo'];
		$dayOpenedBy = $row['shiftOpenedBy'];
		
		$_SESSION['closingid'] = $closingid;
		$_SESSION['closingtime'] = $closingtime;

		
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
	
		
		pageStart($lang['start-shift'], NULL, $validationScript, "pcloseday", "step1", $lang['start-shift'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
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
			
			$dis2Class = 'closed';
			$dis2Text = $lang['openday-main-opened'];
			$dis2linkVar = "#";
			
		} else {
		
			if ($recOpened == '2') {
				$recClass = 'closed';
				$recText = $lang['openday-main-opened'];
				$reclinkVar = "open-shift-reception.php";
			} else if ($recOpened == 1) {
				$recClass = 'inprocess';
				$recText = $lang['closeday-main-inprocess'];
				$reclinkVar = "open-shift-reception.php";
			} else {
				$recClass = 'notclosed';
				$recText = $lang['closeday-main-notstarted'];
				$reclinkVar = "open-shift-reception.php";
			}
			
			if ($disOpened == '2') {
				$disClass = 'closed';
				$disText = $lang['openday-main-opened'];
				$dislinkVar = "open-shift-dispensary.php";
			} else if ($disOpened == 1) {
				$disClass = 'inprocess';
				$disText = $lang['closeday-main-inprocess'];
				$dislinkVar = "open-shift-dispensary.php";
			} else {
				$disClass = 'notclosed';
				$disText = $lang['closeday-main-notstarted'];
				$dislinkVar = "open-shift-dispensary.php";
			}
			
			if ($dis2Opened == '2') {
				$dis2Class = 'closed';
				$dis2Text = $lang['openday-main-opened'];
				$dis2linkVar = "open-shift-dispensary-units.php";
			} else if ($dis2Opened == 1) {
				$dis2Class = 'inprocess';
				$dis2Text = $lang['closeday-main-inprocess'];
				$dis2linkVar = "open-shift-dispensary-units.php";
			} else {
				$dis2Class = 'notclosed';
				$dis2Text = $lang['closeday-main-notstarted'];
				$dis2linkVar = "open-shift-dispensary-units.php";
			}
			
			if (($dayOpened == '1') && ($recOpened < 2 || $disOpened < 2 || $dis2Opened < 2)) {
				$dayClass = 'notready';
				$dayText = $lang['closeday-main-notavailable'];
				$linkVar = "#";
			} else if ($dayOpened == '1') {
				$dayClass = 'inprocess';
				$dayText = $lang['closeday-main-inprocess'];
				$linkVar = "open-shift-summary.php?oid=$dayOpenedNo&cid=$closingid";
			} else if ($recOpened == '2' && $disOpened == '2' && $dis2Opened == '2') {
				$dayClass = 'notclosed';
				$dayText = $lang['closeday-main-notstarted'];
				$linkVar = "open-shift-summary.php?oid=$dayOpenedNo&cid=$closingid";
			} else {
				$dayClass = 'notready';
				$dayText = $lang['closeday-main-notavailable'];
				$linkVar = "#";
			}
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
 <tr>
  <td><strong><?php echo $lang['summary']; ?>:</strong></td>
  <td><a href="<?php echo $linkVar; ?>" class="<?php echo $dayClass; ?>"><?php echo $dayText; ?></a></td>
 </tr>
</table>

<?php displayFooter();