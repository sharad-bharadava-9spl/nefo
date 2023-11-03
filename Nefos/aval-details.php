<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	// Get the card / user ID
	if ($_POST['cardid'] != '') {
		$cardid = $_POST['cardid'];
		$userDetails = "SELECT user_id FROM users WHERE cardid = '{$cardid}'";
		$userCheck = mysql_query($userDetails);
		if(mysql_num_rows($userCheck) == 0) {
	   		handleError($lang['error-keyfob'],"");
		}
	
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
		$user_id = $row['user_id'];
							
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
	
	$origAval = $user_id;
	$_SESSION['aval'] = $user_id;
			
	// Query to look up user
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.friend2, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(), u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.dniext1, u.dniext2, u.workStation FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-useridnotexist'],"");
	}
	
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
	$memberno = $row['memberno'];
	$registeredSince = $row['registeredSince'];
	$membertime = date("M y", strtotime($registeredSince));
	$membertimeFull = date("d-m-y", strtotime($registeredSince));
	$userGroup = $row['userGroup'];
	$groupName = $row['groupName'];
	$groupDesc = $row['groupDesc'];
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$email = $row['email'];
	$day = $row['day'];
	$month = $row['month'];
	$year = $row['year'];
	$nationality = $row['nationality'];
	$gender = $row['gender'];
	$dni = $row['dni'];
	$street = $row['street'];
	$streetnumber = $row['streetnumber'];
	$flat = $row['flat'];
	$postcode = $row['postcode'];
	$city = $row['city'];
	$country = $row['country'];
	$telephone = $row['telephone'];
	$mconsumption = $row['mconsumption'];
	$usageType = $row['usageType'];
	$signupsource = $row['signupsource'];
	$cardid = $row['cardid'];
	$photoid = $row['photoid'];
	$docid = $row['docid'];
	$doorAccess = $row['doorAccess'];
	$friend = $row['friend'];
	$friend2 = $row['friend2'];
	$paidUntil = $row['paidUntil'];
	$adminComment = $row['adminComment'];
	$daysMember = $row['daysMember'];
	$form1 = $row['form1'];
	$form2 = $row['form2'];
	$dniscan = $row['dniscan'];
	$paymentWarning = $row['paymentWarning'];
	$paymentWarningDate = $row['paymentWarningDate'];
	$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
	$userCredit = $row['credit'];
	$banComment = $row['banComment'];
	$creditEligible = $row['creditEligible'];
	$discount = $row['discount'];
	$discountBar = $row['discountBar'];
	$photoext = $row['photoext'];
	$dniext1 = $row['dniext1'];
	$dniext2 = $row['dniext2'];
	$workStation = $row['workStation'];
	
	// Look up notes
	$getNotes = "SELECT noteid, notetime, userid, note FROM usernotes WHERE userid = $user_id ORDER by notetime DESC";
	
	$noteCheck = mysql_query($getNotes);
	if(mysql_num_rows($noteCheck) == 0) {
   		$userNotes = '';
	} else {
		$userNotes = mysql_query($getNotes)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	}
	

// Calculate Age - only if Birthday exists

if ($day != 0) {
	$bdayraw = $day . "." . $month . "." . $year;
	$bday = new DateTime($bdayraw);
	$today = new DateTime(); // for testing purposes
	$diff = $today->diff($bday);
	$age = $diff->y;
	
	$birthday = date("d M Y", strtotime($bdayraw));
} else {
	$birthday = '';
}	

			
	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$totalAmount = $row['SUM(quantity)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
			
} else {
		handle_error($lang['error-findinginfo'],"Error locating user with ID {$user_id}");
}

	$deleteNoteScript = <<<EOD
function delete_note(noteid, userid) {
	if (confirm("{$lang['confirm-deletenote']}")) {
				window.location = "uTil/delete-note.php?noteid=" + noteid + "&userid=" + userid;
				}
}
EOD;
	pageStart($lang['aval-tree'], NULL, $deleteNoteScript, "avalpage", "", $lang['aval-tree'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


$totalAmount = number_format($totalAmount,0);
$totalAmountPerWeek = number_format($totalAmountPerWeek,0);

	$mainAval = <<<EOD
<div class="overview">
 <span class="profilepicholder"><img class="profilepic" src="images/members/$user_id.$photoext" /></span>
 <span class="profilefirst">#$memberno - $first_name $last_name</span>
 <span class="profilesecond"><br />
 <strong>{$lang['age']}: </strong>$age {$lang['member-yearsold']}<br />
 <strong>{$lang['member-regsince']}: </strong>$membertimeFull ($daysMember {$lang['days']})<br />
 <strong>{$lang['closeday-dispensed']}: </strong> $totalAmount g ($totalAmountPerWeek g/ {$lang['week']})
 </span>
 <br />
<span class="profilethird">
EOD;

	// If member is banned
	if ($userGroup == 7) {
		
		// Banned 
	$mainAval .= "<span class='banDisplay'><span class='banHeader'>*** {$lang['bannedC']} !! ***</span><br /><strong>{$lang['reason']}:</strong><br />" . $banComment . "</span>";
		
	} else {
	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1) {  // show Member w/ expiry
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if ($memberExp == $timeNow) {
			$mainAval .= "<a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 5px;' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a>";
	  	} else if ($memberExp > $timeNow) {
		  	$mainAval .= "<a href='pay-membership.php?user_id=$user_id' class='white'>" . $lang['member-memberuntil'] . ": $memberExpReadable</a>";
		} else {
		  	$mainAval .= "<a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable</span></a>";
		  	
		  	if ($paymentWarning == '1') {
		  	$mainAval .= "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px;' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: -15px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . ": $paymentWarningDateReadable</span></a>";
		  	}
		  	
		}
		
	} else {
		
		$mainAval .= $groupName . "&nbsp;";
		
		if ($_SESSION['puestosOrNot'] == 1) {
		
			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {
				$mainAval .= "<img src='images/profile-reception.png' />&nbsp;";
			}
			if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16) {
				$mainAval .= "<img src='images/profile-bar.png' />&nbsp;";
			}
			if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16) {
				$mainAval .= "<img src='images/profile-dispensary.png' />&nbsp;";
			}
		}		
	}
	

	if ($usageType == 'Medicinal') {
		$mainAval .= "<br /><img src='images/medical-22.png' lass='warningIcon' style='margin-bottom: -3px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['medicinal-user']}</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		$mainAval .= "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['global-birthday']}</span>";
	}
	
	$file = 'images/ID/' . $user_id . '-front.' . $dniext1;
	$file3 = 'images/sigs/' . $user_id . '.png';

	if (!file_exists($file)) {
    	$mainAval .= "<br /><a href='new-id-scan.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>" . $lang['member-dninotscanned'] . "</span></a>";
	}
	
	if (!file_exists($file3)) {
    	$mainAval .= "<br /><a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>" . $lang['signature-missing'] . "</span></a>";
	}
	
	// Retrieve system settings, to determine high roller and consumption %
	$selectSettings = "SELECT highRollerWeekly, consumptionPercentage FROM systemsettings";

	$settingsResult = mysql_query($selectSettings)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($settingsResult);
		$highRollerWeekly = $row['highRollerWeekly'];
		$consumptionPercentage = $row['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly) {
		$mainAval .= "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>High roller</span>";
	}
	
	if ($userNotes != '') {
		$noteCount = mysql_num_rows($noteCheck); //2
		$i = 1;
		
			$mainAval .= <<<EOD
<br />
<span id='adminComment' onClick="javascript:toggleDiv('userNotes');">
 <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' />
 <span class='yellow' id='showComment' style='cursor: pointer;'>{$lang['global-admincomment']}</span>
</span>
EOD;

	if ($_GET['deleted'] == 'yes' || isset($_GET['openComment'])) {
		$mainAval .= "<div class='userNotes'>";
	} else {
		$mainAval .= "<div class='userNotes' style='display: none;'>";
	}
	
		$mainAval .= <<<EOD
	 <table class="profileNew">
  	  <tr>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['pur-date']}</strong></th>
  	   <th class="smallerfont" colspan='2'><strong>{$lang['global-comment']}</strong></th>
	  </tr>
EOD;
		while ($userNote = mysql_fetch_array($userNotes)) {
	
			if ($userNote['notetime'] == NULL) {
				$formattedDate = '';
			} else {
				$formattedDate = date("d-m-y H:i", strtotime($userNote['notetime'] . "+$offsetSec seconds"));
			}
			$noteid = $userNote['noteid'];
			$note = $userNote['note'];
			
		if($i == $noteCount) {
	   		$mainAval .= <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$formattedDate</td>
  <td style='border-bottom: 0;'>$note</td>
 </tr>
EOD;
		} else {
			$mainAval .= <<<EOD
 <tr>
  <td>$formattedDate</td>
  <td>$note</td>
 </tr>
EOD;
	}
			$mainAval .= <<<EOD
EOD;
	$i++;
		}
$mainAval .= "</table></div>";
	}

	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	
	if ($quantityMonth >= $mconsumption) {
		$mainAval .= "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitexc'] . " (+$consumptionDelta g)</span>";
	} else if ($consumptionDeltaPlus < ($mconsumption * $consumptionPercentage)) {
		$mainAval .= "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitnear'] . " ($consumptionDeltaPlus g " . $lang['global-remaining'] . ")</span>";
	}
	
	
}

	$mainAval .= <<<EOD


</span>
 </div> <!-- END OVERVIEW -->
 
 
EOD;

	// echo $mainAval;
	// Start a loop, show 3 past avals max (so 4 total avals)
	// Look up aval
	// If numeric, loop up aval's details - and his aval
	
	
	if (is_numeric($friend)) {

		$friendDetails = "SELECT memberno, first_name, last_name, friend, dniext1 FROM users WHERE user_id = $friend";
			
		$result = mysql_query($friendDetails)
			or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];
			$dniext1 = $row['dniext1'];
			$user_id = $friend;
			$friend = $row['friend'];
			
		$query = "SELECT COUNT(user_id) FROM users WHERE friend = $user_id";
		
		$result = mysql_query($query)
			or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$noOfAvalees = $row['COUNT(user_id)'];


	$secondAval = <<<EOD
<div class='productoverview'>
  <center><img src="images/members/$user_id.$photoext" height='150' style='display: inline; vertical-align: middle;' />

 <table style="display: inline-block; vertical-align: top;">
  <tr>
   <td class='biggerfont'><strong>#$memberno - $first_name $last_name</strong></td>
  </tr>
  <tr>
   <td><strong>{$lang['avalado']}: </strong>$noOfAvalees {$lang['membersLC']}</td>
  </tr>
 </table>
 </center>
</div>
EOD;

	}

	if (is_numeric($friend)) {

		$friendDetails = "SELECT memberno, first_name, last_name, friend, dniext1 FROM users WHERE user_id = $friend";
			
		$result = mysql_query($friendDetails)
			or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];
			$dniext1 = $row['dniext1'];
			$user_id = $friend;
			$friend = $row['friend'];
			
		$query = "SELECT COUNT(user_id) FROM users WHERE friend = $user_id";
		
		$result = mysql_query($query)
			or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$noOfAvalees = $row['COUNT(user_id)'];


	$thirdAval = <<<EOD
<div class='productoverview'>
  <center><img src="images/members/$user_id.$photoext" height='150' style='display: inline; vertical-align: middle;' />

 <table style="display: inline-block; vertical-align: top;">
  <tr>
   <td class='biggerfont'><strong>#$memberno - $first_name $last_name</strong></td>
  </tr>
  <tr>
   <td><strong>{$lang['avalado']}: </strong>$noOfAvalees {$lang['membersLC']}</td>
  </tr>
 </table>
 </center>
</div>
EOD;

echo $thirdAval;

echo "<div style='margin-left: auto; margin-right: auto; text-align: center;'><img src='images/arrow-purple.png' /></center></div>";
	}
	
echo $secondAval;

echo "<div style='margin-left: auto; margin-right: auto; text-align: center;'><img src='images/arrow-purple.png' /></center></div>";


echo $mainAval;




		// List of avalees
		$friendDetails = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.day, u.month, u.year, g.groupName FROM users u, usergroups g WHERE u.userGroup = g.userGroup AND friend = $origAval";
			
		$resultF = mysql_query($friendDetails)
			or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
			
			
				echo <<<EOD
				<center><table class='default' style='width: 70%;'>
				 <thead>
				  <tr>
				   <th>{$lang['global-member']}</th>
				   <th>{$lang['age']}</th>
				   <th>{$lang['avalado']}</th>
				   <th>{$lang['global-type']}</th>
				   <th>{$lang['global-comment']}</th>
				   <th>{$lang['consumed']}</th>
				   <th>{$lang['global-registered']}</th>
				  </tr>
				 </thead>
				 <tbody>
EOD;

				
		$i = 0;
		
		while ($avalee = mysql_fetch_array($resultF)) {

			$user_id = $avalee['user_id'];
			$memberno = $avalee['memberno'];
			$first_name = $avalee['first_name'];
			$last_name = $avalee['last_name'];
			$registeredSince = $avalee['registeredSince'];
			$registeredSinceFormatted = date("d-m-Y", strtotime($avalee['registeredSince']));
			$day = $avalee['day'];
			$month = $avalee['month'];
			$year = $avalee['year'];
			$groupName = $avalee['groupName'];
			
			// Query to look up total sales and find weekly average
			$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id";
		
			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$totalAmount = $row['SUM(quantity)'];
				$totalAmountPerDay = $totalAmount / $daysMember;
				$totalAmountPerWeek = $totalAmountPerDay * 7;
				$totalAmount = number_format($totalAmount,0);
				$totalAmountPerWeek = number_format($totalAmountPerWeek,1);
		
			// Calculate age
			if ($day != 0) {
				$bdayraw = $day . "." . $month . "." . $year;
				$bday = new DateTime($bdayraw);
				$today = new DateTime(); // for testing purposes
				$diff = $today->diff($bday);
				$age = $diff->y;
			} else {
				$age = '';
			}	

			// Look up number of Avalees
			$query = "SELECT COUNT(user_id) FROM users WHERE friend = $user_id";
			
			$result = mysql_query($query)
				or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$noOfAvalees = $row['COUNT(user_id)'];
				
			// Look up notes
			$getNotes = "SELECT noteid, notetime, userid, note FROM usernotes WHERE userid = $user_id ORDER by notetime DESC";
			
			$noteCheck = mysql_query($getNotes);
			
			if (mysql_num_rows($noteCheck) > 0) {
				
				
				$commentRead = <<<EOD
	<img src='images/comments.png' id='comment$i' /><div id='helpBox$i' class='helpBox'>
			                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						</script>

	 <table class="profileNew">
  	  <tr>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['pur-date']}</strong></th>
  	   <th class="smallerfont" colspan='2'><strong>{$lang['global-comment']}</strong></th>
	  </tr>
EOD;
				
				$noteCount = mysql_num_rows($noteCheck); //2
				$y = 1;
				
				while ($userNote = mysql_fetch_array($noteCheck)) {
	
					if ($userNote['notetime'] == NULL) {
						$formattedDate = '';
					} else {
						$formattedDate = date("d-m-y H:i", strtotime($userNote['notetime'] . "+$offsetSec seconds"));
					}
					
					$noteid = $userNote['noteid'];
					$note = $userNote['note'];
			
					if($y == $noteCount) {
						
	   					$commentRead .= <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$formattedDate</td>
  <td style='border-bottom: 0;'>$note</td>
 </tr>
EOD;

					} else {
			
						$commentRead .= <<<EOD
 <tr>
  <td>$formattedDate</td>
  <td>$note</td>
 </tr>
EOD;
					}
					$y++;
				}
				
			$commentRead .=  "</table>";
		
		
				
			} else {
				$commentRead = "";
			}
				
			echo <<<EOD
			
			<tr>
			 <td class='clickableRow' href='profile.php?user_id=$user_id'># $memberno $first_name $last_name</td>
			 <td>$age</td>
			 <td class='centered'>$noOfAvalees</td>
			 <td>$groupName</td>
			 <td class='relative centered'>$commentRead</td>
			 <td>$totalAmount g ($totalAmountPerWeek g/ {$lang['week']})</td>
			 <td>$registeredSinceFormatted</td>
			</tr>
			
EOD;
							

			$i++;
		}
			

			echo "</tbody></table></center>";


?>
  <div class="clearfloat"></div>
  <center><a href='new-member.php?aval=<?php echo $origAval; ?>' class='cta'>Continuar</a></center><br />

  
  
<script>

	
function toggleDiv(divId) {
   $("."+divId).toggle();
}	
</script>

<?php displayFooter(); ?>
