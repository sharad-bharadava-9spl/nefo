<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// STATUS:
	// 1 - waiting for client
	// 2 - waiting for CCS
	// 3 - closed by client
	// 4 - closed automatically
	// 5 - closed by CCS
	
	
	if(isset($_GET['did'])){
		// delete department
		$id= $_GET['did'];
		$getAttachments = "SELECT file_name from feedback_attachments where feedback_id = ".$id;
				try
				{
					$delete_attach_results = $pdo3->prepare("$getAttachments");
					$delete_attach_results->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching attachment: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$attachCount = $delete_attach_results->rowCount();
				if($attachCount > 0){
					while($deattachRow = $delete_attach_results->fetch()){
						
						unlink("../".$deattachRow['file_name']); 
					 		
					}
					$deleteFeedbackAttach = "DELETE FROM feedback_attachments where feedback_id = $id";
					try
					{
						$results = $pdo3->prepare("$deleteFeedbackAttach");
						$results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				}
		$deleteFeedback = "DELETE FROM feedback where id = $id";
			try
			{
				$results = $pdo3->prepare("$deleteFeedback");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$_SESSION['successMessage'] = "Feeddback deleted successfully!";
			header("location: feedback.php");
			exit();
	}
	
	if (isset($_GET['userid'])) {
		
		$clubid = $_GET['userid'];
		
		$query = "SELECT number FROM customers WHERE id = '$clubid'";
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
			$number = $row['number'];
			
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
			
		$limitVar = "AND club = '$domain' ";
		
		
	}
	
	
	// Query to look up users
	 $selectFeedback= "SELECT * FROM feedback WHERE 1 AND status > 2 AND id > 395 $limitVar order by id DESC";
		try
		{
			$results = $pdo3->prepare("$selectFeedback");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	$query = "SELECT COUNT(id) FROM feedback WHERE status > 2 AND id > 395";
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
		$statusAll = $row['COUNT(id)'];	
		
	$query = "SELECT AVG(rating) FROM feedback WHERE status = 3 AND id > 395 AND rating > 0";
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
		$rating = $row['AVG(rating)'];

			
			
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Feedback",
			    filename: "Feedback" //do not include extension
		
			  });
		
			});
		  
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); 

		});
      function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this feedback ?')){
      	 	 window.location = "feedback.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Resolved tickets", NULL, $memberScript, "pmembership", NULL, "Resolved tickets", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
	<a href='help-section.php' class='cta1'>&laquo; Help center &laquo;</a>
	<a href='feedback.php' class='cta1'>Open tickets</a><br />

</center>
<center>
<div id='productoverview'>
 <table>
  <tr>
   <td>Closed tickets</td>
   <td class='right'><strong><?php echo $statusAll; ?></strong></td>
  </tr>
  <tr>
   <td>Avg. rating</td>
   <td class='right'><strong><?php echo number_format($rating,1); ?></strong></td>
  </tr>
 </table>
</div>
</center>
<br />
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});" ><img src="images/excel-new.png" style='margin-top: -40px;'/></a>
<br />

	 <table class='defaultalternate left' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Rating</th>
	    <th>Created</th>
	    <th>Closed at</th>
	    <th>Closed by</th>
	    <th>#</th>
	    <th>Club</th>
	    <th>Reason</th>
	    <th style='width: 200px !important;'>Issue</th>
	    <th>Replies</th>
	    <th>Description</th>
	    <th>Attachments</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  	$i =0;
	  	
	  	 $main_site = str_replace("Nefos/", "", $siteroot);
		while ($feedback = $results->fetch()) {
			
				$feedbackid= $feedback['id'];
				$rating = $feedback['rating'];
				$closedby = $feedback['closedby'];
				$number = $feedback['number'];
				$statusRaw = $feedback['status'];
				$closedat = date("d-m-Y H:i", strtotime($feedback['closedat']));
				
				if ($closedat == "01-01-1970 01:00") {
					$closedat = "<span style='color: #fff;'>00-00-0000</span>";
				}
				
				if ($rating > 3) {
					$ratingT = "<span style='color: green;'>$rating</span>";
				} else if ($rating == 0) {
					$ratingT = "<span></span>";
				} else if ($rating < 3) {
					$ratingT = "<span style='color: red;'>$rating</span>";
				} else {
					$ratingT = "<span>$rating</span>";
				}
				
				$time = date('d-m-Y H:i', strtotime($feedback['created_at']));
				if ($feedback['message'] != '') {
		
						$commentRead = "
						                <span class='relativeitem'><img src='images/description.png' id='comment$feedbackid' /><div id='helpBox$feedbackid' class='helpBox'><strong>Message:</strong><br />{$feedback['message']}</div>
						                <script>
						                  	$('#comment$feedbackid').on({
										 		'mouseover' : function() {
												 	$('#helpBox$feedbackid').css('display', 'block');
										  		},
										  		'mouseout' : function() {
												 	$('#helpBox$feedbackid').css('display', 'none');
											  	}
										  	});
										</script></span>
						                ";
						
					} else {
						
						$commentRead = "";
						
					}


				$getAttachments = "SELECT file_name from feedback_attachments where feedback_id = ".$feedbackid;
				try
				{
					$attach_results = $pdo3->prepare("$getAttachments");
					$attach_results->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching attachment: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$attachCount = $attach_results->rowCount();
				if($attachCount > 0){
					$x =0;
					while($attachRow = $attach_results->fetch()){
						$attach_no = $x+1;
						$attach_arr[$i][$x] = "<a target='_blank' href='https://ccsnubev2.com/v6/".$attachRow['file_name']."' download><img src='images/paperclip.png' width='15' /></a>&nbsp;"; 
					 	$x++;	
					}
					$attachments = implode(" ", $attach_arr[$i]);
				}else{
					$attachments = '';
				}
				
				if ($feedback['status'] == 0) {
					$status = "<span class='negative'>New</span>";
				} else if ($feedback['status'] == 1) {
					$status = "Waiting on client";
				} else if ($feedback['status'] == 2) {
					$status = "<span class='negative'>Action needed</span>";
				} else {
					$status = "<img src='images/checkmark-new.png' width='18' />";
				}
				
			$selectRows = "SELECT COUNT(id) FROM feedback_comments WHERE feedbackid = '$feedbackid'";
			$rowCount = $pdo3->query("$selectRows")->fetchColumn();
			
			if ($statusRaw == 5) {
				
				$closedbyTxt = 'CCS';
				
			} else if ($statusRaw == 4) {
				
				$closedbyTxt = 'Automatically';
				
			} else if ($closedby == 0) {
				
				$closedbyTxt = 'N/A';
				
			} else {
				
				$query = "SELECT db_pwd, customer, warning, domain FROM db_access WHERE customer = '$number'";
				try
				{
					$result = $pdo->prepare("$query");
					$result->execute();
					$data = $result->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
				if ($data) {
		
					$row = $data[0];
						$db_pwd = $row['db_pwd'];
						$customer = $row['customer'];
						$warning = $row['warning'];
						$domain = $row['domain'];
			
					$db_name = "ccs_" . $domain;
					$db_user = $db_name . "u";
			
					try	{
				 		$pdo6 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
				 		$pdo6->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				 		$pdo6->exec('SET NAMES "utf8"');
					}
					catch (PDOException $e)	{
				  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
				
				 		echo $output;
				 		exit();
					}
					
					$query = "SELECT memberno, first_name FROM users WHERE user_id = '$closedby'";
					try
					{
						$result = $pdo6->prepare("$query");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$memberno = $row['memberno'];
						$first_name = $row['first_name'];
						
					$closedbyTxt = "#$memberno $first_name";
						
				} else {
					
					$closedbyTxt = "N/A";
					
				}			
				
			}


	echo sprintf("
  	    <tr>
  	    <td class='centered'><strong>%s</strong></td>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td style='text-transform: capitalize;'>%s</td>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td style='width: 300px !important;'>%s</td>
  	    <td class='centered'>%d</td>
  	    <td class='centered'>%s</td>
		<td class='centered'><a href='ticket.php?ticketid=$feedbackid'><img src='images/magglass-new.png' width='15' /></a></td></tr>",
  	 $ratingT, date('d-m-Y H:i', strtotime($feedback['created_at'])), $closedat, $closedbyTxt, $feedback['number'], $feedback['club'], $feedback['reason'], $feedback['issue'], $rowCount, $commentRead, $attachments, $feedback['id']
  	);

	 $i++; 
  }
				
?>

	 </tbody>
	 </table>
<?php  displayFooter();