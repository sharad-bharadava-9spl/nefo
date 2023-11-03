<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
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
	
	pageStart($lang['member-visits'], NULL, $deleteVisitScript, "avalpage", "dev-align-center", $lang['member-visits'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
  </form>
  <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		<button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="Desde fecha" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="Hasta fecha" onchange='this.form.submit()' />
		 <button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;

	}
?>
        </form>
 </div>
</div>
</center>
<br />

<?php	
	$topimg = $google_root."images/_$domain/members/$user_id.$photoext";

	$object_exist = object_exist($google_bucket, $google_root_folder."images/_$domain/members/$user_id.$photoext");

	if ($object_exist === false) {
		$topimg = $google_root.'images/silhouette-new-big.png';
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
	
 <div class='relativeitem' style='display: inline-block;'>
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
		$chk =1;
		$i=0;
		$activeclass='';

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
		 $chk =1;
		//$midOutput .= "<tr><td style='text-align: center; border-bottom: 0; color: #a80082; font-weight: 600; font-size: 18px; margin-top: 20px; background-color: #eee;' colspan='5' class='non-hover'>$dateOnly</td></tr><tr><th>{$lang['global-member']}</th><th>{$lang['entry']}</th><th>{$lang['exit']}</th><th>{$lang['duration']}</th><th></th></tr>";


		if($i > 0){
	            
	            $midOutput .=  "<tr><td colspan='7' style='border: none;'> </td></tr><thead><tr><td class='main-td'  colspan='4' align='left'>$dateOnly</td><td class='main-td'></td><td class='main-td'></td><td class='main-td togglebtn'><span class='toggle_icon'><span class='ui-icon ui-icon-pluswhite ui-icon-plusthick'></span></span></td></tr></thead> <tr><td colspan='7' style='border: none;'> </td></tr>  <tr><th class='hidden_td'></th><th>{$lang['global-member']}</th><th>{$lang['entry']}</th><th>{$lang['exit']}</th><th>{$lang['duration']}</th><th>Borrar</th><th class='hidden_td'></th></tr>";
	        }else{
	            $midOutput .=  "<thead><tr><td class='main-td'  colspan='4' align='left'>$dateOnly</td><td class='main-td'></td><td class='main-td'></td><td class='main-td togglebtn'><span class='toggle_icon'><span class='ui-icon ui-icon-pluswhite ui-icon-plusthick'></span></span></td></tr></thead> <tr><td colspan='7' style='border: none;'> </td></tr>  <tr><th class='hidden_td'></th><th>Socio</th><th>Entrada</th><th>Salida</th><th>Duraci&oacute;n</th><th>Borrar</th><th class='hidden_td'></th></tr>";
	        }  
		// Insert row with date.
	}
			if($chk%2 == 0){
				 	$bgcolor = "";
				 }else{
				 	$bgcolor = "bgcolor";
				 } 
	if ($scanout == '') {

		$expense_row =	sprintf("
	  	  <tr>
	  	   <td class='hidden_td'></td>
	  	   <td class='clickableRow  $bgcolor' href='member-visits.php?userid=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow  $bgcolor' href='member-visits.php?userid=%d'>%s</td>
	  	   <td class='clickableRow  $bgcolor' href='member-visits.php?userid=%d'></td>
	  	   <td class='clickableRow  $bgcolor' href='member-visits.php?userid=%d' style='text-align: right;'></td>
	  	   <td class= '$bgcolor' style='text-align: center;'><a href='javascript:delete_visit(%d)'><img src='images/delete.png' height='15' title='Borrar' /></a></td><td class='hidden_td'></td>
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
	  	  <td class='hidden_td'></td>
	  	   <td class='clickableRow  $bgcolor' href='member-visits.php?userid=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow  $bgcolor' href='member-visits.php?userid=%d'>%s</td>
	  	   <td class='clickableRow  $bgcolor' href='member-visits.php?userid=%d'>%s</td>
	  	   <td class='clickableRow  $bgcolor' href='member-visits.php?userid=%d' style='text-align: right;'>%dh %02dm</td>
	  	   <td class='$bgcolor' style='text-align: center;'><a href='javascript:delete_visit(%d)'><img src='images/delete.png' height='15' title='Borrar' /></a></td><td class='hidden_td'></td>
		  </tr>",
		  $userid, $member, $userid, $scantimeReadable, $userid, $signoutReadable, $userid, $hours, $minutes, $visitNo
		  );
		  
	}
	

	  $midOutput .=  $expense_row;

	$prevScantime = $scanin;
	$totalMinutes = $totalMinutes + $duration;
					$i++;
				$chk++;
  }
	  
	$totalMinutesPerDay = $totalMinutes / $noOfVisits;
	
	$averageVisitDuration = date('H:i', mktime(0,$totalMinutesPerDay));
	
	$hours  = floor($totalMinutesPerDay/60); //round down to nearest minute. 
	$minutes = $totalMinutesPerDay % 60;
	
	$averageVisit = $hours . "h " . $minutes . "m";
	
	$finalOutput = "
<div class='actionbox-np2'>
  <div class='mainboxheader'>{$lang['close-day-details']}</div>
  <div class='boxcontent'>
	 <table class='purchasetable'>
		 <tr>
			   <td class='biggerFont'><strong>{$lang['total-visits']}</strong>&nbsp;
			  		$noOfVisits
			   </td>
			     <td class='biggerFont'><strong>{$lang['weekly-visits']}</strong>&nbsp;
			   			{$weeklyVisits}
			   	</td>
			</tr>
			<tr>   	
			   	 <td class='biggerFont'><strong>{$lang['daily-visits']}</strong>&nbsp;
			   		{$dailyVisits}
			   	</td>			   	 
			   	<td class='biggerFont'><strong>{$lang['average-stay']}</strong>&nbsp;
			   		{$averageVisit}
			   	</td>
		  </tr>
	 </table>
	 </div>
	</div>
<br /><br /><br />

<div class='accord_box' style='width:97%;'>
	 <table class='default2 custom_tbl' >
	  <tbody>";
  
	$finalOutput .= $midOutput;
  	echo $finalOutput;
  
?>

	 </tbody>
	 </table>
</div>
<?php displayFooter(); ?>
<script type="text/javascript">

$(document).ready(function(){
  $('.default2 thead').click(function() {
  	  $(this).toggleClass('active');
  	  if($(this).hasClass('active')){
  	  	$(this).find("span.toggle_icon").html("<span class='ui-icon ui-icon-minusthick'>-</span>");
  	  }else{
  	  	$(this).find("span.toggle_icon").html("<span class='ui-icon ui-icon-pluswhite ui-icon-plusthick'></span>");
  	  }	
      $(this).next().slideToggle();
      return false;
  }).next().hide();
});
</script>
