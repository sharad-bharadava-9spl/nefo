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
	$selectUsers = "SELECT id, number, shortName, longName, phone, email, language, instagram, facebook, prospect_mail, prospect_facebook, prospect_instagram, prospect_call, registeredSince, status, launchdate, prospect_demo, prospect_demoDate, organic FROM customers WHERE status < 5 AND DATE(registeredSince) IS NOT NULL ORDER BY id DESC";
	//$selectUsers = "SELECT id, number, shortName, longName, phone, email, language, instagram, facebook, prospect_mail, prospect_facebook, prospect_instagram, prospect_call, registeredSince FROM customers WHERE status < 5";
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
				usNumberFormat: true,
				headers: {
					1: {
						sorter: "dates"
					},
					2: {
						sorter: "dates"
					},
					9: {
						sorter: "dates"
					},
					10: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					},
					12: {
						sorter: "dates"
					},
					13: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Prospect tracking", NULL, $memberScript, "pmembership", NULL, "Prospect tracking", $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
	    <th>Added</th>
	    <th>Launched?</th>
	    <th>Short name</th>
	    <th>Status</th>
	    <th>Organic</th>
	    <th>E-mail</th>
	    <th>Phone</th>
	    <th>Instagram</th>
	    <th>Facebook</th>
	    <th>Mailed</th>
	    <th>Instagrammed</th>
	    <th>Facebooked</th>
	    <th>Called</th>
	    <th>Demo organised?</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {

			$id = $user['id'];
			$number = $user['number'];
			$registeredSince = date('d-m-Y', strtotime($user['registeredSince']));
			$launchdate = date('d-m-Y', strtotime($user['launchdate']));
			$shortName = $user['shortName'];
			$longName = $user['longName'];
			$phone = $user['phone'];
			$email = $user['email'];
			$instagram = $user['instagram'];
			$facebook = $user['facebook'];
			$prospect_mail = $user['prospect_mail'];
			$prospect_facebook = $user['prospect_facebook'];
			$prospect_instagram = $user['prospect_instagram'];
			$prospect_call = $user['prospect_call'];
			$prospect_demo = $user['prospect_demo'];
			$prospect_demoDate = $user['prospect_demoDate'];
			$status = $user['status'];
			$organic = $user['organic'];
			
			if ($organic == 0) {
				$organic = 'No';
			} else {
				$organic = 'Yes';
			}
			
			if ($launchdate == '01-01-1970') {
				$launchdate = "";
			}

			$query = "SELECT statusName FROM customerstatus WHERE id = $status";
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
				$statusName = $row['statusName'];
			
			if ($phone != '') {
				$phone = "<center><img src='images/complete.png' width='16' /></center>";
			} else {
				$phone = "";
			}
			
			if ($email != '') {
				$email = "<center><img src='images/complete.png' width='16' /></center>";
			} else {
				$email = "";
			}
			
			if ($instagram != '') {
				$instagram = "<center><img src='images/complete.png' width='16' /></center>";
			} else {
				$instagram = "";
			}
			
			if ($facebook != '') {
				$facebook = "<center><img src='images/complete.png' width='16' /></center>";
			} else {
				$facebook = "";
			}
			
			if ($prospect_mail == NULL) {
				$prospect_mail = "<a href='uTil/prospect_mail.php?id=$id' style='color: #333;'>No</a>";
			} else {
				$prospect_mail = "<a href='uTil/prospect_mail.php?id=$id&set=null' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_mail)) . "</a>";
			}
			
			if ($prospect_facebook == NULL) {
				$prospect_facebook = "<a href='uTil/prospect_facebook.php?id=$id' style='color: #333;'>No</a>";
			} else {
				$prospect_facebook = "<a href='uTil/prospect_facebook.php?id=$id&set=null' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_facebook)) . "</a>";
			}
			
			if ($prospect_instagram == NULL) {
				$prospect_instagram = "<a href='uTil/prospect_instagram.php?id=$id' style='color: #333;'>No</a>";
			} else {
				$prospect_instagram = "<a href='uTil/prospect_instagram.php?id=$id&set=null' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_instagram)) . "</a>";
			}
			
			if ($prospect_call == NULL) {
				$prospect_call = "<a href='uTil/prospect_call.php?id=$id' style='color: #333;'>No</a>";
			} else {
				$prospect_call = "<a href='uTil/prospect_call.php?id=$id&set=null' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_call)) . "</a>";
			}
			
			if ($prospect_demo == NULL) {
				$prospect_demo = "<a href='uTil/prospect_demo.php?id=$id' style='color: #333;'>No</a>";
			} else {
				$prospect_demo = "<a href='uTil/prospect_demo.php?id=$id&set=null' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_demo)) . "</a>";
			}
			
			
	echo <<<EOD
  	   <tr>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$number</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$registeredSince</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$launchdate</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$shortName</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$statusName</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$organic</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$email</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$phone</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$instagram</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$facebook</td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_mail</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_facebook</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_instagram</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_call</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_demo</center></td>
  	   </tr>
EOD;
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();