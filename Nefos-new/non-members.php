<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();	
	
	// Query to look up users
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1 FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND (memberno = '0' OR u.userGroup > 5) ORDER by u.memberno ASC";
		
	$result = mysql_query($selectUsers)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		
	
		
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Socios",
			    filename: "Socios" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
EOD;

	if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "currency"
					},
					5: {
						sorter: "dates"
					},
					10: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 0) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "currency"
					},
					5: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 0 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					8: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	}
	
	$memberScript .= <<<EOD
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Non-" . $lang['index-members'], NULL, $memberScript, "pmembership", NULL, "NON-" . $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><?php echo $lang['global-credit']; ?></th>
	    <th style='text-align: center;'>*</th>
<?php } ?>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['member-gender']; ?></th>
	    <th><?php echo $lang['age']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['member-group']; ?></th>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
	    <th><?php echo $lang['expiry']; ?></th>
<?php } ?>
	    <th class='centered'><?php echo $lang['signature']; ?></th>
	    <th><?php echo $lang['dni-scan']; ?></th>
	    <th><?php echo $lang['global-comment']; ?></th>
	    <th><?php echo $lang['global-edit']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($user = mysql_fetch_array($result)) {
// Calculate Age:
	$day = $user['day'];
	$month = $user['month'];
	$year = $user['year'];
	$paidUntil = $user['paidUntil'];
$bdayraw = $day . "." . $month . "." . $year;
$bday = new DateTime($bdayraw);
$today = new DateTime(); // for testing purposes
$diff = $today->diff($bday);
$age = $diff->y;

	// Find out if DNI has been scanned:
	$file = 'images/ID/' . $user['user_id'] . '-front.' . $user['dniext1'];
	
	if (!file_exists($file)) {
		$dnicolour = 'negative';
		$dniScan = $lang['global-no'];
	} else {
		$dnicolour = '';
		$dniScan = $lang['global-yes'];
	}

	// Find out if member has signed:
	$file2 = 'images/sigs/' . $user['user_id'] . '.png';
	
	if (!file_exists($file2)) {
		$form1colour = 'negative';
		$form1 = $lang['global-no'];
	} else {
		$form1colour = '';
		$form1 = $lang['global-yes'];
	}

	if ($user['usageType'] == 'Medicinal') {
		$usageType = "<img src='images/medical.png' width='16' />";
	} else {
		$usageType = '';
	}
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

	if ($user['userGroup'] > 4) {
		if ($memberExp == $timeNow) {
			$membertill = "<span class='mid'>$memberExpReadable</span>";
	  	} else if ($memberExp < $timeNow) {
		  	$membertill = "<span class='negative'>$memberExpReadable</span>";
		} else if ($memberExp > $timeNow) {
		  	$membertill = "<span class='positive'>$memberExpReadable</span>";
		}
	} else {
		$membertill = "<span class='white'>00-00-0000</span>";
	}
	
	if ($user['creditEligible'] == 1) {
		$creditEligible = $lang['global-yes'];
	} else {
		$creditEligible = '';
	}

	// Does user have comments?
	$getNotes = "SELECT noteid, notetime, userid, note FROM usernotes WHERE userid = {$user['user_id']} ORDER by notetime DESC";
	
	$noteCheck = mysql_query($getNotes);
	if(mysql_num_rows($noteCheck) == 0) {
   		$comment = '';
	} else {
   		$comment = "<img src='images/note.png' width='16' /><span style='display:none'>1</span>";
	}
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.1f &euro;</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], $user['credit'], $user['user_id'], $creditEligible);
  	  
}

	echo sprintf("
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%d</td>
  	   <td class='clickableRow' style='text-align: center;' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $user['gender'], $user['user_id'], $age, $user['user_id'], $usageType, $user['user_id'], $user['groupName']);

if ($_SESSION['membershipFees'] == 1) {
	  
	echo sprintf("<td class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>",
   $paidClass, $user['user_id'], $membertill);
	    
}

  	   
	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan);

  	
	echo sprintf("
  	   <td style='text-align: center;' class='clickableRow' href='profile.php?user_id=%d&openComment'>%s</td>
  	   <td style='text-align: center;'><a href='edit-profile.php?user_id=%d'><img src='images/edit.png' height='15' title='Edit user' /></a></td>
	  </tr>",
	  $user['user_id'], $comment, $user['user_id']
	  );
	  
  }
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
