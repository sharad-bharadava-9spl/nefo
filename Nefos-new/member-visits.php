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
	
	// Look up visits type 0 (scan in)
	$scanIn = "SELECT visitNo, userid, scanin, scanout, completed, duration FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC";
	
	$scanInResult = mysql_query($scanIn)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$noOfVisits = mysql_num_rows($scanInResult);
	
	$firstScan = "SELECT scanin FROM newvisits WHERE userid = $user_id ORDER BY scanin ASC LIMIT 1";
	
	$firstScanDate = mysql_query($firstScan)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($firstScanDate);
		
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
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$paidUntil = $row['paidUntil'];
		$userGroup = $row['userGroup'];
		$credit = $row['credit'];
		$photoExt = $row['photoExt'];
		
		$member = "#" . $memberno . " - " . $first_name . " " . $last_name;
		
	
	

		
	$deleteVisitScript = <<<EOD
function delete_visit(visitNo) {
	if (confirm("Estas seguro?")) {
				window.location = "uTil/delete-visit.php?visitNo=" + visitNo;
				}
}
EOD;
	
	pageStart("Visitas de socio", NULL, $deleteVisitScript, "pmembership", "admin", "Visitas de socio", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
echo "<center><div id='profilearea'><img src='images/members/$userid.$photoExt' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4></center>";
	
$midOutput = "<table class='default'><tbody>";
	
while ($scaninData = mysql_fetch_array($scanInResult)) {
	
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
	$result = mysql_query($userDetails)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());

	while ($user = mysql_fetch_array($result)) {

		$member = "#" . $user['memberno'] . " - " . $user['first_name'] . " " . $user['last_name'];

	}

	if (date('d', strtotime($scanin)) != date('d', strtotime($prevScantime))) {
		$midOutput .= "<tr><td colspan='5' style='border: 0;' class='non-hover'></td></tr><tr><td style='text-align: center; border-bottom: 0; color: #a80082; font-weight: 600; font-size: 18px; margin-top: 20px; background-color: #eee;' colspan='5' class='non-hover'>$dateOnly</td></tr>	   <tr><th>Socio</th><th>Entrada</th><th>Salida</th><th>Duración</th><th></th></tr>";
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

<br /><br /><center><div id='productoverview' style='background-color: #5aa242'>
 <table>
  <tr>
   <td>Visitas en total:</td>
   <td class='yellow fat right'>$noOfVisits</td>
  </tr>
  <tr>
   <td>Visitas semanales:</td>
   <td class='yellow fat right'>$weeklyVisits</td>
  </tr>
  <tr>
   <td>Visitas diarias:</td>
   <td class='yellow fat right'>$dailyVisits</td>
  </tr>
  <tr>
   <td>Duración de estancia media: </td>
   <td class='yellow fat right'>$averageVisit</td>
  </tr>
 </table>
</div></center>


	 <table class='default'>
	  <tbody>";
  
	$finalOutput .= $midOutput;
  	echo $finalOutput;
  
?>

	 </tbody>
	 </table>
	 
	 
<?php displayFooter(); ?>
