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
	// $selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' AND u.userGroup < 6 ORDER by u.memberno ASC LIMIT 1000";
	
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, donations d WHERE u.user_id = d.userid AND u.memberno <> '0' AND u.userGroup < 6 AND DATE(d.donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) ORDER by u.memberno ASC";
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
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					},
					7: {
						sorter: "currency"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart($lang['donations-vs-dispenses'], NULL, $memberScript, "pmembership", NULL, $lang['donations-vs-dispenses'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>C</th>
	    <th class='centered'>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-donations']; ?></th>
	    <th><?php echo $lang['bar']; ?></th>
	    <th><?php echo $lang['dispensary']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {
	
	
	$starCat = $user['starCat'];
	$oldNumber = $user['oldNumber'];
	$user_id = $user['user_id'];
	
	$donatedQuery = "SELECT SUM(amount) FROM donations WHERE DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) AND userid = $user_id";
		try
		{
			$result = $pdo3->prepare("$donatedQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$donated = $row['SUM(amount)'];

	$dispensedQuery = "SELECT SUM(amount) FROM sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) AND userid = $user_id";
		try
		{
			$result = $pdo3->prepare("$dispensedQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$dispensed = $row['SUM(amount)'];

	$barQuery = "SELECT SUM(amount) FROM b_sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) AND userid = $user_id";
		try
		{
			$result = $pdo3->prepare("$barQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$bar = $row['SUM(amount)'];

	
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
	
	if ($donated > 0) {

		echo sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
		  $user['user_id'], $userStar, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	
		echo sprintf("
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.02f€</td>
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.02f€</td>
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.02f€</td>
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.02f€</td>",
	  	  $user['user_id'], $donated, $user['user_id'], $bar, $user['user_id'], $dispensed, $user['user_id'], $donated - $bar - $dispensed);
	  	  
  	  }

  }
?>
	 </tbody>
	 </table>

<?php  displayFooter(); ?>
