<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$ticketid = $_GET['ticketid'];
	$feedbackid = $ticketid;
	
	if (isset($_GET['newCat'])) {
		
		$reason = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['category'])));
		
		$query = "UPDATE feedback SET reason = '$reason' WHERE id = '$ticketid'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
	}
	
	// Query to look up feedback
	$selectFeedback= "SELECT * FROM feedback WHERE id = '$ticketid'";
	try
	{
		$result = $pdo3->prepare("$selectFeedback");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

		
	pageStart("Ticket details", NULL, $memberScript, "pmembership", NULL, "Ticket details", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center>
	<a href='feedback.php' class='cta1'>&laquo; Tickets &laquo;</a>
<br />
<?php
	  	$i = 0;
	  	
	  	 $main_site = str_replace("Nefos/", "", $siteroot);
	  	 
			$feedback = $result->fetch();
			
				$status = $feedback['status'];
				$number = $feedback['number'];
				$club = $feedback['club'];
				$reason = $feedback['reason'];
				$issue = $feedback['issue'];
				$message = $feedback['message'];
				$operator_id = $feedback['operator_id'];
				$operator_name = $feedback['operator_name'];
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
						$attach_arr[$i][$x] = "<a target='_blank' href='https://ccsnubev2.com/v6/".$attachRow['file_name']."' download><img src='images/paperclip.png' width='20' /></a>&nbsp;"; 
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
				
				// Status 3 = Marked as 'solved' by client
				// Status 4 = Marked as 'solved' automatically after 7 days
				
?>

<div id="mainbox-no-width" >
 <div class='boxcontent'>
<span style='font-size: 22px; color: #f2b149; font-weight: 600; text-transform: capitalize;'><?php echo $number . ' ' . $club; ?></span><br />
<span style='font-size: 18px; color: #00a48c; font-weight: 600; text-transform: capitalize;'><?php echo $operator_name; ?></span><br />
<span style='font-size: 15px; color: #777; font-weight: 600; text-transform: capitalize;'><?php echo $time; ?>
</span>
</div>
</div>
<br />
<div id="mainbox-no-width" style='text-align: left; min-width: 900px;'>
 <div id="mainboxheader">
  <span id="catTrigger"><?php echo $reason; ?></span>
<span id='newCategory' style='display: none;'>
 <br />
 <form id="registerForm" action="?newCat&ticketid=<?php echo $ticketid; ?>" method="POST">
  <select name='category'>
    <option value="<?php echo $reason; ?>"><?php echo $reason; ?></option>
	<option value="Bug report">Bug report</option>
	<option value="Testimonial">Testimonial</option>
	<option value="Just saying 'hi'">Just saying 'hi'</option>
	<option value="Interface issue">Interface issue</option>
	<option value="How to">How to</option>

  </select>
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>
</span>

</div>
<div class='boxcontent'>

<!--
<span style='font-size: 15px; color: #f2b149; font-weight: 600; text-transform: capitalize;'>&raquo; {$feedback['reason']}</span><br /><br />
-->
<strong><?php echo $issue; ?></strong><br />
<?php echo $message; ?>

<br />
<br />
<?php

	if ($attachments != '') {
		
		echo "Attachments:<br /><br /> $attachments";
		
	}
	
?>
<br />
<br />
<br />
<a href='add-ticket-comment.php?ticketid=<?php echo $ticketid; ?>&number=<?php echo $number; ?>' class='addComment'><img src='images/plus-new2.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Add reply</a><br /><br /><br />
<a href='uTil/close-ticket.php?id=<?php echo $ticketid; ?>&number=<?php echo $number; ?>' class='addComment'><img src='images/checkmark-new.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Close ticket</a><br /><br /><br />

<table class='default' style='width: 100%;'>
 <thead>
  <tr>
   <th></th>
   <th><strong>Name</strong></th>
   <th><strong>Time</strong></th>
   <th><strong>Reply</strong></th>
  </tr>
 </thead>
 <tbody>
 

<?php

		$query = "SELECT * FROM feedback_comments WHERE feedbackid = '$ticketid' ORDER BY time DESC";
		try
		{
			$results = $pdo3->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
			
			$time = date("d-m-Y H:i", strtotime($row['time']));
			$operator = $row['operator'];
			$type = $row['type'];
			$comment = $row['comment'];
			$feedback_comment_id = $row['id'];
			
			if ($type == 1) {
				
				$logo = "<img src='images/logo-square.png' width='20' />";
				
				$query = "SELECT first_name, last_name FROM users WHERE user_id = '$operator'";
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
					$opName = $row['first_name'] . " " . $row['last_name'];
					
			} else {
				
				$logo = "";
				
				// Look up domain
				$findDomain = "SELECT domain, db_pwd FROM db_access WHERE customer = '$number'";
				try
				{
					$result = $pdo->prepare("$findDomain");
					$result->execute();
					$data2 = $result->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
				$row = $data2[0];
				$domain = $row['domain'];
				$db_pwd = $row['db_pwd'];
				$db_name = "ccs_" . $domain;
				$db_user = $db_name . "u";
				
				try	{
			 		$pdoC = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
			 		$pdoC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			 		$pdoC->exec('SET NAMES "utf8"');
				}
				catch (PDOException $e)	{
			  		// $output = 'Unable to connect to the database server: ' . $e->getMessage();
			
			 		// echo $output;
			 		$_SESSION['errorMessage'] = "Customer does not have a database (meaning they haven't been launched - or they have been sunset!)";
			 		$nodb = 'true';
				}
	
				
				$query = "SELECT first_name, last_name FROM users WHERE user_id = '$operator'";
				try
				{
					$result = $pdoC->prepare("$query");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$opName = $row['first_name'] . " " . $row['last_name'];
					
			}
			
				$getAttachments = "SELECT file_name from feedback_comment_attachments where feedback_id = ".$feedback_comment_id;
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

				$attachments = '';
				unset($attach_arr[$i]);
				
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

			
			echo <<<EOD
			
  <tr>
   <td>$logo</td>
   <td>$opName</td>
   <td>$time</td>
   <td>$comment<br />$attachments</td>
  </tr>
			
EOD;
			
		}
			
			
			
?>

 </tbody>
</table>
</div>
</div>
<script>
	$("#catTrigger").click(function () {
		$("#newCategory").toggle();
	});
</script>
<?php displayFooter();