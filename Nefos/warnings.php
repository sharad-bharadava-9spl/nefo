<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
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
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_warning(warningid) {
	if (confirm("{$lang['donation-deleteconfirm']}")) {
				window.location = "uTil/delete-warning.php?warningid=" + warningid;
				}
}

		
EOD;


	pageStart("Warnings & Cutoffs", NULL, $memberScript, "pmembership", NULL, "Warnings & Cutoffs", $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
	    <th class='centered'>Brand</th>
	    <th class='centered'>Type</th>
	    <th class='centered'>#</th>
	    <th class='centered'>Registered</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>City</th>
	    <th>Warning</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	// Query to look up clients with warnings
	$selectUsers = "SELECT domain, warning, customer FROM db_access WHERE warning > 0 ORDER by customer ASC";
	try
	{
		$results = $pdo->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}


	while ($row = $results->fetch()) {
		
		$domain = $row['domain'];
		$warning = $row['warning'];
		$customer = $row['customer'];
		
		// Query to look up users
		$selectUsers = "SELECT id, brand, registeredSince, number, shortName, city, private FROM customers WHERE number = '$customer'";
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

		$user = $result->fetch();
		
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
		
		
		if ($warning == 1) {
			$warning = "Soft";
		} else if ($warning == 2) {
			$warning = "Last warning";
		} else {
			$warning = "<span style='color: red;'>CUT OFF</span>";
		}
	
	echo sprintf("
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%d</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=%d'>%s</td>
  	   <td><a href='javascript:delete_warning(%s)'><img src='images/delete.png' height='15' /></a></td>
  	   ",
  	  $user['id'], $brand, $user['id'], $private, $user['id'], $user['number'], $user['id'], $registered, $user['id'], $user['shortName'], $user['id'], $user['city'], $user['id'], $warning, $user['number']
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
