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
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.exento, u.visittime FROM users u WHERE u.userGroup = 10 OR u.userGroup = 11 OR u.userGroup = 12 ORDER by u.registeredSince DESC";
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
			 //    var dateArray = s.split('-');
			 //    var timeArray = dateArray[2].split(':');
			 //    var lastField = (timeArray[0].replace(/\s/g, '')+timeArray[1].replace(/\s/g, ''));
			 //    //alert(dateArray[0] + dateArray[1] + lastField);
			 //    var newDate = myDate[1]+","+myDate[0]+","+lastField;
				// console.log(new Date(newDate).getTime());​
			 //    return ($.tablesorter.formatFloat(dateArray[0] + dateArray[1] + lastField));

			  	 var date,
				dateTimeParts = s.split(' '),
			    timeParts = dateTimeParts[1].split(':'),
			    dateParts = dateTimeParts[0].split('-');
				date = new Date(dateParts[2], parseInt(dateParts[1], 10) - 1, dateParts[0], timeParts[0], timeParts[1]);
				//return dateParts[2], parseInt(dateParts[1], 10) - 1, dateParts[0], timeParts[0], timeParts[1];
				return date.getTime();

			  },
			  type: 'numeric'

			});
			
						
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					8: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	
	$memberScript .= <<<EOD
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart($lang['pre-registered'], NULL, $memberScript, "pmembership", NULL, $lang['pre-registered'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th></th>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['member-gender']; ?></th>
	    <th><?php echo $lang['age']; ?></th>
	    <th></th>
	    <th><?php echo $lang['visit-time']; ?></th>
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
	
	$visittime = date("d-m-Y H:i:s", strtotime($user['visittime']."+$offsetSec seconds"));
	
	if ($userGroup == 10 || $userGroup == 12) {
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
	
	if ($_SESSION['domain'] == 'royaldream') {
		
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $src, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

	echo sprintf("
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%d</td>
  	   <td class='clickableRow' style='text-align: center;' href='approve.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $user['gender'], $user['user_id'], $age, $user['user_id'], $usageType);
  	  
	  } else {
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $src, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

	echo sprintf("
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%d</td>
  	   <td class='clickableRow' style='text-align: center;' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $user['gender'], $user['user_id'], $age, $user['user_id'], $usageType, $user['user_id'], $visittime);
  	  
	  }

	  
  }
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
