<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up users
	$selectUsers = "SELECT id, number, shortName, longName, phone, email, language, instagram, facebook, status, linkid FROM customers";
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
			    name: "Clients",
			    filename: "Clients" //do not include extension
		
			  });
		
			});
			
			$('#mainTable').tablesorter({
				usNumberFormat: true
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Clients", NULL, $memberScript, "pmembership", NULL, "Clients", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>


	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />

	 <table class='default' id='mainTable' style='font-size: 12px !important;'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th>Long name</th>
	    <th>Short name</th>
	    <th>Status</th>
	    <th>Language</th>
	    <th>Name</th>
	    <th>Role</th>
	    <th>Phone</th>
	    <th>E-mail</th>
	    <th>Instagram</th>
	    <th>Facebook</th>
	    <th>Link ID</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {

			$id = $user['id'];
			$number = $user['number'];
			$shortName = $user['shortName'];
			$longName = $user['longName'];
			$phone = $user['phone'];
			$email = $user['email'];
			$language = $user['language'];
			$instagram = $user['instagram'];
			$facebook = $user['facebook'];
			$status = $user['status'];
			$linkid = $user['linkid'];
			
		if ($status == 1 || $status == 2 || $status == 3 || $status == 4 || $status == 9 || $status == 10 || $status == 12 || $status == 14 || $status == 15) {
			$query = "SELECT statusName from customerstatus WHERE id = $status";
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
			
	echo <<<EOD
  	   <tr style='border-top: 1px solid #bbb;'>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$number</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$shortName</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$longName</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$custStatus</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$language</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$phone</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$email</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$instagram</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$facebook</strong></td>
  	    <td class='clickableRow' href='customer.php?user_id=$id' style='border-bottom: 0px !important;'><strong>$linkid</strong></td>
  	   </tr>
EOD;

	// Look up contacts for this client
	$selectUsers = "SELECT id, name, role, language, telephone, email, linkid FROM contacts WHERE customer = $number";
	try
	{
		$resultsContacts = $pdo3->prepare("$selectUsers");
		$resultsContacts->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($userC = $resultsContacts->fetch()) {

		$name = $userC['name'];
		$role = $userC['role'];
		$language = $userC['language'];
		$telephone = $userC['telephone'];
		$email = $userC['email'];
		$linkid = $userC['linkid'];
		
	echo <<<EOD
  	   <tr style='border-top: 0px !important;'>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$number</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$shortName</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$longName</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$custStatus</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$language</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$name</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$role</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$telephone</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$email</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'></td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'></td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id' style='border-bottom: 0px !important;'>$linkid</td>
  	   </tr>
EOD;
	}

	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();