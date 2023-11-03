<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();

	// Query to look up requests
	$selectRequests = "SELECT * FROM change_requests order by id DESC";
		try
		{
			$results = $pdo3->prepare("$selectRequests");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
	
	$deleteSaleScript = <<<EOD
	    $(document).ready(function() {

		    $( "#dead_datepicker" ).datepicker({
				dateFormat: "dd-mm-yy"
		    });
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Requests",
			    filename: "Requests" //do not include extension
		
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

		function approve_request(value, id){
			window.location = "change-requests-actions.php?requestid=" + id + "&approve="+value;
		}
EOD;
	pageStart("Request Changes", NULL, $deleteSaleScript, "psales", "Request Changes", "Request Changes", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center>
 <a href="add-change-request.php" class="cta1">Request change</a> <br />
<a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<br />
<br />
<table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Time</th>
	    <th>Operator</th>
	    <th>Topic</th>
	    <th>Description</th>
	    <th>Priority</th>
	    <th>Approved</th>
	    <th>Deadline</th>
	    <th>Completed</th>
	    <th>Comment</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	while ($request = $results->fetch()) {
			
				$time = date("d-m-Y H:i", strtotime($request['created_at']));
				$id = $request['id'];
				$operator = $request['user_id'];
				$topic = $request['topic'];
				$description = $request['description'];
				$priority = $request['priority'];
				$approved = $request['approved'];
				$deadline_date = date("d-m-Y", strtotime($request['deadline']));
				$completed = $request['completed'];
				$comment = $request['comment'];
				
				if ($comment != '') {
					$comment_par = $comment;
					$commentRead = "
					                <img src='images/comments.png' id='comment$id' /><div id='helpBox$id' class='helpBox'>$comment</div>
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
					$comment_par = 'null';
					$commentRead = "";
					
				}			

				if ($description != '') {
					
					$descriptionRead = "
					                <img src='images/comments.png' id='description$id' /><div id='descBox$id' class='helpBox'>$description</div>
					                <script>
					                  	$('#description$id').on({
									 		'mouseover' : function() {
											 	$('#descBox$id').css('display', 'block');
									  		},
									  		'mouseout' : function() {
											 	$('#descBox$id').css('display', 'none');
										  	}
									  	});
									</script>
					                ";
					
				} else {
					
					$descriptionRead = "";
					
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

				if($approved == 0){
					$approvedText  = "<a href='javascript:approve_request(1, $id);'><img src='images/awaiting.png' style='height: 15px;'></a>";
				}else{
					$approvedText  = "<a href='javascript:approve_request(0, $id);'><img src='images/complete.png' style='height: 15px;'></a>";
				}			

				if($completed == 0){
					$completedText  = "<a href='javascript:complete_request(1, $id);'><img src='images/awaiting.png' style='height: 15px;'></a>";
				}else{
					$completedText  = "<a href='javascript:complete_request(0, $id);'><img src='images/complete.png' style='height: 15px;'></a>";
				}			

				if($request['deadline'] == ''){
					$deadlineText  = "<a href='javascript:deadline_request($id);'><img src='images/awaiting.png' style='height: 15px;'></a>";
				}else{
					$deadlineText  = $deadline_date;
				}	
						
			
				echo "
	  	   <tr>
	  	    <td>$time</td>
	  	    <td>$first_name</td>
	  	    <td>$topic</td>
	  	    <td class='centered'><span class='relativeitem'>$descriptionRead</span></td>
	  	    <td>$priority</td>
	  	    <td class='centered'>$approvedText</td>
	  	    <td class='centered'>$deadlineText</td>
	  	    <td class='centered'>$completedText</td>
	  	    <td class='centered'><span class='relativeitem'>$commentRead</span></td>
	  	   </tr>";
	  
  	}
?>

	 </tbody>
</table>
<div  class="actionbox-npr" id = "dialog-deadline" title = "Deadline">
	
	<div class='boxcomtemt'>
		<p>Enter Deadline Date</p><br>
		<input type="text" id="dead_datepicker" name="deadline_date" autocomplete="nope" class="sixDigit defaultinput" placeholder="Enter date" />
		<input type="hidden" id="dead_id">
		<button class='cta1' id="dead_submit">Ok</button>
		
	</div>
</div>

<div  class="actionbox-npr" id = "dialog-complete" title = "Comment">
	
	<div class='boxcomtemt'>
		<p>Enter comment</p><br>
		<textarea id="comment_content" style="height: 120px;" class="defaultinput"></textarea>
		<input type="hidden" id="comment_id">
		<input type="hidden" id="complete_value">
		<button class='cta1' id="comment_submit">Ok</button>
		
	</div>
</div>
	 
	 
<?php



displayFooter(); ?>

<script type="text/javascript">
	$( "#dialog-deadline" ).dialog({
	    autoOpen: false, 
	    hide: "puff",
	    show : "slide",
	 });	

	$( "#dialog-complete" ).dialog({
	    autoOpen: false, 
	    hide: "puff",
	    show : "slide",
	 });

	function deadline_request(id){
		$( "#dialog-deadline" ).dialog( "open" );
		$("#dead_id").val(id);
	}	

	function complete_request(value, id){
		$( "#dialog-complete" ).dialog( "open" );
		$("#comment_id").val(id);
		$("#complete_value").val(value);
	}

	$("#dead_submit").click(function(){
		var dead_date = $("#dead_datepicker").val();
		var dead_id = $("#dead_id").val();
		if(dead_date == ''){
			alert("Please enter date!");
			return false;
		}
		window.location = "change-requests-actions.php?deadlineDate="+dead_date+"&dead_id="+dead_id; 
	});	

	$("#comment_submit").click(function(){
		var comment_content = $("#comment_content").val();
		var comment_id = $("#comment_id").val();
		var complete_status = $("#complete_value").val();
		if(comment_content == ''){
			alert("Please enter comment!");
			return false;
		}
		window.location = "change-requests-actions.php?comment="+comment_content+"&comment_id="+comment_id+"&complete_status="+complete_status; 
	});
</script>
