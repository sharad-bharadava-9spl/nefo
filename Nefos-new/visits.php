<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Look up visits type 0 (scan in)
	$scanIn = "SELECT visitNo, userid, scanin, scanout, completed, duration FROM newvisits ORDER BY scanin DESC";
	
	$scanInResult = mysql_query($scanIn)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
	
	$deleteVisitScript = <<<EOD
function delete_visit(visitNo) {
	if (confirm("Estas seguro?")) {
				window.location = "uTil/delete-visit.php?visitNo=" + visitNo + "&source=visits";
				}
}
EOD;
	
	pageStart("Visitas", NULL, $deleteVisitScript, "pexpenses", "admin", "Visitas", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

	 <table class="default">
	  <tbody>
	  
	  <?php

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
		echo "<tr><td colspan='5' style='border: 0;' class='non-hover'></td></tr><tr><td style='text-align: center; border-bottom: 0; color: #a80082; font-weight: 600; font-size: 18px; margin-top: 20px; background-color: #eee;' colspan='5' class='non-hover'>$dateOnly</td></tr>	   <tr><th>Socio</th><th>Entrada</th><th>Salida</th><th>Duración</th><th></th></tr>";
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

	echo $expense_row;

	$prevScantime = $scanin;

  }
?>

	 </tbody>
	 </table>
	 
	 
<?php displayFooter(); 

	/*
	// Check if user also signed out
	$scanOut = "SELECT scantime FROM visits WHERE userid = $userid AND visitNo = $visitNo AND type = 1";
		
	$scanOutResult = mysql_query($scanOut)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
	
	if (mysql_num_rows($scanOutResult) == 0) {
		
		// User NOT signed out
		$signout = '';
		$duration = '';
		$signoutReadable = '';
		
	} else {
		
		// User signed out
		$row = mysql_fetch_array($scanOutResult);
			$signout = $row['scantime'];
			$signoutReadable = date('H:i', strtotime($signout."+$offsetSec seconds"));
			
		
		// Determine duration	
		$minutesOfVisit = round(abs(strtotime($scantime) - strtotime($signout)) / 60,2);
		$hours  = floor($minutesOfVisit/60); //round down to nearest minute. 
		$minutes = $minutesOfVisit % 60;
		$duration = $hours . "h " . $minutes . "m";

	}*/
	?>
