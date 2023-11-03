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
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup  ORDER by u.memberno ASC";
		try
		{
			$results = $pdo3->prepare("$selectUsers");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	
		
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
					4: {
						sorter: "currency"
					},
					6: {
						sorter: "dates"
					},
					11: {
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
					4: {
						sorter: "currency"
					},
					6: {
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
					4: {
						sorter: "dates"
					},
					9: {
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


	pageStart($lang['users'], NULL, $memberScript, "pmembership", NULL, $lang['users'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['member-group']; ?></th>
	    <th><?php echo $lang['global-comment']; ?></th>
	    <th><?php echo $lang['global-edit']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
		while ($user = $results->fetch()) {

// Calculate Age:
	$day = $user['day'];
	$month = $user['month'];
	$year = $user['year'];
	$paidUntil = $user['paidUntil'];
	$starCat = $user['starCat'];
	$oldNumber = $user['oldNumber'];
	
	
	
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

	if ($user['usageType'] == '1') {
		$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
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
	
	if ($starCat == 1) {
   		$userStar = "<img src='images/star-yellow.png' width='16' /><span style='display:none'>1</span>";
	} else if ($starCat == 2) {
   		$userStar = "<img src='images/star-black.png' width='16' /><span style='display:none'>2</span>";
	} else if ($starCat == 3) {
   		$userStar = "<img src='images/star-green.png' width='16' /><span style='display:none'>3</span>";
	} else if ($starCat == 4) {
   		$userStar = "<img src='images/star-red.png' width='16' /><span style='display:none'>4</span>";
	} else {
   		$userStar = "<span style='display:none'>0</span>";
	}

	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

	echo sprintf("
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $user['groupName']);

  	   
  	
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
