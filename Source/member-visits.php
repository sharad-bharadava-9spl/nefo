<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the user ID
	if (isset($_POST['userid'])) {
		$user_id = $_POST['userid'];
	} else if (isset($_GET['userid'])) {
		$user_id = $_GET['userid'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$timeLimit = "WHERE";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$timeLimit = "WHERE";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$timeLimit = "WHERE";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
						
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "WHERE MONTH(scanin) = $month AND YEAR(scanin) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";		
				
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		$timeLimit = "WHERE";
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
		
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$limitVar = '';

		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "WHERE DATE(scanin) BETWEEN DATE('$fromDate') AND DATE('$untilDate') AND";
			
	}


	$scanIn = "SELECT COUNT(visitNo) FROM newvisits $timeLimit userid = $user_id $limitVar";
	$noOfVisits = $pdo3->query("$scanIn")->fetchColumn();

	// Look up visits type 0 (scan in)
	$scanIn = "SELECT visitNo, userid, scanin, scanout, completed, duration FROM newvisits $timeLimit userid = $user_id ORDER BY scanin DESC $limitVar";
		try
		{
			$scanInResult = $pdo3->prepare("$scanIn");
			$scanInResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	$firstScan = "SELECT scanin FROM newvisits $timeLimit userid = $user_id ORDER BY scanin ASC $limitVar";
		try
		{
			$firstScanDate = $pdo3->prepare("$firstScan");
			$firstScanDate->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $firstScanDate->fetch();
	$noOfDays = floor((time() - strtotime($row['scanin']))/(60*60*24));
	$noOfWeeks = $noOfDays / 7;
	
	if ($noOfDays < 1) {
		$noOfDays = 1;
	}
	
	if ($noOfWeeks < 1) {
		$noOfWeeks = 1;
	}
	
	$dailyVisits = number_format(($noOfVisits / $noOfDays),1);
	$weeklyVisits = number_format(($noOfVisits / $noOfWeeks),1);
     	
	$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit, photoExt FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$paidUntil = $row['paidUntil'];
		$userGroup = $row['userGroup'];
		$credit = $row['credit'];
		$photoExt = $row['photoExt'];
		
		$member = "#" . $memberno . " - " . $first_name . " " . $last_name;
		
	
	

		
	$deleteVisitScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });	    

function delete_visit(visitNo) {
	if (confirm("Estas seguro?")) {
				window.location = "uTil/delete-visit.php?visitNo=" + visitNo;
				}
}
EOD;
	
	pageStart($lang['member-visits'], NULL, $deleteVisitScript, "avalpage", "", $lang['member-visits'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<center>
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent' style='padding-bottom: 0;'>
  <form action='' method='POST' style='margin-top: 3px;'>
   <select id='filter' name='filter' class='defaultinput-no-margin' style='width: 242px;' onchange='this.form.submit()'>
    <?php echo $optionList; ?>
   </select>
  </form><br />
  <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <br /><button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="Desde fecha" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="Hasta fecha" onchange='this.form.submit()' />
		 <br /><button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;

	}
?>
        </form>
 </div>
</div>
</center>
<br />

<?php	
	$topimg = "images/_$domain/members/$user_id.$photoExt";
	if (!file_exists($topimg)) {
		$topimg = 'images/silhouette-new.png';
	}
	
		$query = "SELECT groupName FROM usergroups WHERE userGroup = $userGroup";
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
			$userGroupName = $row['groupName'];
			
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$userGroupName</span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$userGroupName</span>";
		
	}

?>
<center><a href="profile.php?user_id=<?php echo $user_id; ?>" class='cta1nm'>&laquo; <?php echo $lang['title-profile']; ?> &laquo;</a></center>
<?php
	echo <<<EOD
	
 <div id='mainbox'>
<center><div class='topaval'>
  <center> <span class="profilepicholder" style="float: left; margin-right: 15px;" ><img class="profilepic" src="$topimg" width="143" />$highroller</span>


 <table style="display: inline-block; vertical-align: top; text-align: left;">
  <tr>
   <td class='biggerfont'><span class='firsttext'>#$memberno</span>&nbsp;&nbsp;<span class='secondtext'></span><br />
   <span class='nametext'>$first_name $last_name</span><br /> $groupName<br /></td>
  </tr>
  <tr>
   <td><strong></td>
  </tr>
 </table>
 </center>
</div></center><br />

EOD;
		while ($scaninData = $scanInResult->fetch()) {
	
	$visitNo = $scaninData['visitNo'];
	$userid = $scaninData['userid'];
	$scanin = $scaninData['scanin'];
	$scanout = $scaninData['scanout'];
	$duration = $scaninData['duration'];
	$completed = $scaninData['completed'];
	
	$scantimeReadable = date('H:i', strtotime($scanin."+$offsetSec seconds"));
	
	setlocale(LC_ALL, 'es_ES');

	$dateOnly = ucfirst(strftime("%A %d %B %Y", strtotime($scanin)));

	$userDetails = "SELECT memberno, first_name, last_name from users WHERE user_id = $userid";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$user = $result->fetch();

		$member = "#" . $user['memberno'] . " - " . $user['first_name'] . " " . $user['last_name'];


	if (date('d', strtotime($scanin)) != date('d', strtotime($prevScantime))) {
		$midOutput .= "<tr><td style='text-align: center; border-bottom: 0; color: #a80082; font-weight: 600; font-size: 18px; margin-top: 20px; background-color: #eee;' colspan='5' class='non-hover'>$dateOnly</td></tr><tr><th>{$lang['global-member']}</th><th>{$lang['entry']}</th><th>{$lang['exit']}</th><th>{$lang['duration']}</th><th></th></tr>";
		// Insert row with date.
	}
	
	if ($scanout == '') {

		$expense_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='member-visits.php?userid=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='member-visits.php?userid=%d'>%s</td>
	  	   <td class='clickableRow' href='member-visits.php?userid=%d'></td>
	  	   <td class='clickableRow' href='member-visits.php?userid=%d' style='text-align: right;'></td>
	  	   <td style='text-align: center;'><a href='javascript:delete_visit(%d)'><img src='images/delete.png' height='15' title='Borrar' /></a></td>
		  </tr>",
		  $userid, $member, $userid, $scantimeReadable, $userid, $userid, $visitNo
		  );

	} else {
		
		// Determine visit duration	
		$hours  = floor($duration/60); //round down to nearest minute. 
		$minutes = $duration % 60;
		
		$signoutReadable = date('H:i', strtotime($scanout."+$offsetSec seconds"));
		
		$expense_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='member-visits.php?userid=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='member-visits.php?userid=%d'>%s</td>
	  	   <td class='clickableRow' href='member-visits.php?userid=%d'>%s</td>
	  	   <td class='clickableRow' href='member-visits.php?userid=%d' style='text-align: right;'>%dh %02dm</td>
	  	   <td style='text-align: center;'><a href='javascript:delete_visit(%d)'><img src='images/delete.png' height='15' title='Borrar' /></a></td>
		  </tr>",
		  $userid, $member, $userid, $scantimeReadable, $userid, $signoutReadable, $userid, $hours, $minutes, $visitNo
		  );
		  
	}
	

	  $midOutput .=  $expense_row;

	$prevScantime = $scanin;
	$totalMinutes = $totalMinutes + $duration;

  }
	  
	$totalMinutesPerDay = $totalMinutes / $noOfVisits;
	
	$averageVisitDuration = date('H:i', mktime(0,$totalMinutesPerDay));
	
	$hours  = floor($totalMinutesPerDay/60); //round down to nearest minute. 
	$minutes = $totalMinutesPerDay % 60;
	
	$averageVisit = $hours . "h " . $minutes . "m";
	
	$finalOutput = "

<center><div id='productoverview'>
 <table class='default'>
  <tr>
   <td>{$lang['total-visits']}:</td>
   <td class='yellow fat right'>$noOfVisits</td>
  </tr>
  <tr>
   <td>{$lang['weekly-visits']}:</td>
   <td class='yellow fat right'>$weeklyVisits</td>
  </tr>
  <tr>
   <td>{$lang['daily-visits']}:</td>
   <td class='yellow fat right'>$dailyVisits</td>
  </tr>
  <tr>
   <td>{$lang['average-stay']}: </td>
   <td class='yellow fat right'>$averageVisit</td>
  </tr>
 </table>
</div></center>

<br />
	 <table class='default'>
	  <tbody>";
  
	$finalOutput .= $midOutput;
  	echo $finalOutput;
  
?>

	 </tbody>
	 </table>
	 
<?php displayFooter(); ?>
