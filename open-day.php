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
	<div class="actionbox-np2">
	  <div class='mainboxheader'>{$lang['close-day-details']}</div>
	  <div class="boxcontent">
	 <table class='purchasetable'>
		 <tr>
			   <td class='biggerFont'><strong>{$lang['last-closing']}</strong>&nbsp;
			  		{$closingtimeView}
			   </td>
			   </tr>
			   <tr>
			   	 <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
			   		{$openingtimeView}
			   	</td>
		  </tr>
	 </table>
	 </div>
	</div>
	EOD;
		
		
		pageStart($lang['title-openday'], NULL, NULL, "pcloseday", "step1 dev-align-center", $lang['title-openday'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		echo $pageHeader;
		
		if ($dayOpened == '2') {
			
			$recClass = 'closed';
			$recText = $lang['openday-main-opened'];
			$reclinkVar = "#";
			$recIcon = 'images/cartelopen.svg';

			$disClass = 'closed';
			$disText = $lang['openday-main-opened'];
			$dislinkVar = "#";
			$disIcon = 'images/cartelopen.svg';
			
			$dis2Class = 'closed';
			$dis2Text = $lang['openday-main-opened'];
			$dis2linkVar = "#";
			$dis2Icon = 'images/cartelopen.svg';
			
			$barClass = 'closed';
			$barText = $lang['openday-main-opened'];
			$barlinkVar = "#";
			$barIcon = 'images/cartelopen.svg';
				
			$dayClass = 'closed';
			$dayText = $lang['openday-main-opened'];
			$linkVar = "#";
			$dayIcon = 'images/cartelopen.svg';
			
		} else {
		
			if ($recOpened == '2') {
				$recClass = 'closed';
				$recText = $lang['openday-main-opened'];
				$reclinkVar = "open-day-reception.php";
				$recIcon =  'images/cartelopen.svg';
			} else if ($recOpened == 1) {
				$recClass = 'inprocess';
				$recText = $lang['closeday-main-inprocess'];
				$reclinkVar = "open-day-reception.php";
				$recIcon = 'images/in-progress.png';
			} else {
				$recClass = 'notclosed';
				$recText = $lang['closeday-main-notstarted'];
				$reclinkVar = "open-day-reception.php";
				$recIcon = 'images/exclamationtriangle.svg';
			}
			
			if ($disOpened == '2') {
				$disClass = 'closed';
				$disText = $lang['openday-main-opened'];
				$dislinkVar = "open-day-dispensary.php";
				$disIcon = "images/cartelopen.svg";
			} else if ($disOpened == 1) {
				$disClass = 'inprocess';
				$disText = $lang['closeday-main-inprocess'];
				$dislinkVar = "open-day-dispensary.php";
				$disIcon = 'images/in-progress.png';
			} else {
				$disClass = 'notclosed';
				$disText = $lang['closeday-main-notstarted'];
				$dislinkVar = "open-day-dispensary.php";
				$disIcon = 'images/exclamationtriangle.svg';
			}
			
			if ($dis2Opened == '2') {
				$dis2Class = 'closed';
				$dis2Text = $lang['openday-main-opened'];
				$dis2linkVar = "open-day-dispensary-units.php";
				$dis2Icon = "images/cartelopen.svg";
			} else if ($dis2Opened == 1) {
				$dis2Class = 'inprocess';
				$dis2Text = $lang['closeday-main-inprocess'];
				$dis2linkVar = "open-day-dispensary-units.php";
				$dis2Icon = 'images/in-progress.png';
			} else {
				$dis2Class = 'notclosed';
				$dis2Text = $lang['closeday-main-notstarted'];
				$dis2linkVar = "open-day-dispensary-units.php";
				$dis2Icon = 'images/exclamationtriangle.svg';
			}
			
			if ($barOpened == '2') {
				$barClass = 'closed';
				$barText = $lang['openday-main-opened'];
				$barlinkVar = "open-day-bar.php";
				$barIcon = "images/cartelopen.svg";
			} else if ($barOpened == 1) {
				$barClass = 'inprocess';
				$barText = $lang['closeday-main-inprocess'];
				$barlinkVar = "open-day-bar.php";
				$barIcon = "images/in-progress.png";
			} else {
				$barClass = 'notclosed';
				$barText = $lang['closeday-main-notstarted'];
				$barlinkVar = "open-day-bar.php";
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
				$linkVar = "open-day-summary.php?oid=$dayOpenedNo&cid=$closingid";
				$dayIcon = "images/in-progress.png";
			} else if ($recOpened == '2' && $disOpened == '2' && $dis2Opened == '2') {
				$dayClass = 'notclosed';
				$dayText = $lang['closeday-main-notstarted'];
				$linkVar = "open-day-summary.php?oid=$dayOpenedNo&cid=$closingid";
				$dayIcon = "images/exclamationtriangle.svg";
			} else {
				$dayClass = 'notready';
				$dayText = $lang['closeday-main-notavailable'];
				$linkVar = "#";
				$dayIcon = "images/exclamationtriangle.svg";
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
			$recIcon = "images/exclamationtriangle.svg";

			$disClass = 'notclosed';
			$disText = $lang['closeday-main-notstarted'];
			$dislinkVar = "open-day-dispensary.php";
			$disIcon = "images/exclamationtriangle.svg";
			
			$dis2Class = 'notclosed';
			$dis2Text = $lang['closeday-main-notstarted'];
			$dis2linkVar = "open-day-dispensary-units.php";
			$dis2Icon = "images/exclamationtriangle.svg";
			
			$barClass = 'notclosed';
			$barText = $lang['closeday-main-notstarted'];
			$barlinkVar = "open-day-bar.php";
			$barIcon = "images/exclamationtriangle.svg";
			
			$dayClass = 'notready';
			$dayText = $lang['closeday-main-notavailable'];
			$linkVar = "#";
			$dayIcon = "images/exclamationtriangle.svg";
			
		} else {
			
			if ($dayOpened == '2') {
				
				$recClass = 'closed';
				$recText = $lang['openday-main-opened'];
				$reclinkVar = "#";
				$recIcon = 'images/cartelopen.svg';
				
				$disClass = 'closed';
				$disText = $lang['openday-main-opened'];
				$dislinkVar = "#";
				$disIcon = 'images/cartelopen.svg';
				
				$dis2Class = 'closed';
				$dis2Text = $lang['openday-main-opened'];
				$dis2linkVar = "#";
				$dis2Icon = 'images/cartelopen.svg';
				
				$barClass = 'closed';
				$barText = $lang['openday-main-opened'];
				$barlinkVar = "#";
				$barIcon ='images/cartelopen.svg';
				
				$dayClass = 'closed';
				$dayText = $lang['openday-main-opened'];
				$linkVar = "#";
				$dayIcon ='images/cartelopen.svg';
				
			} else {
			
				if ($recOpened == '2') {
					$recClass = 'closed';
					$recText = $lang['openday-main-opened'];
					$reclinkVar = "open-day-reception.php";
					$recIcon = 'images/cartelopen.svg';
				} else if ($recOpened == 1) {
					$recClass = 'inprocess';
					$recText = $lang['closeday-main-inprocess'];
					$reclinkVar = "open-day-reception.php";
					$recIcon ='images/in-progress.png';
				} else {
					$recClass = 'notclosed';
					$recText = $lang['closeday-main-notstarted'];
					$reclinkVar = "open-day-reception.php";
					$recIcon = 'images/exclamationtriangle.svg';
				}
				
				if ($disOpened == '2') {
					$disClass = 'closed';
					$disText = $lang['openday-main-opened'];
					$dislinkVar = "open-day-dispensary.php";
					$disIcon = 'images/cartelopen.svg';
				} else if ($disOpened == 1) {
					$disClass = 'inprocess';
					$disText = $lang['closeday-main-inprocess'];
					$dislinkVar = "open-day-dispensary.php";
					$disIcon = 'images/in-progress.png';
				} else {
					$disClass = 'notclosed';
					$disText = $lang['closeday-main-notstarted'];
					$dislinkVar = "open-day-dispensary.php";
					$disIcon = 'images/exclamationtriangle.svg';
				}
				
				if ($dis2Opened == '2') {
					$dis2Class = 'closed';
					$dis2Text = $lang['openday-main-opened'];
					$dis2linkVar = "open-day-dispensary-units.php";
					$dis2Icon = 'images/cartelopen.svg';
				} else if ($dis2Opened == 1) {
					$dis2Class = 'inprocess';
					$dis2Text = $lang['closeday-main-inprocess'];
					$dis2linkVar = "open-day-dispensary-units.php";
					$dis2Icon = 'images/in-progress.png';
				} else {
					$dis2Class = 'notclosed';
					$dis2Text = $lang['closeday-main-notstarted'];
					$dis2linkVar = "open-day-dispensary-units.php";
					$dis2Icon = 'images/exclamationtriangle.svg';
				}
				
				if ($barOpened == '2') {
					$barClass = 'closed';
					$barText = $lang['openday-main-opened'];
					$barlinkVar = "open-day-bar.php";
					$barIcon = 'images/cartelopen.svg';
				} else if ($barOpened == 1) {
					$barClass = 'inprocess';
					$barText = $lang['closeday-main-inprocess'];
					$barlinkVar = "open-day-bar.php";
					$barIcon = 'images/in-progress.png';
				} else {
					$barClass = 'notclosed';
					$barText = $lang['closeday-main-notstarted'];
					$barlinkVar = "open-day-bar.php";
					$barIcon = 'images/exclamationtriangle.svg';
				}
				
				if (($dayOpened == '1') && ($recOpened < 2 || $disOpened < 2 || $dis2Opened < 2)) {
					$dayClass = 'notready';
					$dayText = $lang['closeday-main-notavailable'];
					$linkVar = "#";
					$dayIcon = 'images/exclamationtriangle.svg';
				} else if ($dayOpened == '1') {
					$dayClass = 'inprocess';
					$dayText = $lang['closeday-main-inprocess'];
					$linkVar = "open-day-summary.php?oid=$dayOpenedNo";
					$dayIcon = 'images/in-progress.png';
				} else if ($recOpened == '2' && $disOpened == '2' && $dis2Opened == '2') {
					$dayClass = 'notclosed';
					$dayText = $lang['closeday-main-notstarted'];
					$linkVar = "open-day-summary.php?oid=$dayOpenedNo";
					$dayIcon = 'images/exclamationtriangle.svg';
				} else {
					$dayClass = 'notready';
					$dayText = $lang['closeday-main-notavailable'];
					$linkVar = "#";
					$dayIcon = 'images/exclamationtriangle.svg';
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
	
		
		pageStart($lang['title-openday'], NULL, $validationScript, "pcloseday", "step1 dev-align-center", $lang['title-openday'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	}


?>
<br /><br />

<!-- <div class="actionbox-np2">
 <div class="mainboxheader"><?php echo $lang['opening-process']; ?></div>
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
</div> -->
<div class="actionbox-np2">
 <div class="mainboxheader"><?php echo $lang['opening-process']; ?></div>
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
			<p><span class='customicon'><img src="images/bolsaicon.svg"></span>&nbsp;&nbsp;<strong><?php echo $lang['dispensary']; ?> &nbsp;<span class='usergrouptext2'>gr.</span></strong></p><br>
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
			<p><span class='customicon'><img src="images/bolsaicon.svg"></span>&nbsp;&nbsp;<strong><?php echo $lang['dispensary']; ?> &nbsp;<span class='usergrouptext2'>Un.</span></strong></p><br> 
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

