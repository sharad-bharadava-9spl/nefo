<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
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
			
		$limitVar = "WHERE club = '$domain' ";
	}
	
	
	// Query to look up users
	 $selectFeedback= "SELECT * FROM feedback $limitVar order by id DESC LIMIT 1";
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
	
	$query = "SELECT COUNT(id) FROM feedback WHERE status < 3";
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
			
	$query = "SELECT COUNT(id) FROM feedback WHERE status = 0";
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
		$status0 = $row['COUNT(id)'];	
			
	$query = "SELECT COUNT(id) FROM feedback WHERE status = 1";
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
		$status1 = $row['COUNT(id)'];	
			
	$query = "SELECT COUNT(id) FROM feedback WHERE status = 2";
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
		$status2 = $row['COUNT(id)'];	
			
			
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


	pageStart("Open tickets", NULL, $memberScript, "pmembership", NULL, "Open tickets", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
	<a href='help-center.php' class='cta1'>&laquo; Help center &laquo;</a>
	<a href='feedback-solved.php' class='cta1'>Resolved tickets</a><br />

</center>
<center>
<div id='productoverview'>
 <table>
  <tr>
   <td>Open tickets</td>
   <td class='right'><strong><?php echo $statusAll; ?></strong></td>
  </tr>
  <tr>
   <td>New</td>
   <td class='right'><strong class='negative'><?php echo $status0; ?></strong></td>
  </tr>
  <tr>
   <td>Action needed</td>
   <td class='right'><strong class='negative'><?php echo $status2; ?></strong></td>
  </tr>
  <tr>
   <td>Waiting on client</td>
   <td class='right'><strong><?php echo $status1; ?></strong></td>
  </tr>
 </table>
</div>
</center>
<br />
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});" ><img src="images/excel-new.png" style='margin-top: -40px;'/></a>
<br />

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Status</th>
	    <th>Created</th>
	    <th>#</th>
	    <th>Club</th>
	    <th>Reason</th>
	    <th style='width: 200px !important;'>Issue</th>
	    <th>Description</th>
	    <th>Attachments</th>
	    <th>Action</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  	$i =0;
	  	
	  	 $main_site = str_replace("Nefos/", "", $siteroot);
		while ($feedback = $results->fetch()) {
				$feedbackid= $feedback['id'];
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
						$attach_arr[$i][$x] = "<a href='https://ccsnubev2.com/v6/".$attachRow['file_name']."' download><img src='images/paperclip.png' width='15' /></a>&nbsp;"; 
					 	$x++;	
					}
					$attachments = implode(" ", $attach_arr[$i]);
				}else{
					$attachments = '';
				}
				
				if ($feedback['status'] == 0) {
					$status = "<span class='negative'>New</span>";
				} else if ($feedback['status'] == 1) {
					$status = "Waiting for client";
				} else if ($feedback['status'] == 2) {
					$status = "<span class='negative'>Action needed</span>";
				} else {
					$status = "<img src='images/checkmark-new.png' width='18' />";
				}
				//style="display: none;
			$commentShow = <<<EOD
			
<a href='#' id='showComment$feedbackid'><img src='images/magglass-new.png' width='15' /></a>
<div id="commentBox$feedbackid" class='commentBox' ">
<a href='#' id='hideComment$feedbackid' class="closeComment"><img src="images/delete.png" width='22' /></a>

<span style='font-size: 20px; color: #00a48c; font-weight: 600; text-transform: capitalize;'>{$feedback['club']}<br />{$feedback['operator_name']}</span><br />
<span style='font-size: 16px; color: #777; font-weight: 600; text-transform: capitalize;'>$time</span>
<br />
<br />
<!--
<span style='font-size: 15px; color: #f2b149; font-weight: 600; text-transform: capitalize;'>&raquo; {$feedback['reason']}</span><br /><br />
-->
<span style='font-size: 14px;'>

<strong>{$feedback['issue']}</strong><br />
{$feedback['message']}

</span>
<br />
<br />
$attachments
<br />
<br />
<br />
<a href='add-cutoff-comment.php?client=$feedbackid&period=$period' class='addComment'><img src='images/plus-new.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Add comment</a><br /><br /><br />
$comments

</div>
<script>
$("#showComment$feedbackid").click(function (e) {
	e.preventDefault();
	$("#commentBox$feedbackid").css("display", "block");
});
$("#hideComment$feedbackid").click(function (e) {
	e.preventDefault();
	$("#commentBox$feedbackid").css("display", "none");
});
</script>
EOD;

					

	echo sprintf("
  	    <tr>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td style='text-transform: capitalize;'>%s</td>
  	    <td>%s</td>
  	    <td style='width: 300px !important;'>%s</td>
  	    <td class='centered'>%s</td>
  	    <td class='centered'>%s</td>
		<td class='centered'>$commentShow&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete feedback'></a></td></tr>",
  	 $status, date('d-m-Y H:i', strtotime($feedback['created_at'])), $feedback['number'], $feedback['club'], $feedback['reason'], $feedback['issue'], $commentRead, $attachments, $feedback['id']
  	);

	 $i++; 
  }
				
?>

	 </tbody>
	 </table>
<?php  displayFooter();