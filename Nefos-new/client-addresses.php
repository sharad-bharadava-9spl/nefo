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
	$selectUsers = "SELECT id, number, shortName, longName, phone, email, street, streetnumber, flat, postcode, city, state, country FROM customers";
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

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th>Long name</th>
	    <th>Short name</th>
	    <th>Phone</th>
	    <th>E-mail</th>
	    <th>Address</th>
	    <th>Postcode</th>
	    <th>City</th>
	    <th>State</th>
	    <th>Country</th>
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
			$street = $user['street'];
			$streetnumber = $user['streetnumber'];
			$flat = $user['flat'];
			$address = "$street $streetnumber $flat";
			$postcode = $user['postcode'];
			$city = $user['city'];
			$state = $user['state'];
			$country = $user['country'];
			
	echo <<<EOD
  	   <tr>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$number</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$shortName</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$longName</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$phone</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$email</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$address</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$postcode</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$city</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$state</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$country</td>
  	   </tr>
EOD;
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();