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
	$selectUsers = "SELECT id, name, telephone, email, city FROM lawyers";
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


	pageStart("Lawyers", NULL, $memberScript, "pmembership", NULL, "Lawyers", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='new-lawyer.php' class='cta'>New lawyer</a></center>

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
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Phone</th>
	    <th>E-mail</th>
	    <th>City</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {

	
	echo sprintf("
  	   <tr><td class='clickableRow' href='edit-lawyer.php?lawyerid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-lawyer.php?lawyerid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-lawyer.php?lawyerid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-lawyer.php?lawyerid=%d'>%s</td></tr>",
  	  $user['id'], $user['name'], $user['id'], $user['telephone'], $user['id'], $user['email'], $user['id'], $user['city'], 
  	  );
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();