<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_GET['y'])) {
		
		$year = $_GET['y'];
		$month = $_GET['m'];
		
		$selectUsers = "SELECT c.id, c.brand, c.registeredSince, c.number, c.shortName, c.city, c.country, c.status, c.type, c.private, s.statusName, c.contract, c.language FROM customers c, customerstatus s WHERE c.status = s.id AND MONTH(launchdate) = $month AND YEAR(launchdate) = $year AND (c.status = 5 OR c.status = 6 OR c.status = 7 OR c.status = 8 OR c.status = 9 OR c.status = 11 OR c.status = 13) ORDER by c.id ASC";
		
	} else {
	
		$selectUsers = "SELECT c.id, c.brand, c.registeredSince, c.number, c.shortName, c.city, c.country, c.status, c.type, c.private, s.statusName, c.contract, c.language FROM customers c, customerstatus s WHERE c.status = s.id AND (c.status = 5 OR c.status = 6 OR c.status = 7 OR c.status = 8 OR c.status = 9 OR c.status = 11 OR c.status = 13) ORDER by c.id ASC";
		
	}
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
		
EOD;


	pageStart("Clients", NULL, $memberScript, "pmembership", NULL, "CLIENTS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='non-clients.php' class='cta1'>Non-clients</a></center>

<br />
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
<br />

<style>
th {
  position: -webkit-sticky;
  position: sticky;
  top: 0;
  z-index: 2;
}
</style>
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>Brand</th>
	    <th class='centered'>Type</th>
	    <th class='centered'>#</th>
	    <th class='centered'>Registered</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>City</th>
	    <th>Country</th>
	    <th>Language</th>
	    <th>Status</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {
	
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
			$custStatus = $user['statusName'];			
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
	
	echo sprintf("
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%d</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>",
  	  $user['id'], $brand, $user['id'], $private, $user['id'], $user['number'], $user['id'], $registered, $user['id'], $user['shortName'], $user['id'], $user['city'], $user['id'], $user['country'], $user['id'], $user['language'], $user['id'], $custStatus
  	  );


	echo sprintf("
  	   <td style='text-align: center;' class='clickableRow' href='profile.php?user_id=%d&openComment'>%s</td>
	  </tr>",
	  $user['user_id'], $comment, $user['id']
	  );
	  
  }
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
