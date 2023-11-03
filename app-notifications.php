<?php
	// created by konstant for notification panel on 28-06-2022
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up users
	$selectUsers = "SELECT * FROM pushnotification ORDER BY id DESC";
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
			    name: "Notifications",
			    filename: "Notifications" //do not include extension
		
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
	    <th>User</th>
	    <th>Title</th>
	    <th>Image</th>
	    <th>Type</th>
	    <th>Content</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {
			
			$time = date("d-m-Y H:i", strtotime($user['create_at']));
			$id = $user['id'];
			$customer = $user['user_id'];
			$title = $user['title'];
			$description = $user['description'];
			$image = $user['image'];
			$note_type = $user['note_type'];
			
			if ($description != '') {
				
				$commentRead = "
				                <img src='images/comments.png' id='comment$id' /><div id='helpBox$id' class='helpBox'>$description</div>
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
			$img_disp = "";
			$domain = $_SESSION['domain'];
			if($image != ""){
				$image_loc  = "images/_$domain/notifications/$image";
				$img_disp = "<img src='$image_loc' height='100'/>";
			}

			
			$query = "SELECT first_name, last_name FROM users WHERE user_id = '$customer'";
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
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
					

					
			// Types: 0 = text, 1 = contact update, 2 = calendar invite, 3 = New invoice, 4 = Software update, 5 = help center ticket, 6 = Stock notification
			if ($note_type == 1) {
				$note = 'Success';
			} else if ($note_type == 2) {
				$note = 'Info';
			} else if ($note_type == 3) {
				$note = 'Alert';
			}else if ($note_type == 4) {
				$note = 'Warning';
			}
				
	
		
			echo "
  	   <tr>
  	    <td>$time</td>
  	    <td>$first_name $last_name</td>
  	    <td>$title</td>
  	    <td>$img_disp</td>
  	    <td>$note</td>
  	    <td class='centered'><span class='relativeitem'>$commentRead</span></td>
  	   </tr>";
	  
  		}
?>

	 </tbody>
	 </table>
<?php  displayFooter();