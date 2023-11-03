<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_GET['organic'])) {
		$organic = "AND organic = 1";
	} else if (isset($_GET['nonorganic'])) {
		$organic = "AND (organic = 0 OR organic = 2)";
	}
	
	$pos = $_GET['pos'];
	if ($pos == '') {
		$pos = 0;
	}
	
	$href .= strpos($href, '?') === false ? '?' : '&';
    $href .= http_build_query($_GET);
    
    if (strpos($href, 'nonorganic') !== false) {
	    $href = 'nonorganic';
    } else if (strpos($href, 'organic') !== false) {
	    $href = 'organic';
    } else {
	    $href = '';
    }
    
	$query = "SELECT DISTINCT customer FROM prospect_contact";
	try
	{
		$results1 = $pdo2->prepare("$query");
		$results1->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($row1 = $results1->fetch()) {
		
		$customer = $row1['customer'];
		$customerlist .= "$customer ";
		
	}
	$query = "SELECT DISTINCT customer FROM prospect_feedback";
	try
	{
		$results1 = $pdo2->prepare("$query");
		$results1->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($row1 = $results1->fetch()) {
		
		$customer = $row1['customer'];
		$customerlist2 .= "$customer ";
		
	}
	
	$query = "SELECT DISTINCT domain FROM logins";
	try
	{
		$results1 = $pdo->prepare("$query");
		$results1->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($row1 = $results1->fetch()) {
		
		$domain = $row1['domain'];	
		
		$query = "SELECT customer FROM db_access WHERE domain = '$domain'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$customer = $row['customer'];	
				
		$customerlist3 .= "$customer ";
		
	}

	// Query to look up users
	$selectUsers = "SELECT id, number, shortName, longName, phone, email, language, instagram, facebook, prospect_mail, prospect_facebook, prospect_instagram, prospect_call, prospect_visit, registeredSince, status, launchdate, prospect_demo, prospect_demoDate, organic, country, city, addedBy, phone_sms, prospect_sms, prospect_responsible, prospect_followup, prospect_task, us_brand FROM customers WHERE status < 5 AND DATE(registeredSince) IS NOT NULL AND brand = 3 $organic ORDER BY id DESC";
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
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			  	if(s != null && s != ''){
				    var dateArray = s.split('-');
				    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  	}else{
			  		return 0;
			  	}
			  },
			  type: 'numeric'
			});			

			$.tablesorter.addParser({
			  id: 'multiDates',
			  is: function(s) { return false },
			  format: function(s) {
			  	if(s != null && s != ''){
				    var multiDateArray =  s.split('<br>');
				    var dateArray = multiDateArray[0].split('-');
				    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  	}else{
			  		return 0;
			  	}
			  },
			  type: 'numeric'
			});
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					1: {
						sorter: "dates"
					},					
					14: {
						sorter: "multiDates"
					},					
					15: {
						sorter: "multiDates"
					},					
					16: {
						sorter: "multiDates"
					},					
					17: {
						sorter: "multiDates"
					},					
					18: {
						sorter: "multiDates"
					},					
					19: {
						sorter: "multiDates"
					},					
					21: {
						sorter: "dates"
					},					
					22: {
						sorter: "multiDates"
					},					
					23: {
						sorter: "dates"
					},

				}
			}); 

		});

		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Prospect tracking - USA", NULL, $memberScript, "pmembership", NULL, "Prospect tracking - USA", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

  <center>
   <a href='not-interested.php' class='cta1'>Not interested</a>
   <a href='?organic' class='cta1'>Organic</a>
   <a href='?nonorganic' class='cta1'>Non-organic</a>
   <a href='conversions.php' class='cta1'>Conversions</a>
   <a href='new-client.php' class='cta1'>New client</a>
   <a href='prospects.php' class='cta4'>Europe</a>
  </center>

         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
<br />
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
	    <th>#</th>
	    <th>Added</th>
	    <th>Added by</th>
	    <th>Type</th>
<!--	    <th>Launched?</th>-->
	    <th>Short name</th>
	    <th>City</th>
	    <th>Country</th>
	    <th>Status</th>
	    <th>Organic</th>
	    <th class='noExl'>E-mail</th>
	    <th class='noExl'>Phone</th>
	    <th class='noExl'>SMS</th>
	    <th class='noExl'>Instagram</th>
	    <th class='noExl'>Facebook</th>
	    <th>Mailed</th>
	    <th>Instagrammed</th>
	    <th>Facebooked</th>
	    <th>Called</th>
	    <th>SMS</th>
	    <th>Visited</th>
	    <th>Responsible</th>
	    <th>Last contact</th>
	    <th>Customer reply</th>
	    <th>Date of trial</th>
	    <th>Task</th>
	    <th>Follow up</th>
	    <th class='noExl'>Comments</th>
	    <th class='noExl centered'>Actions</th>
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
			$phone_sms = $user['phone_sms'];
			$email = $user['email'];
			$instagram = $user['instagram'];
			$facebook = $user['facebook'];
			$prospect_mail = $user['prospect_mail'];
			$prospect_facebook = $user['prospect_facebook'];
			$prospect_instagram = $user['prospect_instagram'];
			$prospect_call = $user['prospect_call'];
			$prospect_sms = $user['prospect_sms'];
			$prospect_visit = $user['prospect_visit'];
			$prospect_demo = $user['prospect_demo'];
			$prospect_demoDate = $user['prospect_demoDate'];
			$status = $user['status'];
			$organic = $user['organic'];
			$country = $user['country'];
			$city = $user['city'];
			$addedBy = $user['addedBy'];
			$prospect_responsible = $user['prospect_responsible'];
			$prospect_followup = $user['prospect_followup'];
			$prospect_task = $user['prospect_task'];
			$us_brand = $user['us_brand'];
			
			if ($us_brand == 1) {
				$us_brandtext = "Dispensary";
			} else if ($us_brand == 2) {
				$us_brandtext = "Lounge";
			} else if ($us_brand == 3) {
				$us_brandtext = "Dispensary + lounge";
			} else {
				$us_brandtext = "";
			}
			
			
			if ($prospect_task == '') {
				
				$task = "<img src='images/plus-new.png' width='15' />";
				
			} else {
				
				$task = "<a href='$prospect_task' target='_blank'><img src='images/task.png' width='15' /></a>";

			}
			
			
			if ($prospect_responsible == 0) {
				
				$responsible = "<span style='color: red;'>None</span>";
				
			} else {
				
				// Look up user
				$query = "SELECT first_name FROM users WHERE user_id = '$prospect_responsible'";
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
					$responsible = $row['first_name'];
					
			}
			
			if ($prospect_followup == NULL) {
				
				$followup = "<span style='color: #fff;'>00-00-0000</span>";
				$followupicon = "<img src='images/plus-new.png' width='15' style='margin-left: -70px;' />";
				$deleteFollowup = "";
				
			} else {
				
				$followupicon = "";
				$followup = date('d-m-Y', strtotime($prospect_followup));
				
				if (strtotime($prospect_followup) < strtotime(date('Y-m-d'))) {
					
					$followup = "<span style='color: red;'>" . date('d-m-Y', strtotime($prospect_followup)) . "</span>";
					
				} else {
					
					$followup = "<span style='color: #333;'>" . date("d-m-Y", strtotime($prospect_followup)) . "</span>";
					
				}
				
				
			}


			// Look up user
			$query = "SELECT first_name, last_name FROM users WHERE user_id = '$addedBy'";
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
				$operator = $row['first_name'];
						
			// Look up comments. If none, show 'add comment' button. If there are comments, show.
			$query = "SELECT id, user_id, time, comment FROM comments WHERE customer = '$id' ORDER BY time DESC";
			try
			{
				$result = $pdo3->prepare("$query");
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
				
				$commentShow = "<a href='add-comment.php?client=$id'><img src='images/plus-new.png' width='15' /></a>";
				
				
			} else {
	
				$comments = '';
					
				foreach ($data as $row) {
			
					$commentid = $row['id'];
					$commentuser_id = $row['user_id'];
					$commenttime = date("d/m/Y H:i", strtotime($row['time']));
					$comment = $row['comment'];
						
					// Look up user
					$query = "SELECT first_name, last_name FROM users WHERE user_id = '$commentuser_id'";
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
						
					$comments .= "<strong><span style='font-size: 16px;'>$first_name $last_name</span><br />$commenttime</strong><br />$comment<br /><br />";
				
				}
				
			
				$commentShow = <<<EOD
				
<a href='#' id='showComment$id'><img src='images/comments.png' width='15' /></a>
<div id="commentBox$id" class='commentBox' style="display: none;">
<a href='#' id='hideComment$id' class="closeComment"><img src="images/delete.png" width='22' /></a>
<h1>Comments for $shortName</h1><br />
<a href='add-comment.php?client=$id' class='addComment'><img src='images/plus-new.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Add comment</a><br /><br /><br />
$comments

</div>
<script>
$("#showComment$id").click(function (e) {
	e.preventDefault();
	$("#commentBox$id").css("display", "block");
});
$("#hideComment$id").click(function (e) {
	e.preventDefault();
	$("#commentBox$id").css("display", "none");
});
</script>
EOD;
			}
			
			if ($organic == 1) {
				$organicFlag = 'Yes';
			} else {
				$organicFlag = 'No';
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
			
			/*
			if ($prospect_mail == NULL) {
				$prospect_mail = "<a href='#' onClick='return prospect_mail($id)' style='color: #fff;'>00-00-0000</a>";
			} else {
				$prospect_mail = "<a href='#' onClick='return prospect_mail2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_mail)) . "</a>";
			}
			
			if ($prospect_facebook == NULL) {
				$prospect_facebook = "<a href='#' onClick='return prospect_facebook($id)' style='color: #fff;'>00-00-0000</a>";
			} else {
				$prospect_facebook = "<a href='#' onClick='return prospect_facebook2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_facebook)) . "</a>";
			}
			
			if ($prospect_instagram == NULL) {
				$prospect_instagram = "<a href='#' onClick='return prospect_instagram($id)' style='color: #fff;'>00-00-0000</a>";
			} else {
				$prospect_instagram = "<a href='#' onClick='return prospect_instagram2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_instagram)) . "</a>";
			}
			
			if ($prospect_call == NULL) {
				$prospect_call = "<a href='#' onClick='return prospect_call($id)' style='color: #fff;'>00-00-0000</a>";
			} else {
				$prospect_call = "<a href='#' onClick='return prospect_call2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_call)) . "</a>";
			}
			
			if ($prospect_visit == NULL) {
				$prospect_visit = "<a href='#' onClick='return prospect_visit($id)' style='color: #fff;'>00-00-0000</a>";
			} else {
				$prospect_visit = "<a href='#' onClick='return prospect_visit2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_visit)) . "</a>";
			}
			
			if ($prospect_demo == NULL) {
				$prospect_demo = "<a href='#' onClick='return prospect_demo($id)' style='color: #fff;'>00-00-0000</a>";
			} else {
				$prospect_demo = "<a href='#' onClick='return prospect_demo2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_demo)) . "</a>";
			}
			
			if ($prospect_sms == NULL) {
				$prospect_sms = "<a href='#' onClick='return prospect_sms($id)' style='color: #fff;'>00-00-0000</a>";
			} else {
				$prospect_sms = "<a href='#' onClick='return prospect_sms2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_sms)) . "</a>";
			}
			*/

			// Check for logins (trial start), feedback and contact attempts, only run queries if attemps are found!
			if (strpos($customerlist3, $number) !== false && $number != 0) {
				
				$query = "SELECT domain FROM db_access WHERE customer = '$number'";
				try
				{
					$result = $pdo->prepare("$query");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$domain = $row['domain'];	

				
				$query = "SELECT time FROM logins WHERE domain = '$domain' ORDER by time ASC";
				try
				{
					$resultsC = $pdo->prepare("$query");
					$resultsC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				$rowC = $resultsC->fetch();
					$trialstart = date("d-m-Y", strtotime($rowC['time']));
				
			} else {
				
				//$trialstart = "<span style='color: #fff;'>00-00-0000</span>";
				$trialstart = "";
				
			}

			if (strpos($customerlist2, $number) !== false && $number != 0) {
				
				$query = "SELECT time FROM prospect_feedback WHERE customer = '$number' ORDER by time DESC";
				try
				{
					$resultsC = $pdo2->prepare("$query");
					$resultsC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while ($rowC = $resultsC->fetch()) {
					
					$timestamp = date("d-m-Y", strtotime($rowC['time']));
					if (strpos($prospect_feedback, $timestamp) !== false && $number != 0) {
						
					} else {
						$prospect_feedback .= "$timestamp<br />";
					}
					
				}
				
			} else {
				
				$prospect_feedback = "";
				
			}
			

			
			if (strpos($customerlist, $number) !== false && $number != 0) {

				$query = "SELECT time FROM prospect_contact WHERE customer = '$number' AND type = '7' ORDER by time DESC";
				try
				{
					$resultsC = $pdo2->prepare("$query");
					$resultsC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while ($rowC = $resultsC->fetch()) {
					
					$timestamp = date("d-m-Y", strtotime($rowC['time']));
					$prospect_mail .= "$timestamp<br />";
					
				}
	
				$query = "SELECT time FROM prospect_contact WHERE customer = '$number' AND type = '10' ORDER by time DESC";
				try
				{
					$resultsC = $pdo2->prepare("$query");
					$resultsC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while ($rowC = $resultsC->fetch()) {
					
					$timestamp = date("d-m-Y", strtotime($rowC['time']));
					$prospect_instagram .= "$timestamp<br />";
					
				}
				
				$query = "SELECT time FROM prospect_contact WHERE customer = '$number' AND type = '11' ORDER by time DESC";
				try
				{
					$resultsC = $pdo2->prepare("$query");
					$resultsC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while ($rowC = $resultsC->fetch()) {
					
					$timestamp = date("d-m-Y", strtotime($rowC['time']));
					$prospect_facebook .= "$timestamp<br />";
					
				}
				
				$query = "SELECT time FROM prospect_contact WHERE customer = '$number' AND type = '1' ORDER by time DESC";
				try
				{
					$resultsC = $pdo2->prepare("$query");
					$resultsC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while ($rowC = $resultsC->fetch()) {
					
					$timestamp = date("d-m-Y", strtotime($rowC['time']));
					$prospect_call .= "$timestamp<br />";
					
				}
				
				$query = "SELECT time FROM prospect_contact WHERE customer = '$number' AND type = '8' ORDER by time DESC";
				try
				{
					$resultsC = $pdo2->prepare("$query");
					$resultsC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while ($rowC = $resultsC->fetch()) {
					
					$timestamp = date("d-m-Y", strtotime($rowC['time']));
					$prospect_sms .= "$timestamp<br />";
					
				}
				
				$query = "SELECT time FROM prospect_contact WHERE customer = '$number' AND type = '13' ORDER by time DESC";
				try
				{
					$resultsC = $pdo2->prepare("$query");
					$resultsC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while ($rowC = $resultsC->fetch()) {
					
					$timestamp = date("d-m-Y", strtotime($rowC['time']));
					$prospect_visited .= "$timestamp<br />";
					
				}
				
				// Check last contact
				$query = "SELECT time FROM prospect_contact WHERE customer = '$number' ORDER BY time DESC";
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
					$lastcontact = date("d-m-Y", strtotime($row['time']));
					if ($lastcontact == '01-01-1970') {
						//$lastcontact = "<span style='color: #fff;'>00-00-0000</span>";
						$lastcontact = "";
					}
					
			} else {
				
				//$lastcontact = "<span style='color: #fff;'>00-00-0000</span>";
				$lastcontact = "";
				$prospect_sms = "";
				$prospect_call = "";
				$prospect_facebook = "";
				$prospect_instagram = "";
				$prospect_mail = "";
				$prospect_visited = "";

			}
			
			
			
	echo <<<EOD
  	   <tr>
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$number</td>
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$registeredSince</td>
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$operator</td>
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$us_brandtext</td>
  	    <!--<td class='clickableRow' href='prospect.php?user_id=$id'>$launchdate</td>-->
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$shortName</td>
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$city</td>
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$country</td>
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$statusName</td>
  	    <td class='clickableRow' href='prospect.php?user_id=$id'>$organicFlag</td>
  	    <td class='clickableRow noExl' href='prospect.php?user_id=$id'>$email</td>
  	    <td class='clickableRow noExl' href='prospect.php?user_id=$id'>$phone</td>
  	    <td class='clickableRow noExl' href='prospect.php?user_id=$id'>$phone_sms</td>
  	    <td class='clickableRow noExl' href='prospect.php?user_id=$id'>$instagram</td>
  	    <td class='clickableRow noExl' href='prospect.php?user_id=$id'>$facebook</td>
  	    <td class='centered' href='prospect.php?user_id=$id'>$prospect_mail</td>
  	    <td class='centered' href='prospect.php?user_id=$id'>$prospect_facebook</td>
  	    <td class='centered' href='prospect.php?user_id=$id'>$prospect_instagram</td>
  	    <td class='centered' href='prospect.php?user_id=$id'>$prospect_call</td>
  	    <td class='centered' href='prospect.php?user_id=$id'>$prospect_sms</td>
  	    <td class='centered' href='prospect.php?user_id=$id'>$prospect_visited</td>
	    <td class='centered'><a href='uTil/change-responsible-prospect.php?clientid=$id&r=$prospect_responsible'>$responsible</a></td>
  	    <td class='' href='prospect.php?user_id=$id'>$lastcontact</td>
  	    <td class='' href='prospect.php?user_id=$id'>$prospect_feedback</td>
  	    <td class=''>$trialstart</td>
  	    <td class='centered'><a href='#' onClick='return add_task($id)'>$task</a></td>
  	    <td class='centered'><a href='#' onClick='return edit_followup($id)'>$followup $followupicon</a></td>
  	    <td class='noExl' href='prospect.php?user_id=$id'><center>$commentShow</center></td>
  	    <td class='centered'>
  	    <span style='display: inline-block; width: 80px !important;'>
  	     <a href='#' onClick='return edit_customer($id)'><img src='images/orange-edit.png' title='Edit prospect' /></a>
  	     &nbsp;<a href='#' onClick='return new_contact_attempt($id)'><img src='images/orange-contact.png' title='New contact attempt' /></a>
  	     &nbsp;<a href='#' onClick='return new_feedback($id)'><img src='images/orange-feedback.png' title='New feedback' /></a>
  	    </span>
  	    </td>
  	   </tr>
EOD;
	  
  }
  
  	if (isset($_GET['pos'])) {
	  
	  $scrollLeft = "var left = $(document).outerWidth() - $(window).width();
$('body, html').scrollLeft(left);";

	}

  
?>


	 </tbody>
	 </table>
	 
<script>

function edit_customer(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'edit-customer.php?user_id='+id+'&prospect=yes&pos='+curpos+'&set=null&prospectlist=yes';
		return false;

}
function new_contact_attempt(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'new-contact-attempt.php?user_id='+id+'&prospect=yes&pos='+curpos+'&set=null&prospectlist=yes';
		return false;

}

function new_feedback(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'new-feedback.php?user_id='+id+'&prospect=yes&pos='+curpos+'&set=null&prospectlist=yes';
		return false;

}

function edit_followup(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect-followup.php?clientid='+id+'&prospect=yes&pos='+curpos+'&set=null&prospectlist=yes';
		return false;

}

function add_task(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/add-prospect-task.php?clientid='+id+'&prospect=yes&pos='+curpos+'&set=null&prospectlist=yes';
		return false;

}









function prospect_mail(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_mail.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_mail2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_mail.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_facebook(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_facebook.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_facebook2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_facebook.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_instagram(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_instagram.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_instagram2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_instagram.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_call(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_call.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_call2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_call.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_visit(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_visit.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_visit2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_visit.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_demo(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_demo.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_demo2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_demo.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_sms(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_sms.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_sms2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_sms.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}


document.documentElement.scrollTop = document.body.scrollTop = <?php echo $pos; ?>;

<?php echo $scrollLeft; ?>

</script>

<?php  displayFooter();