<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['searchfield'])) {
		
		$phrase = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['searchfield'])));
	
		$selectUsers = "SELECT id, brand, registeredSince, number, shortName, city, country, status, type, private, contract, language, alias FROM customers WHERE shortName LIKE ('%$phrase%') OR longName LIKE ('%$phrase%') OR number LIKE ('%$phrase%') OR city LIKE ('%$phrase%') OR state LIKE ('%$phrase%') OR email LIKE ('%$phrase%') OR phone LIKE ('%$phrase%') OR alias LIKE ('%$phrase%') ORDER by number ASC";
					
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
						sorter: "dates"
					},
					4: {
						sorter: "currency"
					},
					7: {
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
						sorter: "dates"
					},
					4: {
						sorter: "currency"
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
					6: {
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


		pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, "Search", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>		
		
<center><h1>CLIENTS</h1></center>
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered' style='position: sticky;'>Brand</th>
	    <th class='centered' style='position: sticky;'>Type</th>
	    <th class='centered' style='position: sticky;'>#</th>
	    <th class='centered' style='position: sticky;'>Registered</th>
	    <th style='position: sticky;'><?php echo $lang['global-name']; ?></th>
	    <th style='position: sticky;'>Alias</th>
	    <th style='position: sticky;'>City</th>
	    <th style='position: sticky;'>Country</th>
	    <th style='position: sticky;'>Language</th>
	    <th style='position: sticky;'>Status</th>
	    <th style='position: sticky;'>Type</th>
	    <th style='position: sticky;'>Contract?</th>
	    <th style='position: sticky;'><?php echo $lang['global-comment']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php


	while ($user = $results->fetch()) {
		
		$custNumbers = $user['number'] . ", ";

	// Does user have comments?
	$getNotes = "SELECT noteid, notetime, userid, note FROM customernotes WHERE userid = {$_SESSION['user_id']} ORDER by notetime DESC";
	try
	{
		$result = $pdo3->prepare("$getNotes");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if (!$data) {
   		$comment = '';
	} else {
   		$comment = "<img src='images/note.png' width='16' /><span style='display:none'>1</span>";
	}
	
	if ($user['type'] == 1) {
		$userType = "Normal";
	} else if ($user['type'] == 2) {
		$userType = "<strong>VIP</strong>";
	} else {
		$userType = "";
	}
	
	if ($user['private'] == 1) {
		$private = "Private";
	} else if ($user['private'] == 2) {
		$private = "Business";
	} else {
		$private = "";
	}
	
	if ($user['brand'] == 2) {
		$brand = "Nefos";
	} else {
		$brand = "CCS";
	}
	
	if ($user['registeredSince'] == NULL || $user['registeredSince'] == 0) {
		$registered = "<span style='color: #fff;'>00-00-0000</span>";
	} else {
		$registered = date('d-m-Y', strtotime($user['registeredSince']));
	}
	
	if ($user['status'] == 10 ||$user['status'] == 11 ||$user['status'] == 14) {
		echo "<tr class='negative'>";
	} else {
		echo "<tr>";
	}
	
	$status = $user['status'];
	
		if ($status == 1 || $status == 2 || $status == 3 || $status == 4 || $status == 9 || $status == 10 || $status == 12 || $status == 14 || $status == 15) {
			$query = "SELECT statusName FROM customerstatus WHERE id = $status";
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
				$custStatus = $row['statusName'];
			
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
		
		
	
	// Look up domain via number
	$queryX = "SELECT db_pwd, customer, domain FROM db_access WHERE customer = '{$user['number']}'";
	try
	{
		$resultX = $pdo->prepare("$queryX");
		$resultX->execute();
		$dataX = $resultX->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	if ($dataX) {

		$row = $dataX[0];
		$db_pwd = $row['db_pwd'];
		$customer = $row['customer'];
		$domain = $row['domain'];
		
		if ($domain != 'exotic') {

			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";
		
			// Create pdo4
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
			
			// Ping the 'contract' table
			$selectRows = "SELECT COUNT(cif) FROM contract";
			$rowCount = $pdo4->query("$selectRows")->fetchColumn();
		
			
			if ($rowCount == 0) {
				$contract = "<span style='color: red; font-weight: 800;'>No</span>";
			} else {
				$contract = "Yes";
			}

		} else {
			$contract = "";
		}

	} else {
			$contract = "";
	}
	
	echo sprintf("
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%d</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='customer.php?user_id=%d'>%s</td>",
  	  $user['id'], $brand, $user['id'], $private, $user['id'], $user['number'], $user['id'], $registered, $user['id'], $user['shortName'], $user['id'], $user['alias'], $user['id'], $user['city'], $user['id'], $user['country'], $user['id'], $user['language'], $user['id'], $custStatus, $user['id'], $userType, $user['id'], $contract
  	  );


	echo sprintf("
  	   <td style='text-align: center;' class='clickableRow' href='profile.php?user_id=%d&openComment'>%s</td>
	  </tr>",
	  $user['user_id'], $comment, $user['id']
	  );
	  
  }
  
  $custNumbers = substr($custNumbers, 0, -2);
  
  
?>

	 </tbody>
	 </table>
	 <br /><br />
<center><h1>CONTACTS</h1></center>

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th>Long name</th>
	    <th>Short name</th>
	    <th>Contact</th>
	    <th>Role</th>
	    <th>Phone</th>
	    <th>E-mail</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	  
	// Query to look up contacts
	$selectUsers = "SELECT id, customer, name, role, language, telephone, email FROM contacts WHERE customer LIKE ('%$phrase%') OR name LIKE ('%$phrase%') OR telephone LIKE ('%$phrase%') OR email LIKE ('%$phrase%') ORDER BY customer ASC";
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


		while ($user = $results->fetch()) {

			$number = $user['customer'];
			$name = $user['name'];
			$role = $user['role'];
			$language = $user['language'];
			$telephone = $user['telephone'];
			$email = $user['email'];

			// Query to look up customer data
			$selectUsers = "SELECT id, shortName, longName FROM customers WHERE number = '$number'";
			try
			{
				$result = $pdo3->prepare("$selectUsers");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$row = $result->fetch();
				$id = $row['id'];
				$shortName = $row['shortName'];
				$longName = $row['longName'];
			
	echo <<<EOD
  	   <tr>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$number</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$shortName</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$longName</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$name</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$role</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$telephone</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$email</td>
  	   </tr>
EOD;
	  
  }
  
	// Query to look up contacts for THIS customer
	$selectUsers = "SELECT id, customer, name, role, language, telephone, email FROM contacts WHERE customer IN ('$custNumbers') ORDER BY customer ASC";
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


		while ($user = $results->fetch()) {

			$number = $user['customer'];
			$name = $user['name'];
			$role = $user['role'];
			$language = $user['language'];
			$telephone = $user['telephone'];
			$email = $user['email'];

			// Query to look up customer data
			$selectUsers = "SELECT id, shortName, longName FROM customers WHERE number = '$number'";
			try
			{
				$result = $pdo3->prepare("$selectUsers");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$row = $result->fetch();
				$id = $row['id'];
				$shortName = $row['shortName'];
				$longName = $row['longName'];
			
	echo <<<EOD
  	   <tr>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$number</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$shortName</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$longName</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$name</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$role</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$telephone</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$email</td>
  	   </tr>
EOD;
	  
  }
?>

	 </tbody>
	 </table>

	
<?php

	} else {
	
		pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, "Search", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>


<center>
<form id="registerForm" action="" method="POST">
 <div id="overview">
 
<?php
	if ($_SESSION['iPadReaders'] > 0) {
?>
  <input type="text" name="searchfield" placeholder="" /><br /><br /><br />  
<?php
	} else {
?>
  <input type="text" name="searchfield" placeholder="" autofocus /><br /><br /><br />  
<?php
	}
?>
  <button type="submit">Buscar</button>
 </div> <!-- END OVERVIEW -->
</form>
</center>
<br />

<?php } displayFooter();