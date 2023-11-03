<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
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
	// Query to look up users
	 $selectFeedback= "SELECT * FROM feedback order by id DESC";
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


	pageStart("Feedback", NULL, $memberScript, "pmembership", NULL, "Feedback", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<center><a href='help-section.php' class='cta'>Help Center</a></center>

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
	    <th>Club</th>
	    <th>Operator Name</th>
	    <th>Reason</th>
	    <th>Issue</th>
	    <th>Message</th>
	    <th>Attachments</th>
	    <th>Created</th>
	    <th>Action</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  	$i =0;
	  	
	  	 $main_site = str_replace("Nefos/", "", $siteroot);
		while ($feedback = $results->fetch()) {
				$feedbackid= $feedback['id'];
				if ($feedback['message'] != '') {
		
						$commentRead = "
						                <img src='images/description.png' id='comment$feedbackid' /><div id='helpBox$feedbackid' class='helpBox'><strong>Message:</strong><br />{$feedback['message']}</div>
						                <script>
						                  	$('#comment$feedbackid').on({
										 		'mouseover' : function() {
												 	$('#helpBox$feedbackid').css('display', 'block');
										  		},
										  		'mouseout' : function() {
												 	$('#helpBox$feedbackid').css('display', 'none');
											  	}
										  	});
										</script>
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
						$attach_arr[$i][$x] = "<a href='https://ccsnubev2.com/v6/".$attachRow['file_name']."' download>Attachment $attach_no</a>"; 
					 	$x++;	
					}
					$attachments = implode("<br>", $attach_arr[$i]);
				}else{
					$attachments = '';
				}
					

	echo sprintf("
  	    <tr>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td class='relative left'>%s</td>
  	    <td>%s</td>
  	    <td>%s</td>
		<td><a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete feedback'></a></td></tr>",
  	 $feedback['club'], $feedback['operator_name'], $feedback['reason'], $feedback['issue'], $commentRead, $attachments, $feedback['created_at'], $feedback['id']
  	);

	 $i++; 
  }
				
?>

	 </tbody>
	 </table>
<?php  displayFooter();