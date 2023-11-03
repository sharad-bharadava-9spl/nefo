<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
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
			
			if ($content != $orig) {
			
				$selectUsersU = "UPDATE activity SET comment = '$content' WHERE sqldb = '$db'";
				
				try
				{
					$result = $pdo->prepare("$selectUsersU")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
			}
			
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
		
			$_SESSION['successMessage'] = 'Comments / Status updated succesfully!';
		
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
					5: {
						sorter: "dates"
					},
					6: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					},
					8: {
						sorter: "dates"
					},
					9: {
						sorter: "dates"
					},
					10: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					},
					12: {
						sorter: "dates"
					}
				}
			}); 
			
		}); 
		
EOD;

			



	pageStart("Activity report", NULL, $memberScript, "pmembership", NULL, "Activity report", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<form id="registerForm" action="" method="POST">
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
	    <th class='centered'>#</th>
	    <th class='centered'>Club</th>
	    <th class='centered'>City</th>
	    <th class='centered'></th>
	    <th class='left'>Status</th>
	    <th class='centered'>Launched</th>
	    <!--<th class='centered'>First login</th>-->
	    <th class='centered'>Start date</th>
	    <th class='centered'>Last login</th>
	    <!--<th class='centered'>Last dispense</th>-->
	    <th class='centered'>Last member activity</th>
	    <th class='centered'><strong>Last activity</strong></th>
	    <th class='centered'>Last log entry</th>
	    <th class='centered' class='noExl'>Comment</th>
	    <th style='color: white;'>Comment</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	try	{
		$dbh = new PDO('mysql:host=127.0.0.1:3306;', 'root', 'uqgj5nif5OqjtO3z');
 		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$dbh->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
		
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
 		
	}
	
$sql = $dbh->query('SHOW DATABASES');
$getAllDbs = $sql->fetchALL(PDO::FETCH_ASSOC);

$i = 1;
foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_irena' && $database != 'ccs_masterdb' && $database != 'ccs_andyclub1' && $database != 'ccs_andyclub2' && $database != 'ccs_andysclub3' && $database != 'ccs_andyshouse' && $database != 'ccs_berryscorner' && $database != 'ccs_berryshole' && $database != 'ccs_ccstest' && $database != 'ccs_demo' && $database != 'ccs_demo1' && $database != 'ccs_demo2' && $database != 'ccs_demo3' && $database != 'ccs_demo4' && $database != 'ccs_demo5' && $database != 'ccs_g13viejo' && $database != 'ccs_iuhhfisud' && $database != 'ccs_jazzhuset' && $database != 'ccs_jazzhusetnano' && $database != 'ccs_kjell' && $database != 'ccs_weedbunny') {
		
	$domain = substr($database,4);
		
	$query = "SELECT db_pwd, customer, warning FROM db_access WHERE domain = '$domain'";
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

		$db_name = "ccs_" . $domain;
		$db_user = $db_name . "u";

		try	{
	 		$pdo4 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo4->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo4->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
	
	 		echo $output;
	 		exit();
		}
		
		$selectUsersU = "SELECT c.number, c.launchdate, c.startdate, c.status, s.statusName, c.membermodule, c.number, c.city FROM customers c, customerstatus s WHERE c.status = s.id AND c.number = '$customer'";
		try
		{
			$result = $pdo2->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$rowX = $result->fetch();
			$launchdate = $rowX['launchdate'];
			$startdate = $rowX['startdate'];
			$status = $rowX['status'];
			$statusName = $rowX['statusName'];
			$membermodule = $rowX['membermodule'];
			$number = $rowX['number'];
			$city = $rowX['city'];
			
				if ($launchdate != NULL) {
					$launch = date("d-m-Y", strtotime($launchdate));
				} else {
					$launch = "<span class='white'>00-00-0000</span>";
				}
				if ($startdate != NULL) {
					$start = date("d-m-Y", strtotime($startdate));
				} else {
					$start = "<span class='white'>00-00-0000</span>";
				}
			
		
		$selectUsersU = "SELECT comment FROM activity WHERE sqldb = '$database'";
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
			$comment = $row['comment'];
		/*
		$selectUsersU = "SELECT domain, trialMode from systemsettings";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$domain = $row['domain'];
			$trialMode = $row['trialMode'];
			*/
			if ($trialMode == 1) {
				$trial = 'Yes';
			} else {
				$trial = 'No';
			}
			/*
		$selectUsersU = "SELECT time FROM logins WHERE domain = '$domain' AND email <> 'super@user.com' ORDER BY time ASC LIMIT 1";
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
			$firstLogin = date("d-m-Y", strtotime($row['time']));
			
		if ($firstLogin == '01-01-1970') {*/
			$firstLogin = "<span class='white'>00-00-0000</span>";
		/*} else {
			$firstLogin = $firstLogin;
		}*/

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
			$lastLogin = date("d-m-Y H:i", strtotime($row['time']));
			$lastLogin2 = date("d-m-Y H:i", strtotime($row['time']) + 3600);
			
		if ($lastLogin == '01-01-1970') {
			$lastLogin = "<span class='white'>00-00-0000</span>";
		} else {
			$lastLogin = $lastLogin;
		}
		
		/*$selectUsersU = "SELECT saletime FROM sales ORDER BY saletime DESC LIMIT 1";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lastSale = date("d-m-Y H:i", strtotime($row['saletime']));
			$lastSale2 = date("d-m-Y H:i", strtotime($row['saletime']) + 3600);
			*/
		$selectUsersU = "SELECT logtime FROM log ORDER BY logtime DESC LIMIT 1";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lastLog = date("d-m-Y H:i", strtotime($row['logtime']));
			
		$selectRows = "SELECT COUNT(id) FROM log";
		$rowCount = $pdo4->query("$selectRows")->fetchColumn();
		
		if ($lastSale == '01-01-1970') {
			$lastSale = "<span class='white'>00-00-0000</span>";
		} else {
			$lastSale = $lastSale;
		}
		
		$selectUsersU = "SELECT paymentdate FROM memberpayments WHERE userid <> 1 ORDER BY paymentdate DESC LIMIT 1";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lastMem = date("d-m-Y", strtotime($row['paymentdate']));
			$lastMem2 = date("d-m-Y H:i", strtotime($row['paymentdate']) + 3600);
			
		if ($lastMem == '01-01-1970') {
			$lastMem = "<span class='white'>00-00-0000</span>";
		} else {
			$lastMem = $lastMem;
		}
		
		if (strtotime($lastLogin2) > strtotime($lastSale2) && strtotime($lastLogin2) > strtotime($lastMem2)) {
			$lastActivity = $lastLogin2;
		} else if (strtotime($lastSale2) > strtotime($lastMem2)) {
			$lastActivity = $lastSale2;
		} else if (strtotime($lastMem2) > strtotime($lastSale2)) {
			$lastActivity = $lastMem2;
		} else if (strtotime($lastMem2) == strtotime($lastSale2)) {
			$lastActivity = $lastMem2;
		} else {
			$lastActivity = "<span class='white'>00-00-0000</span>";
		}
		
		$from = strtotime($lastLog);
		$today = time();
		$difference = $today - $from;
		$daysSinceLog = floor($difference / 86400);
		
		if ($status == 1 || $status == 2 || $status == 3 || $status == 4 || $status == 9 || $status == 10 || $status == 12 || $status == 14 || $status == 15) {
			$custStatus = $statusName;			
		} else if ($warning == 3) {
			$custStatus = 'Access cut';
		} else if ($daysSinceLog > 2) {
			$custStatus = 'Stopped using SW';
		} else if ($daysSinceLog < 3) {
			if ($membermodule == 1) {
				$custStatus = 'Customer - member module';
			} else {
				$custStatus = 'Customer';
			}
		} else {
			$custStatus = 'Unknown';
		}
		
		
		if ($lastLog == '01-01-1970') {
			$lastLog = "<span class='white'>00-00-0000</span>";
		} else {
			$lastLog = $lastLog;
		}
		
		$statuschange = <<<EOD
		<input type='hidden' name='comment[$i][oldstatus]' value='$status' />
		<input type='hidden' name='comment[$i][number]' value='$number' />
		<select name='comment[$i][status]' id="status" class='noExl' style='width: 80px;'>
        <option value='0'>Change?</option>
EOD;
      
      	// Query to look up customergroups      	
		$selectGroups = "SELECT id, statusName FROM customerstatus WHERE id = 1 || id = 2 || id = 3 || id = 4 || id = 9 || id = 10 || id = 12 || id = 13 ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
			if ($group['id'] != $status) {
				$group_row = sprintf("<option value='%d'>%s</option>",
	  								 $group['id'], $group['statusName']);
	  			$statuschange .= $group_row;
  			}
  		}
	  			$statuschange .= "</select>";

			
			echo sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>$statuschange</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td><strong>%s</strong></td>
  	   <td>%s</td>
   	   <td class='noExl'><textarea name='comment[$i][content]'>%s</textarea><input type='hidden' name='comment[$i][db]' value='%s' /><input type='hidden' name='comment[$i][orig]' value='%s' /></td>
   	   <td>$comment</td>
 	   </tr>",
	  $number, $domain, $city, $custStatus, $launch, $start, $lastLogin, $lastMem, $lastActivity, $lastLog, $comment, $database, $comment, $comment);
	  
	  
	  $i++;

	}
}
}

?>
</table><br />
 <input type='hidden' name='resubmit' value='yes' />
 <button type="submit">OK</button><br />
</form>		

<?php

displayFooter();