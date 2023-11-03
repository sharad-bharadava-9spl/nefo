<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	session_start();

	if (isset($_POST['resubmit'])) {
		
		foreach($_POST['comment'] as $sale) {
			$db = $sale['db'];
			$content = str_replace("'","\'",str_replace('%', '&#37;', trim($sale['content'])));
			$orig = str_replace("'","\'",str_replace('%', '&#37;', trim($sale['orig'])));
			
			$number = $sale['number'];
			$status = $sale['status'];
			$oldstatus = $sale['oldstatus'];
			
			
			if ($status != $oldstatus && $status != 0 && $number != 0 && $number != 55) {
				
				// Update DB with new status
				$updateUsersU = "UPDATE customers SET status = '$status' WHERE number = '$number'";
				try
				{
					$result = $pdo2->prepare("$updateUsersU")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
			}
			
		}
		
		$_SESSION['successMessage'] = 'Status updated succesfully!';
		
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
					6: {
						sorter: "dates"
					}
				}
			}); 
			
		}); 
		
EOD;

			



	pageStart("Client status", NULL, $memberScript, "pmembership", NULL, "Client status", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
 <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<form id="registerForm" action="" method="POST">

<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>#</th>
	    <th>Club</th>
	    <th>City</th>
	    <th class='centered'></th>
	    <th class='left'>Status</th>
	    <th class='left'>DB?</th>
	    <th class='centered'>Launched</th>
	    <th class='centered'>Last login<br />(days)</th>
	    <th class='centered'>Last log<br />(days)</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	
	  // Look up ALL clients from customer database
	  $selectUsersU = "SELECT * FROM customers c, customerstatus s WHERE c.status = s.id ORDER BY c.number ASC";
		try
		{
			$results = $pdo2->prepare("$selectUsersU");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
      	// Query to look up customergroups      	
		$selectGroups = "SELECT id, statusName FROM customerstatus WHERE id = 1 || id = 2 || id = 3 || id = 4 || id = 9 || id = 10 || id = 12 || id = 13 || id = 14 || id = 15 ORDER by id ASC";
		try
		{
			$resultsG = $pdo2->prepare("$selectGroups");
			$resultsG->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		while ($group = $resultsG->fetch()) {
				$group_row = sprintf("<option value='%d'>%s</option>",
	  								 $group['id'], $group['statusName']);
	  			$statuslist .= $group_row;
  		}
  		
	  	$statuschange .= "</select>";
		  	
		$i = 1;
		$j = 1;
		while ($row = $results->fetch()) {
			
			$number = $row['number'];
			$clientid = $row['id'];
			$launchdate = $row['launchdate'];
			$status = $row['status'];
			$statusName = $row['statusName'];
			$city = $row['city'];
			$shortName = $row['shortName'];
			
		
  
		  	
			$query = "SELECT db_pwd, customer, warning, domain FROM db_access WHERE customer = '$number'";
			try
			{
				$result = $pdo->prepare("$query");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			if ($data) {
	
				$row = $data[0];
					$db_pwd = $row['db_pwd'];
					$customer = $row['customer'];
					$warning = $row['warning'];
					$domain = $row['domain'];
		
				$db_name = "ccs_" . $domain;
				$db_user = $db_name . "u";
				
				$skipCurrent = 'false';
				$hasDb = 'Yes';
		
				try	{
			 		$pdo6 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
			 		$pdo6->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			 		$pdo6->exec('SET NAMES "utf8"');
				}
				catch (PDOException $e)	{
			  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
			
					$skipCurrent = 'true';
					$hasDb = "<span style='color: red;'>No</span>";
			 		// echo "$output<br />";
			 		//exit();
				}
				
				if ($skipCurrent == 'false') {
				
					// Run individual queries
					
					// Check last log, if more than 3 days then run queries
					$selectUsersU = "SELECT logtime FROM log ORDER BY logtime DESC LIMIT 1";
					try
					{
						$result = $pdo6->prepare("$selectUsersU");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$lastLog = date("Y-m-d", strtotime($row['logtime']));
						$lastLogFull = date("d-m-Y H:i", strtotime($row['logtime']));
						
					$dateNow = date("Y-m-d");
					
					$date1 = new DateTime("$lastLog");
					$date2 = new DateTime("$dateNow");
					$interval = $date1->diff($date2);
					
					$daysSinceLastLog = $interval->days;
					
					
					if ($daysSinceLastLog > 1000) {
						$daysSinceLastLog = "";
					}
			
					$selectUsersU = "SELECT time FROM logins WHERE domain = '$domain' AND email <> 'super@user.com' ORDER BY time DESC LIMIT 1";
					try
					{
						$result = $pdo->prepare("$selectUsersU");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$lastLogin = date("Y-m-d", strtotime($row['time']));
						
					$date1 = new DateTime("$lastLogin");
					$date2 = new DateTime("$dateNow");
					$interval = $date1->diff($date2);
					
					$daysSinceLastLogin = $interval->days;
			
					if ($daysSinceLastLogin > 1000) {
						$daysSinceLastLogin = "<span class='white'>0</span>";
					}

				} else {
					
					$firstLogin = "<span class='white'>00-00-0000</span>";
					$daysSinceLastLogin = "";
					$daysSinceLastLog = "";
					$logsLastMonth = "";
					$logsThisMonth = "";
					$hasDb = "<span style='color: red;'>No</span>";
				
				}

				
			} else {
				
				// No db_access entry found, most likely never launched
				
				$firstLogin = "<span class='white'>00-00-0000</span>";
				$daysSinceLastLogin = "";
				$daysSinceLastLog = "";
				$logsLastMonth = "";
				$logsThisMonth = "";
				$hasDb = "<span style='color: red;'>No</span>";
				
			}
			
			if ($status == 1 || $status == 2 || $status == 3 || $status == 4 || $status == 9 || $status == 10 || $status == 12 || $status == 14 || $status == 15) {
				
				if ($warning == 3) {
					$custStatus = $statusName . "<br /><span style='color: red;'>CUT OFF</span>";
				} else {
					$custStatus = $statusName;
				}
				
			} else if ($warning == 3) {
				$custStatus = "<span style='color: red;'>CUT OFF</span>";
			} else if ($daysSinceLastLog > 2) {
				$custStatus = 'Stopped using SW';
			} else if ($daysSinceLastLog < 3) {
				if ($membermodule == 1) {
					$custStatus = 'Customer - member module';
				} else {
					$custStatus = 'Customer';
				}
			} else {
				$custStatus = 'Unknown';
			}
			
			if ($launchdate != NULL) {
				$launch = date("d-m-Y", strtotime($launchdate));
			} else {
				$launch = "<span class='white'>00-00-0000</span>";
			}



			$statuschange = <<<EOD
	<input type='hidden' name='comment[$i][oldstatus]' value='$status' />
	<input type='hidden' name='comment[$i][number]' value='$number' />
	<select name='comment[$i][status]' id="status" class='noExl defaultinput' style='width: 90px; padding-left: 2px;'>
    <option value='0'>Change?</option>
    $statuslist
EOD;

			  	
			echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td>$statuschange</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='centered clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='centered clickableRow' href='customer.php?user_id=$clientid'>%s</td>
 	   </tr>",
	  $number, $shortName, $city, $custStatus, $hasDb, $launch, $daysSinceLastLogin, $daysSinceLastLog);
			

			$i++;
			
		}

?>
</table><br />
 <input type='hidden' name='resubmit' value='yes' />
 <center><button type="submit" class='cta1'>OK</button></center>
</form>		

<?php

displayFooter();