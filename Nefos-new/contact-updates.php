<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	$queryA = "SELECT id, registeredSince, number, longName, shortName, userid, location_street_name, hash, all_users, approvedby, approved FROM customers_tmp ORDER BY longName ASC, registeredSince DESC";
	try
	{
		$resultsA = $pdo3->prepare("$queryA");
		$resultsA->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$sortScript = <<<EOD
	    $(document).ready(function() {
		    
		    			
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
					0: {
						sorter: "dates"
					}
				}
			}); 
			
		});
		
EOD;

	pageStart("Contact updates", NULL, $sortScript, "pprofile", NULL, "Contact updates", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
 <table class='default' id='mainTable'>
  <thead>	
   <tr>
    <th>Time</th>
    <th>#</th>
    <th>Long name</th>
    <th>Short name</th>
    <th>Group</th>
    <th>Name</th>
	<th>Progress</th>
    <th>Status</th>
    <th>Approved by</th>
    <th></th>
   </tr>
  </thead>
  <tbody>
<?php

	while ($rowA = $resultsA->fetch()) {
		
		$timestamp = date("d-m-Y H:i", strtotime($rowA['registeredSince']));
		$id = $rowA['id'];
		$number = $rowA['number'];
		$longName = $rowA['longName'];
		$shortName = $rowA['shortName'];
		$userid = $rowA['userid'];
		$location_street_name = $rowA['location_street_name'];
		$hash = $rowA['hash'];
		$all_users = $rowA['all_users'];
		$approved = $rowA['approved'];
		$approvedby = $rowA['approvedby'];
		
		if ($approved == 0) {
			$appStatus = "Pending";
		} else if ($approved == 1) {
			$appStatus = "<span style='color: green;'>Approved</span>";
		} else {
			$appStatus = "<span style='color: red;'>Rejected</span>";
		}
		
		$query = "SELECT first_name FROM users WHERE user_id = $approvedby";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$approved_name = $row['first_name'];
	
	
		// Create club PDO (pdo9)
		$queryX = "SELECT db_pwd, customer, domain FROM db_access WHERE customer = '$number'";
		try
		{
			$resultX = $pdo->prepare("$queryX");
			$resultX->execute();
			$dataX = $resultX->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user2: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$row = $dataX[0];
			$db_pwd = $row['db_pwd'];
			$customer = $row['customer'];
			$domain = $row['domain'];

		$db_name = "ccs_" . $domain;
		$db_user = $db_name . "u";
	
		// Create pdo9
		try	{
	 		$pdo9 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo9->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo9->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
	 		echo $output . "<br />";
		}
		
		// Look up username and group
		$query = "SELECT memberno, first_name, userGroup FROM users WHERE user_id = '$userid'";
		try
		{
			$result = $pdo9->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
			$userGroup = $row['userGroup'];
			
		if ($userGroup == 1) {
			$group = 'Admin';
		} else if ($userGroup == 2) {
			$group = 'Staff';
		} else if ($userGroup == 3) {
			$group = 'Vol.';
		} else {
			$group = '';
		}
			
		// Calculate Progress
		$query = "SELECT * FROM contacts_tmp WHERE hash = '$hash'";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($location_street_name == '') {
			$progress = "<span style='color: red;'>25%</span>";
		} else if (!$data) {
			$progress = "<span style='color: red;'>50%</span>";
		} else if ($all_users == '') {
			$progress = "<span style='color: red;'>75%</span>";
		} else {
			$progress = "<span style='color: green;'>100%</span>";
		}
		
		echo <<<EOD
   <tr>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'>$timestamp</td>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'>$number</td>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'>$longName</td>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'>$shortName</td>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'>$group</td>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'>#$memberno $first_name</td>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'><center>$progress</center></td>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'>$appStatus</td>
    <td class='clickableRow' href='contact-submit.php?id=$id&number=$number'>$approved_name</td>
    <td><a href='uTil/delete-contact-submitted.php?hash=$hash'><img src='images/delete.png' width='15' /></a></td>
   </tr>
EOD;
		
	}
	


?>

  </tbody>
 </table>

 <?php displayFooter();