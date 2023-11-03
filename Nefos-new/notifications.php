<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up users
	$selectUsers = "SELECT * FROM notifications ORDER BY id DESC";
	try
	{
		$results = $pdo2->prepare("$selectUsers");
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
					0: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Notifications", NULL, $memberScript, "pmembership", NULL, "Notifications", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='notification-send.php' class='cta1'>Send notification</a><br />

         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
         
</center>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Time</th>
	    <th>Operator</th>
	    <th>Type</th>
	    <th>Club</th>
	    <th>Usergroups</th>
	    <th>Content</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {
			
			$time = date("d-m-Y H:i", strtotime($user['time']));
			$id = $user['id'];
			$customer = $user['customer'];
			$userGroups = $user['userGroups'];
			$done = $user['done'];
			$notification = $user['notification'];
			$category = $user['category'];
			$operator = $user['operator'];
			
			if ($notification != '') {
				
				$commentRead = "
				                <img src='images/comments.png' id='comment$id' /><div id='helpBox$id' class='helpBox'>$notification</div>
				                <script>
				                  	$('#comment$id').on({
								 		'mouseover' : function() {
										 	$('#helpBox$id').css('display', 'block');
								  		},
								  		'mouseout' : function() {
										 	$('#helpBox$id').css('display', 'none');
									  	}
								  	});
								</script>
				                ";
				
			} else {
				
				$commentRead = "";
				
			}

			
			$query = "SELECT first_name FROM users WHERE user_id = '$operator'";
			try
			{
				$result = $pdo2->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$first_name = $row['first_name'];
					
			$query = "SELECT shortName FROM customers WHERE id = '$customer'";
			try
			{
				$result = $pdo2->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$shortName = $row['shortName'];
					
			// Types: 0 = text, 1 = contact update, 2 = calendar invite, 3 = New invoice, 4 = Software update, 5 = help center ticket, 6 = Stock notification
			if ($category == 0) {
				$cat = 'Text';
			} else if ($category == 1) {
				$cat = 'Contact update';
			} else if ($category == 3) {
				$cat = 'New invoice';
			}
				
	
		
			echo "
  	   <tr>
  	    <td>$time</td>
  	    <td>$first_name</td>
  	    <td>$cat</td>
  	    <td>$shortName</td>
  	    <td>$userGroups</td>
  	    <td class='centered'><span class='relativeitem'>$commentRead</span></td>
  	   </tr>";
	  
  		}
?>

	 </tbody>
	 </table>
<?php  displayFooter();