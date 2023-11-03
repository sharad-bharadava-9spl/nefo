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
	$selectUsers = "SELECT c.id, c.brand, c.registeredSince, c.number, c.shortName, c.city, c.country, c.status, c.type, c.private, s.statusName, c.contract, c.language FROM customers c, customerstatus s WHERE c.status = s.id ORDER by c.id ASC";
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


	pageStart("Clients", NULL, $memberScript, "pmembership", NULL, "CLIENTS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />

<style>
thead, tbody { display: block; }

.scroll tbody {
    height: 70vh;       /* Just for the demo          */
    overflow-y: auto;    /* Trigger vertical scroll    */
    overflow-x: hidden;  /* Hide the horizontal scroll */
}
</style>
	 <table class='default scroll' id='mainTable'>
	  <thead style='width: 100%;'>	
	   <tr style='cursor: pointer;'>
	    <th class='centered' style='position: sticky;'>Brand</th>
	    <th class='centered' style='position: sticky;'>Type</th>
	    <th class='centered' style='position: sticky;'>#</th>
	    <th class='centered' style='position: sticky;'>Registered</th>
	    <th style='position: sticky;'><?php echo $lang['global-name']; ?></th>
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
  	   <td class='clickableRow centered' href='customer.php?user_id=%d'>%s</td>",
  	  $user['id'], $brand, $user['id'], $private, $user['id'], $user['number'], $user['id'], $registered, $user['id'], $user['shortName'], $user['id'], $user['city'], $user['id'], $user['country'], $user['id'], $user['language'], $user['id'], $custStatus, $user['id'], $userType, $user['id'], $contract
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
<script>
	// Change the selector if needed
	var $table = $('table.scroll'),
    $bodyCells = $table.find('tbody tr:first').children(),
    colWidth;

// Adjust the width of thead cells when window resizes
$(window).resize(function() {
    // Get the tbody columns width array
    colWidth = $bodyCells.map(function() {
        return $(this).width();
    }).get();
    
    // Set the width of thead columns
    $table.find('thead tr').children().each(function(i, v) {
        $(v).width(colWidth[i]);
    });    
}).resize(); // Trigger resize handler
</script>
<?php  displayFooter(); ?>
