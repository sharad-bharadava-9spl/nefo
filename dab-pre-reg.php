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
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.exento, u.coupon FROM users u WHERE u.userGroup = 10 OR u.userGroup = 11 OR u.userGroup = 13 OR u.userGroup = 14 ORDER by u.registeredSince DESC";
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


	pageStart("Pre-registrados", NULL, $memberScript, "pmembership", NULL, "Pre-registrados", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Status</th>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['member-gender']; ?></th>
	    <th><?php echo $lang['age']; ?></th>
	    <th>Coupon?</th>
	    <th></th>
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
	$exento = $user['exento'];
	$userGroup = $user['userGroup'];
	$coupon = $user['coupon'];
	
	if ($userGroup == 10) {
		$src = 'Online';
	} else {
		$src = 'Tablet';
	}
	
	$bdayraw = $day . "." . $month . "." . $year;
	$bday = new DateTime($bdayraw);
	$today = new DateTime(); // for testing purposes
	$diff = $today->diff($bday);
	$age = $diff->y;


	if ($user['usageType'] == '1') {
		$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
	} else {
		$usageType = '';
	}
	
	if ($user['userGroup'] == '13') {
		
		$group = 'Re-submitted';
		
	} else if ($user['userGroup'] == '14') {
		
		$group = 'Awaiting more info';
		
	} else if ($user['userGroup'] == '3') {
		
		$group = 'Resubmitted';
		
	} else {
		
		$group = 'Pre-registered';
		
	}
	
	if ($coupon != '') {
		$coupon = $coupon;
	} else {
		$coupon = '';
	}
	
		
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='approve-new.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='approve-new.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='approve-new.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='approve-new.php?user_id=%d'>%s</td>",
	  $user['user_id'], $group, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

	echo sprintf("
  	   <td class='clickableRow' href='approve-new.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='approve-new.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='approve-new.php?user_id=%d'>%d</td>
  	   <td class='clickableRow' href='approve-new.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: center;' href='approve-new.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y H:i",strtotime($user['registeredSince']) - 14400), $user['user_id'], $user['gender'], $user['user_id'], $age, $user['user_id'], $coupon, $user['user_id'], $usageType);
  	  

	  
  }
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
