<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up contacts
	$selectUsers = "SELECT id, customer, name, role, language, telephone, email FROM contacts WHERE customer <> 9 && customer <> 34 && customer <> 55 && customer <> 99999 ORDER BY customer ASC";
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
			    name: "Contacts",
			    filename: "Contacts" //do not include extension
		
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


	pageStart("Contacts", NULL, $memberScript, "pmembership", NULL, "Contacts", $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
	    <th>Long name</th>
	    <th>Short name</th>
	    <th>Status</th>
	    <th>Contact</th>
	    <th>Role</th>
	    <th>Phone</th>
	    <th>E-mail</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {

			$number = $user['customer'];
			$name = $user['name'];
			$role = $user['role'];
			$language = $user['language'];
			$telephone = $user['telephone'];
			$email = $user['email'];

			// Query to look up customer data
			$selectUsers = "SELECT id, shortName, longName, status FROM customers WHERE number = '$number'";
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
				$status = $row['status'];
			
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
  	   <tr>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$number</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$shortName</td>
  	    <td class='clickableRow' href='contacts.php?user_id=$id'>$custStatus</td>
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
<?php  displayFooter();