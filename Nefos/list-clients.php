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
	$selectUsers = "SELECT id, number, shortName, longName, phone, email, language, instagram, facebook FROM customers";
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
			    name: "Customers",
			    filename: "Customers" //do not include extension
		
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
					3: {
						sorter: "dates"
					}
				}
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
	    <th>Language</th>
	    <th>Phone</th>
	    <th>E-mail</th>
	    <th>Instagram</th>
	    <th>Facebook</th>
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
			
	echo <<<EOD
  	   <tr>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$number</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$shortName</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$longName</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$phone</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$email</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$language</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$instagram</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$facebook</td>
  	   </tr>
EOD;
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();