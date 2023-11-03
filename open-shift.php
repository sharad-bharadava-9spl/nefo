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
	
		
		pageStart($lang['start-shift'], NULL, $validationScript, "pcloseday", "step1 dev-align-center", $lang['start-shift'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		if ($dayOpened == '2') {
			
			$dayClass = 'closed';
			$dayText = $lang['openday-main-opened'];
			$linkVar = "#";
			$dayIcon = 'images/cartel-closed.svg';

			$recClass = 'closed';
			$recText = $lang['openday-main-opened'];
			$reclinkVar = "#";
			$recIcon = 'images/cartel-closed.svg';

			$disClass = 'closed';
			$disText = $lang['openday-main-opened'];
			$dislinkVar = "#";
			$disIcon = 'images/cartel-closed.svg';

			$dis2Class = 'closed';
			$dis2Text = $lang['openday-main-opened'];
			$dis2linkVar = "#";
			$dis2Icon = 'images/cartel-closed.svg';
			
		} else {
		
			if ($recOpened == '2') {
				$recClass = 'closed';
				$recText = $lang['openday-main-opened'];
				$reclinkVar = "open-shift-reception.php";
				$recIcon = 'images/cartel-closed.svg';
			} else if ($recOpened == 1) {
				$recClass = 'inprocess';
				$recText = $lang['closeday-main-inprocess'];
				$reclinkVar = "open-shift-reception.php";
				$recIcon = 'images/in-progress.png';
			} else {
				$recClass = 'notclosed';
				$recText = $lang['closeday-main-notstarted'];
				$reclinkVar = "open-shift-reception.php";
				$recIcon = 'images/exclamationtriangle.svg';
			}
			
			if ($disOpened == '2') {
				$disClass = 'closed';
				$disText = $lang['openday-main-opened'];
				$dislinkVar = "open-shift-dispensary.php";
				$disIcon = 'images/cartel-closed.svg';
			} else if ($disOpened == 1) {
				$disClass = 'inprocess';
				$disText = $lang['closeday-main-inprocess'];
				$dislinkVar = "open-shift-dispensary.php";
				$disIcon = 'images/in-progress.png';
			} else {
				$disClass = 'notclosed';
				$disText = $lang['closeday-main-notstarted'];
				$dislinkVar = "open-shift-dispensary.php";
				$disIcon = 'images/exclamationtriangle.svg';
			}
			
			if ($dis2Opened == '2') {
				$dis2Class = 'closed';
				$dis2Text = $lang['openday-main-opened'];
				$dis2linkVar = "open-shift-dispensary-units.php";
				$dis2Icon = 'images/cartel-closed.svg';

			} else if ($dis2Opened == 1) {
				$dis2Class = 'inprocess';
				$dis2Text = $lang['closeday-main-inprocess'];
				$dis2linkVar = "open-shift-dispensary-units.php";
				$dis2Icon = 'images/in-progress.png';
			} else {
				$dis2Class = 'notclosed';
				$dis2Text = $lang['closeday-main-notstarted'];
				$dis2linkVar = "open-shift-dispensary-units.php";
				$dis2Icon = 'images/exclamationtriangle.svg';
			}
			
			if (($dayOpened == '1') && ($recOpened < 2 || $disOpened < 2 || $dis2Opened < 2)) {
				$dayClass = 'notready';
				$dayText = $lang['closeday-main-notavailable'];
				$linkVar = "#";
				$dayIcon = 'images/exclamationtriangle.svg';
			} else if ($dayOpened == '1') {
				$dayClass = 'inprocess';
				$dayText = $lang['closeday-main-inprocess'];
				$linkVar = "open-shift-summary.php?oid=$dayOpenedNo&cid=$closingid";
				$dayIcon = "images/in-progress.png";
			} else if ($recOpened == '2' && $disOpened == '2' && $dis2Opened == '2') {
				$dayClass = 'notclosed';
				$dayText = $lang['closeday-main-notstarted'];
				$linkVar = "open-shift-summary.php?oid=$dayOpenedNo&cid=$closingid";
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

<div class="actionbox-np2">
 <div class="mainboxheader"><?php echo $lang['opening-process']; ?></div>
	<div class="boxcontent"  style="display: flex;">
		<div>
			<p><span class='customicon'><img src="images/recepcionicon.svg"></span>&nbsp;<strong><?php echo $lang['reception']; ?></strong></p><br>
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
			<p><span class='customicon'><img src="images/bolsaicon.svg"></span>&nbsp;<strong><?php echo $lang['dispensary']; ?> <span class='usergrouptext2'>gr.</span></strong></p><br>	 
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
			<p><span class='customicon'><img src="images/bolsaicon.svg"></span>&nbsp;<strong><?php echo $lang['dispensary']; ?> <span class='usergrouptext2'>Un.</span></strong></p><br> 		 
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
			<p><img src="images/list-icon.svg">&nbsp;&nbsp;&nbsp;<strong><?php echo $lang['summary']; ?></strong></p><br>		 
			<a href="<?php echo $linkVar; ?>" class="<?php echo $dayClass; ?> ctalink ctalink-white">
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
<?php displayFooter();
