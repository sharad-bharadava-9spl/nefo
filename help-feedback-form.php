<?php  $maximum_files = 5;  ?>
<div id="mainbox-no-width" style="display: inline-block; width: 50%; margin-right: 3%;">
	 <div id="mainboxheader"><?php echo $lang['send-feedback']; ?></div>
	 <form id="feedback_form" action="help-feedback-post.php" method="POST" enctype="multipart/form-data">
		 <div class="boxcontent">
		  <table>
		   <tr>
		    <td><label class="smallgreen text-right"><?php echo $lang['reason']; ?></label></td>
		    <td><select name="reason" required="" class="defaultinput" style='width: 366px;'>
							<option value=""><?php echo $lang['select-reason']; ?></option>
							<option value="Bug report"><?php echo $lang['bug-report']; ?></option>
							<option value="Testimonial"><?php echo $lang['testimonial']; ?></option>
							<option value="Just saying 'hi'"><?php echo $lang['saying-hi']; ?></option>
							<option value="Interface issue"><?php echo $lang['interface-issue']; ?></option>
							<option value="How to"><?php echo $lang['how-to']; ?></option>
							<option value="Suggestion"><?php if ($_SESSION['lang'] == 'en') { echo 'Suggestion'; } else { echo 'Sugerencia'; } ?></option>
						</select>
			</td>
		   </tr>
		   <tr>
		    <td><label class="smallgreen text-right"><?php echo $lang['subject']; ?></label></td>
		    <td><input type="text" name="issue" class="defaultinput" placeholder=""  required="" style='width: 350px;'></td>
		   </tr>
		   <tr>
		    <td style='vertical-align: top;'><br /><label class="smallgreen text-right"><?php echo $lang['message']; ?></label></td>
		    <td style='padding-left: 11px;'><textarea name="message" class="defaultinput" placeholder="" id="contacttext" required="" style='width: 368px;'></textarea></td>
		   </tr>
		   <tr>
		    <td><br /><label class="smallgreen text-right"><?php echo $lang['attachments']; ?>&nbsp;</label></td>
		    <td><br /><input type="file" name="attach_files[]" style='border: 0; margin-left: 10px;' multiple><br />
						<span style='font-size: 13px; margin-left: 10px;'>(<?php echo $lang['you-can-select-maximum']; ?> <?php echo $maximum_files; ?> <?php echo $lang['files-to-upload']; ?>)</span>
						<input type="hidden" name="max_files" value="<?php echo $maximum_files; ?>">
</td>
		   </tr>
		   <tr>
		    <td colspan='2'><center><button type="submit" name="feedback_sub" class='cta1'><strong><?php echo $lang['submit']; ?></strong></button></center></td>
		   </tr>
		  </table>

	</form>
</div>
</div>

<div id="mainbox-no-width" style="display: inline-block; width: 46%; vertical-align: top; min-height: 530px;">
	 <div id="mainboxheader"><?php echo $lang['my-feedback']; ?></div>
		 <div class="boxcontent">
		 
<table class='default' style='width: 100%; font-family: osregular, Tahoma;'>
 <thead>
  <tr>
   <th><strong><?php echo $lang['created']; ?></strong></th>
   <th><strong><?php echo $lang['subject']; ?></strong></th>
   <th><strong><?php echo $lang['status']; ?></strong></th>
   <th><strong><?php echo $lang['last-reply']; ?></strong></th>
   <th></th>
   <th></th>
  </tr>
 </thead>
 <tbody>
		 
<?php

	// Query to look up feedback
	$query = "SELECT customer FROM db_access WHERE domain = '{$_SESSION['domain']}'";
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
		$number = $row['customer'];	
		
	if ($_SESSION['domain'] == 'thecoffeeshop') {
		$selectFeedback= "SELECT id, created_at, reason, issue, status FROM feedback WHERE number = '$number' AND id > 794 AND deleted = 0 AND reason <> 'Suggestion' ORDER BY id DESC";
	} else {
		$selectFeedback= "SELECT id, created_at, reason, issue, status FROM feedback WHERE number = '$number' AND id > 395 AND deleted = 0 AND reason <> 'Suggestion' ORDER BY id DESC";
	}
	try
	{
		$results = $pdo2->prepare("$selectFeedback");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($row = $results->fetch()) {
		
		$created_at = date("d-m-Y H:i", strtotime($row['created_at']));
		$reason = $row['reason'];
		$issue = $row['issue'];
		$status = $row['status'];
		$ticketid = $row['id'];
		
		if ($status == 0 || $status == 2) {
			$status = $lang['waiting-ccs'];
		} else if ($status == 1) {
			$status = "<style>.flashing { animation: blink 0.5s; animation-iteration-count: infinite; }</style><span class='negative flashing'><strong>{$lang['action-required']}</strong></span>";
		} else if ($status == 3) {
			$status = "<span class='positive'>{$lang['closed']}</span>";
		} else if ($status == 4) {
			$status = "<span class='positive'>{$lang['closed-automatically']}</span>";
		}
						
		// Look up last reply date
		$query = "SELECT time FROM feedback_comments WHERE feedbackid = '$ticketid' ORDER BY time DESC LIMIT 1";
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
			$lastComment = date("d-m-Y H:i", strtotime($row['time']));
			
		if ($lastComment == '01-01-1970 01:00') {
			$lastComment = "<span class='white'>00-00-0000</span>";
		}
		
		echo <<<EOD
		
<tr>
 <td class='clickableRow' href='ticket.php?ticketid=$ticketid'>$created_at</td>
 <td class='clickableRow' href='ticket.php?ticketid=$ticketid'>$issue</td>
 <td class='clickableRow' href='ticket.php?ticketid=$ticketid'>$status</td>
 <td class='clickableRow' href='ticket.php?ticketid=$ticketid'>$lastComment</td>
 <td class='clickableRow' href='ticket.php?ticketid=$ticketid'><a href='ticket.php?ticketid=$ticketid'><img src='images/magglass-new.png' width='15' /></a></td>
 <td style='text-align: center;'><a href='javascript:delete_ticket($ticketid)'><img src='images/delete.png' height='15' /></a></td>
</tr>

EOD;
		
	}

?>

</tbody>
</table>

		</div>
</div>

<script src="https://cdn.tiny.cloud/1/9pxfemefuncr8kvf2f5nm34xwdg8su9zxhktrj66loa5mexa/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    $(document).ready(function() {
	    	    

      tinymce.init({
        selector: '#contacttext',
        height :'200',
        menubar: false
    });

  }); // end ready
</script>
