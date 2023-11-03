<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	function mb_unserialize($string) {
	    $string = preg_replace_callback(
	        '!s:(\d+):"(.*?)";!s',
	        function ($matches) {
	            if ( isset( $matches[2] ) )
	                return 's:'.strlen($matches[2]).':"'.$matches[2].'";';
	        },
	        $string
	    );
	    return unserialize($string);
	}
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['time_stamp'])) {
		$id = $_POST['id'];
	    $time_stamp = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['time_stamp']))); 
		$customer_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer_name'])));
		$customer_number = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer_number'])));
		$contact_id = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['contact_name'])));
		$choose_call_type = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['choose_call_type'])));
		$affiliation_id = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['affiliation_val'])));
		$call_method = $_POST['call_method'];
		$operator_id = $_SESSION['user_id'];
		$post_issue_arr = $_POST['issue'];
	     if(empty($affiliation_id) || $affiliation_id == ''){
	     	$affiliation_id = 0;
	     }
        
	      for($k1=1; $k1<= count($post_issue_arr); $k1++){
	      		$post_issue_arr1[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['issue'][$k1])));
	      		$post_issueid_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['issue_id'][$k1])));
	      		$post_timer_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['timer'][$k1])));
	      		$post_type_manual_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['type_manual'][$k1])));
	      		$post_department_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['department'][$k1])));
	      		$post_department_cat_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['department_cat'][$k1])));
	      		$post_comment_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'][$k1])));
	      		$post_next_desc_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['next_desc'][$k1])));
	      		$post_deadline_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['deadline'][$k1])));
	      		$post_slected_users_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['slected_users'][$k1])));
	      		$post_priority_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['priority'][$k1])));
	      		$post_task_created_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['task_created'][$k1])));
	      		$post_task_url_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['task_url'][$k1])));
	      		
	      }

		    $issue = serialize($post_issue_arr1);
			$issue_id = serialize($post_issueid_arr);
			$timer = serialize($post_timer_arr);
			$type_manual = serialize($post_type_manual_arr);
			$department = serialize($post_department_arr);
			$department_cat = serialize($post_department_cat_arr);
		    $comment = serialize($post_comment_arr); 
			$next_desc = serialize($post_next_desc_arr);
			$deadline = serialize($post_deadline_arr);
			$slected_users = serialize($post_slected_users_arr);
			$priority = serialize($post_priority_arr);
			$task_created = serialize($post_task_created_arr);
			$task_url = serialize($post_task_url_arr); 

/*		$issue = serialize($_POST['issue']);
		$issue_id = serialize($_POST['issue_id']);
		$timer = serialize($_POST['timer']);
		$type_manual = serialize($_POST['type_manual']);
		$department = serialize($_POST['department']);
		$department_cat = serialize($_POST['department_cat']);
		$comment = serialize($_POST['comment']);
		$next_desc = serialize($_POST['next_desc']);
		$deadline = serialize($_POST['deadline']);
		$slected_users = serialize($_POST['slected_users']);
		$priority = serialize($_POST['priority']);
		$task_created = serialize($_POST['task_created']);*/
       // insert customer if not exist
		if($choose_call_type == 0){
			$checkCustomer = "SELECT COUNT(*) FROM customers WHERE number = $customer_number";
						try
						{
							$customerresult = $pdo3->prepare("$checkCustomer");
							$customerresult->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					$customerRow = $customerresult->fetch();
					$chkcustNum = $customerRow['COUNT(*)'];
					if($chkcustNum <= 0){
						$insertCustomer = sprintf("INSERT into customers set longName ='%s', number = '%s'", $customer_name, $customer_number);
						try
						{
							$result = $pdo3->prepare("$insertCustomer")->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					}
			}
		if(empty($contact_id) || $contact_id == ''){
			$contact_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['contact_name2'])));
			$customer_contact2 = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer_contact'])));
			$customer_email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer_email'])));
			$contact_role = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['contact_role'])));
			if($choose_call_type == 1){
				$customer_number = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['contact_club'])));
			}
			  $insertContact = sprintf("INSERT into contacts set name ='%s', telephone = '%s', email ='%s', role = '%s', customer = '%s'", $contact_name, $customer_contact2, $customer_email, $contact_role, $customer_number);  

					try
					{
						$result = $pdo3->prepare("$insertContact")->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$contact_id =  $pdo3->lastInsertId();
		}else{
			$customer_contact = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer_contact'])));
			$customer_email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer_email'])));
			 $updateContact = sprintf("UPDATE contacts set telephone = '%s', email ='%s' WHERE id='%d'", $customer_contact, $customer_email, $contact_id);
					try
					{
						$result = $pdo3->prepare("$updateContact")->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
		}
		// get customer name and number when call type set to 1
		if($choose_call_type == 1){
			$selectCustomer = "SELECT a.number, a.shortName from customers a, contacts b WHERE b.id = '$contact_id' AND a.number=b.customer";
					try
					{
						$customer_result = $pdo3->prepare("$selectCustomer");
						$customer_result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$customerRow = $customer_result->fetch();
						$customer_number = $customerRow['number'];
						$customer_name = $customerRow['shortName'];

		}
 		$issue_ids = mb_unserialize($issue_id);
 		$issue_names = mb_unserialize($issue);

 		for($kk = 1; $kk <= count($issue_names); $kk++){
	 		$issue_box_arr[$issue_names[$kk]] =  $issue_ids[$kk]; 
 		}
 		$ix =1;
 		foreach($issue_box_arr as $issue_box_name => $issue_box_id){
			if(empty($issue_box_id) || $issue_box_id == ''){
				$insertissue = sprintf("INSERT into issues set issue ='%s'", $issue_box_name);
						try
						{
							$result = $pdo3->prepare("$insertissue")->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					 	$issue_ids[$ix] =  $pdo3->lastInsertId();
			}
			$ix++;
		}
        $issue_id = serialize($issue_ids);
		// Query to update user - 28 arguments
		  $updateUser = "UPDATE calls SET created = '$time_stamp', operator_id = '$operator_id', call_type = '$choose_call_type', affiliation = '$affiliation_id', customerName = '$customer_name', customerNumber = '$customer_number', customerContact = '$contact_id', issue = '$issue_id', duration = '$timer', save_duration = '$type_manual', department = '$department', department_cat = '$department_cat', comment = '$comment', nextaction_desc = '$next_desc', nextaction_end = '$deadline',nextaction_users = '$slected_users', nextaction_priority = '$priority', task_created ='$task_created', call_method = '$call_method', task_url = '$task_url'  WHERE id = $id"; 
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}	

		// On success: redirect.
		$_SESSION['successMessage'] = "Call log updated succesfully!";
		header("Location: calls.php");
		exit();
	}
	
	/***** FORM SUBMIT END *****/
	
	$id = $_GET['callid'];
	
	$validationScript = <<<EOD
    $(document).ready(function() {
		$( "#datepicker" ).datetimepicker({
	  	   dateFormat: "yy-mm-dd"
	  	});	  
	  	$( ".deadpicker" ).datetimepicker({
	  	   dateFormat: "yy-mm-dd"
	  	});    	    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  },
			  task_url:{
			  	url: true,
			  	required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
			} else if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

  //  look up for users
	$selectUsers = "SELECT user_id, first_name, last_name FROM users order by first_name ASC";

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

		while($userRow = $results->fetch()){
			$user_arr[$userRow['user_id']] = $userRow['first_name']." ".$userRow['last_name'];
		}

   //  query to look up issues
	$selectIssues = "SELECT id,issue FROM issues order by id ASC"; 
		try
		{
			$result_issue = $pdo3->prepare("$selectIssues");
			$result_issue->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	while($rowIssue = $result_issue->fetch()){
		$issue_array[$rowIssue['id']] =  $rowIssue['issue'];
	}

	// get departmets and categories

	 $getDepartment = "SELECT a.id AS department_id, b.id, a.name, b.category FROM departments a, department_cat b WHERE a.id = b.department_id";

		try
		{
			$results = $pdo3->prepare("$getDepartment");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
        $department_array = [];
        $i = 0;
		while($depRow = $results->fetch()){
			$department_array[$i]['cat_id'] = $depRow['id'];
			$department_array[$i]['name'] =  $depRow['name'];
			$department_array[$i]['department_id'] =  $depRow['department_id'];
			$department_array[$i]['category'] = $depRow['category'];
			$department_names[$depRow['department_id']] = $depRow['name'];
			$i++;
		}

	// Query to look up calls
	$selectCalls = "SELECT * FROM calls WHERE id = $id";
	try
	{
		$results = $pdo3->prepare("$selectCalls");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $results->fetch();
		$created = $row['created'];
		$call_type = $row['call_type'];
		$affiliation = $row['affiliation'];
		$customerNumber = $row['customerNumber'];
		$customerName = $row['customerName'];
		$customerContact = $row['customerContact'];
		$issue = $row['issue'];
		$duration = $row['duration'];
		$save_duration = $row['save_duration'];
		$department = $row['department'];
		$department_cat = $row['department_cat'];
		$comment = $row['comment'];
		$nextaction_desc = $row['nextaction_desc'];
		$nextaction_end = $row['nextaction_end'];
		$nextaction_users = $row['nextaction_users'];
		$nextaction_priority = $row['nextaction_priority'];
		$task_created = $row['task_created'];
		$call_method = $row['call_method'];
		$task_url = $row['task_url'];


		$issue_arr = mb_unserialize($issue);
		$duraion_arr = mb_unserialize($duration);
		$save_duration_arr = mb_unserialize($save_duration);
		$department_arr = mb_unserialize($department);
		$department_cat_arr = mb_unserialize($department_cat);
		$comment_arr = mb_unserialize($comment);
		$nextaction_desc_arr = mb_unserialize($nextaction_desc);
		$nextaction_end_arr = mb_unserialize($nextaction_end);
		$nextaction_users_arr = mb_unserialize($nextaction_users);
		$nextaction_priority_arr = mb_unserialize($nextaction_priority);
		$task_created_arr = mb_unserialize($task_created);
		$task_url_arr = mb_unserialize($task_url);

	   
		
		/*$issue_parent_arr['issue'] = $issue_arr;
		$issue_parent_arr['duration'] = $duraion_arr;
		$issue_parent_arr['save_duration'] = $save_duration_arr;
		$issue_parent_arr['department'] = $department_arr;
		$issue_parent_arr['department_cat'] = $department_cat_arr;
		$issue_parent_arr['comment'] = $comment_arr;
		$issue_parent_arr['nextaction_desc'] = $nextaction_desc_arr;
		$issue_parent_arr['nextaction_end'] = $nextaction_end_arr;
		$issue_parent_arr['nextaction_users'] = $nextaction_users_arr;
		$issue_parent_arr['nextaction_priority'] = $nextaction_priority_arr;
		$issue_parent_arr['task_created'] = $task_created_arr;*/
      
       for($i=1; $i <= count($issue_arr); $i++){

       		$issue_parent_arr[$i]['issue'] =  $issue_arr[$i];
       		$issue_parent_arr[$i]['duration'] =  $duraion_arr[$i];
       		$issue_parent_arr[$i]['save_duration'] =  $save_duration_arr[$i];
       		$issue_parent_arr[$i]['department'] =  $department_arr[$i];
       		$issue_parent_arr[$i]['department_cat'] =  $department_cat_arr[$i];
       		$issue_parent_arr[$i]['comment'] =  $comment_arr[$i];
       		$issue_parent_arr[$i]['nextaction_desc'] =  $nextaction_desc_arr[$i];
       		$issue_parent_arr[$i]['nextaction_end'] =  $nextaction_end_arr[$i];
       		$issue_parent_arr[$i]['nextaction_users'] =  $nextaction_users_arr[$i];
       		$issue_parent_arr[$i]['nextaction_priority'] =  $nextaction_priority_arr[$i];
       		$issue_parent_arr[$i]['task_created'] =  $task_created_arr[$i];
       		$issue_parent_arr[$i]['task_url'] =  $task_url_arr[$i];
       }



// Get issue

	pageStart("Edit Call", NULL, $validationScript, "pprofile", NULL, "Edit Call", $_SESSION['successMessage'], $_SESSION['errorMessage']);


		// Query to look up users
	 $selectUsers = "SELECT number,shortName FROM customers order by id ASC"; 
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
	while($row = $results->fetch()){
		$customer_arr[$row['number']] = $row['shortName'];
	}
  
		// select affiliate
		$selectAffiliate = "SELECT * from affiliations order by name ASC";
			try
			{
				$affiliate_result = $pdo3->prepare("$selectAffiliate");
				$affiliate_result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

?>

   <form id="registerForm" action="" method="POST">

    <input type="hidden" name="id" value="<?php echo $id; ?>" />
    
 <div class="overview">
	<table class='profileTable' style='text-align: left; margin: 0;'>
		 <tr>
		  <td><strong>Timestamp</strong></td>
		  <td><input type="text" name="time_stamp" id="datepicker" value="<?php echo $created; ?>" required /></td>
		 </tr>
		 <tr>
		 	<td></td>
		 	<td>
		 		<input type="radio" name="choose_call_type" value="0" <?php if($call_type == 0){ echo "checked"; } ?>>Club
		 		<input type="radio" name="choose_call_type" value="1" <?php if($call_type == 1){ echo "checked"; } ?>>Affiliation
		 	</td>
		 </tr>
		 <tr>
		  <td><strong>Call type</strong></td>
		 	<td>
		 		<input type="radio" name="call_method" id="call_method1" value="1" <?php if ($call_method == 1) { echo "checked"; } ?> required><label for="call_method1">Normal</label><br />
		 		<input type="radio" name="call_method" id="call_method2" value="2" <?php if ($call_method == 2) { echo "checked"; } ?>><label for="call_method2">Skype</label><br />
		 		<input type="radio" name="call_method" id="call_method3" value="3" <?php if ($call_method == 3) { echo "checked"; } ?>><label for="call_method3">Whatsapp</label><br />
		 		<input type="radio" name="call_method" id="call_method4" value="4" <?php if ($call_method == 4) { echo "checked"; } ?>><label for="call_method4">Signal</label><br />
		 		<input type="radio" name="call_method" id="call_method5" value="5" <?php if ($call_method == 5) { echo "checked"; } ?>><label for="call_method5">Telegram</label><br />
		 		<input type="radio" name="call_method" id="call_method6" value="6" <?php if ($call_method == 6) { echo "checked"; } ?>><label for="call_method6">Wickr</label><br />
		 	</td>
		 </tr>
		 <tr>
		  <td><strong>Customer</strong></td>
		  <td>
		  	<?php  if($call_type == 0){  ?>
			   <input type="text" name="customer_name" id="cust_name" value="<?php echo $customerName ?>" placeholder="Customer Name" required />
			   <input type="text" name="customer_number" id="cust_num" class="fourDigit" value="<?php echo $customerNumber ?>" placeholder="Customer Number"  required/>
			
			  	<select name="affiliation_val" id="affiliation_drop" style="display: none;" required>
			   			<option value="">Select Affiliation</option>
			   	 	<?php   while($affiliate_val = $affiliate_result->fetch()){  ?>
			   				<option value="<?php echo $affiliate_val['id'] ?>"  <?php if($affiliation == $affiliate_val['id']){  echo "selected"; } ?> ><?php echo $affiliate_val['name'] ?></option>
			   		<?php }?>
			   </select>
			<?php }else{ ?>
				<input type="text" name="customer_name" id="cust_name" style="display: none;" value="<?php echo $customerName ?>" placeholder="Customer Name" required />
			   <input type="text" name="customer_number" id="cust_num" style="display: none;" class="fourDigit" value="<?php echo $customerNumber ?>" placeholder="Customer Number"  required/>
			
			  	<select name="affiliation_val" id="affiliation_drop" required>
			   			<option value="">Select Affiliation</option>
			   	 	<?php   while($affiliate_val = $affiliate_result->fetch()){  ?>
			   				<option value="<?php echo $affiliate_val['id'] ?>"  <?php if($affiliation == $affiliate_val['id']){  echo "selected"; } ?> ><?php echo $affiliate_val['name'] ?></option>
			   		<?php }?>
			   </select>
			<?php } ?>
		  </td>
		 </tr>
		 <tr>
		  <td><strong>Customer Contact</strong></td>
		  <td>
		  	Add New Contact Details<input type="checkbox" name="check_contact" id="check_contact" value="1"><br>
		  	<?php

		  	   if($call_type == 0){
		  	   	   $customer_limit = "WHERE customer = ".$customerNumber;
				  		// get contact details
				  	    $selectContact = "SELECT id, name from contacts WHERE customer = ".$customerNumber;
							try
							{
								$results = $pdo3->prepare("$selectContact");
								$results->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}

							$contact_results = $results->fetchAll();


				  	?>
				  	<select name="contact_name" id="select_contact" required="">
				  		<option value="">Select Contact Person</option>
				  		<?php foreach ($contact_results as $conresult) {
				  			?>
				  					<option value="<?php echo $conresult['id'] ?>"  <?php if($conresult['id'] == $customerContact) { echo 'selected';  } ?>><?php echo $conresult['name'] ?></option>
				  			<?php	}  ?>
				  	</select>
				  <?php }else{ 
				  			$selectClub  = "SELECT number from customers WHERE affiliation=".$affiliation;
										try
										{
											$aff_results = $pdo3->prepare("$selectClub");
											$aff_results->execute();
										}
										catch (PDOException $e)
										{
												$error = 'Error fetching user: ' . $e->getMessage();
												echo $error;
												exit();
										}
										while($affRow = $aff_results->fetch()){
											$customer_numbers[] = $affRow['number']; 
										}
										$cust_nums = implode(",", $customer_numbers);
										if(empty($cust_nums)){
											$cust_nums = -1;
										}
									 $getContacts = "SELECT a.id, a.name, b.shortName from contacts a,customers b WHERE a.customer IN ($cust_nums) AND a.customer = b.number";

									try
									{
										$contact_results = $pdo3->prepare("$getContacts");
										$contact_results->execute();
									}
									catch (PDOException $e)
									{
											$error = 'Error fetching user: ' . $e->getMessage();
											echo $error;
											exit();
									}
									
									?>
								<select name="contact_name" id="select_contact" required="">
							  		<option value="">Select Contact Person</option>
							  		<?php while($conresult = $contact_results->fetch()){
							  			?>
							  					<option value="<?php echo $conresult['id'] ?>"  <?php if($conresult['id'] == $customerContact) { echo 'selected';  } ?>><?php echo $conresult['name'] ?> (<?php echo $conresult['shortName'] ?>)</option>
							  			<?php	}  ?>
							  	</select>
				 <?php  } 

				 			// get contact data
							$getContact = "SELECT name, telephone, email, role from contacts where id = ".$customerContact;
							try
							{
								$results = $pdo3->prepare("$getContact");
								$results->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
							$getContactRow = $results->fetch();
							$contact_name = $getContactRow['name'];
							$contact_telephone = $getContactRow['telephone'];
							$contact_email = $getContactRow['email'];
							$contact_role = $getContactRow['role'];

				 ?>
		  	<select name="contact_club" id="club_name" required="" style="display: none;">
		  		<option value="">Select Customer/club</option>
		  	</select>
		  	<input type="text" name="contact_name2" id="contact_new" style="display: none;" required="" placeholder="Contact Name">
		  	<input type="text" name="customer_contact" id="contact_num" value="<?php echo $contact_telephone; ?>" placeholder="Contact Number" readonly required="">
		  	<input type="email" name="customer_email" id="contact_email" value="<?php echo $contact_email; ?>" placeholder="Contact Email" readonly >
		  	<input type="text" name="contact_role" id="crole" style="display: none;" placeholder="Role">
		  </td>
		 </tr>
		</table>
		<br>
		<div class="issue_div">
			<?php
				$no = 1;
			    $issue_arr = mb_unserialize($issue);
			      // main issue loop
			      foreach($issue_parent_arr as $is_parent){

						 $selectIssue = "SELECT issue from issues WHERE id=".$is_parent['issue'];
							try
							{
								$result_issue = $pdo3->prepare("$selectIssue");
								$result_issue->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching issue: ' . $e->getMessage();
									echo $error;
									exit();
							}
							$rowIssue = $result_issue->fetch();
							$issue_name = $rowIssue['issue'];
			?>
				<table class='profileTable' style='text-align: left; margin: 0;'>
					<h4>Issue <?php echo $no; ?></h4> 
					<tr>
						<td>
						  <strong>Duration:</strong>
						 </td>
						 <td> 
						   <input type="number" name="timer[<?php echo $no; ?>]" id="clock<?php echo $no; ?>" class="twoDigit" value="<?php echo $is_parent['duration'] ?>" required="">(Seconds)
						   <input type="hidden" name="type_manual[<?php echo $no; ?>]" id='save_namual<?php echo $no; ?>' value="<?php echo $is_parent['save_duration'] ?>">
						</td>					
					</tr>
				 <tr>
				  <td><strong>Issue</strong></td>
				  <td><input type="text" name="issue[<?php echo $no; ?>]" id="call_issue<?php echo $no; ?>" class="issue_text" placeholder="Enter Issue" value="<?php echo $issue_name ?>" required /></td>
				    <input type="hidden" name="issue_id[<?php echo $no; ?>]" value="<?php echo $is_parent['issue'] ?>" id="issue_id<?php echo $no; ?>">
				 </tr>
				 <tr>
				  <td><strong>Department</strong></td>
				  <?php  
				  		// get selected departments
				  		 $selectDepartment = "SELECT id,name from departments order by id ASC";
						  	try
							{
								$depresults = $pdo3->prepare("$selectDepartment");
								$depresults->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
				   ?>
				  <td>
				  	<select name="department[<?php echo $no; ?>]" class="department_cls" id="dept_name<?php echo $no; ?>" required="">
				  		 <option value="">Select Department</option>
				  		 <?php  while($depopt = $depresults->fetch()){ ?>
				  		 	<option value="<?php echo $depopt['id'] ?>" <?php if($depopt['id'] == $is_parent['department']) {  echo "selected";  } ?>><?php echo $depopt['name']; ?></option>
				  		 <?php } ?>	
				  	</select>
				  </td>
				 </tr>
				 <tr>
				  <td><strong>Department Category</strong></td>
				  	<?php  
				  		// get selected departments
				  		 $selectDepartmentCat = "SELECT * from department_cat where department_id = '".$is_parent['department']."' order by id ASC";
						  	try
							{
								$depcatresults = $pdo3->prepare("$selectDepartmentCat");
								$depcatresults->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
				   ?>
				  <td>
				  	<select name="department_cat[<?php echo $no; ?>]" class="department_cat" id="dept_cat<?php echo $no; ?>" required="">
				  		<option value="">Select Category</option>
				  		<?php  while($depcatopt = $depcatresults->fetch()){  ?>
				  			<option value="<?php echo $depcatopt['id'] ?>"  <?php if($depcatopt['id'] == $is_parent['department_cat']) {  echo "selected";  } ?>><?php echo $depcatopt['category'] ?></option>
				  		<?php } ?>
				  	</select>
				  	
				 </tr>
				 <tr>
				  <td><strong>Comment</strong></td>
				  <td><textarea name="comment[<?php echo $no; ?>]"><?php echo $is_parent['comment']; ?></textarea></td>
				 </tr>
				 <tr>
				  <td><strong>Next Action</strong></td>
				  <?php
				  		if(!empty($is_parent['nextaction_users']) && $is_parent['nextaction_users'] !=''){
				  			$users_selected = $is_parent['nextaction_users'];
				  		}else{
				  			$users_selected = -1;
				  		}
				  		// selectuser names
				       //  look up for users
						$selectUserNames = "SELECT user_id, first_name, last_name FROM users where user_id IN ($users_selected) order by first_name ASC";

						try
							{
								$user_results = $pdo3->prepare("$selectUserNames");
								$user_results->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
							$x =0;
							while($usernameRow = $user_results->fetch()){
								$username_arr[$x] = $usernameRow['first_name']." ".$usernameRow['last_name'];
								$x++;
							}
							$unames = implode(",", $username_arr);
				  ?>
				  <td>
					  	<textarea name="next_desc[<?php echo $no; ?>]" placeholder="description"><?php echo $is_parent['nextaction_desc']; ?></textarea>
					  	<input type="text" name="deadline[<?php echo $no; ?>]" class="deadpicker" id="deadline<?php echo $no; ?>" placeholder="Deadline" value="<?php echo $is_parent['nextaction_end']; ?>"><br>
					  	<input type="text" name="colleague[<?php echo $no; ?>]" id="userselect<?php echo $no; ?>"  class="multi_users"  value="<?php echo $unames ?>" placeholder="select users">
					  	<input type="hidden" name="slected_users[<?php echo $no; ?>]" id="user_ids<?php echo $no; ?>" value="<?php echo $is_parent['nextaction_users']; ?>">
					  	<select name="priority[<?php echo $no; ?>]">
					  		<?php 
					  			$priority_check = $is_parent['nextaction_priority'];
					  		 ?>
					  		<option value="">Select Priority</option>
					  		<option value="low" <?php if($priority_check == 'low'){ echo "selected"; } ?>>Low</option>
					  		<option value="medium" <?php if($priority_check == 'medium'){ echo "selected"; } ?>>Medium</option>
					  		<option value="high" <?php if($priority_check == 'high'){ echo "selected"; } ?>>High</option>
					  	</select>
				  </td>		  
				 </tr>		 
				 <tr>
				  <td><strong>Task Created ?</strong></td>
				  <td>
		  			<?php 
			  			$task_check = $is_parent['task_created'];
			  		 ?>
				  	<select name="task_created[<?php echo $no; ?>]" id="ctask<?php echo $no; ?>" class="task_change" required="">
				  		<option value="">Select Option</option>
				  		<option value="Yes" <?php if($task_check == 'Yes'){  echo "selected";  } ?>>Yes</option>
				  		<option value="No" <?php if($task_check == 'No'){  echo "selected";  } ?>>No</option>
				  	</select>
				  		<input type="text" name="task_url[<?php echo $no; ?>]"  id="task_url<?php echo $no; ?>" placeholder="Enter URL"  <?php if($task_check != 'Yes'){ ?> style='display:none;' <?php } ?> value = "<?php echo $is_parent['task_url'] ?>" required="">
				  </td>
				 </tr>
				</table>
			<?php 

				$no++;
		} ?>
		</div>
		
	 <br />
	<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</div>


<?php displayFooter(); ?>
<?php  $cust_num_arr = array_flip($customer_arr);


foreach($customer_arr as $cust_key => $cust_val){
	$customer_bind_arr[] = strval($cust_key)."--".$cust_val;
}


 ?>
 <script>
 	   // select on change
   $(document).on("change",".task_change",function(){
   	    var change_val = $(this).val();
   		var change_id = $(this).attr('id');
   		var task_id = change_id.split("ctask");
   		if(change_val == "Yes"){
   			$("#task_url"+task_id[1]).show();
   		}else{
   			$("#task_url"+task_id[1]).val('').hide();
   		}
   });
  	var customer_arr = <?php echo json_encode($customer_arr); ?>;
  	var customer_bind_arr = <?php echo json_encode($customer_bind_arr); ?>;
  	var customer_name =[];
  	for(var i in customer_arr){
  		customer_name.push(customer_arr[i]);
  	}
    $("input[name='choose_call_type']").change(function(){
 		var call_type = $(this).val();
 		var check_contact = $("input[name='check_contact']:checked").val();
 		if(call_type == 0){
 			$("#affiliation_drop").val('').hide();
 			$("#cust_name").val('').show();
 			$("#cust_num").val('').show();
 			if(check_contact == 1){
 				$("#club_name").html("<option value=''>Select Customer/club</option>").hide();
 			}
 		}else{
 			$("#affiliation_drop").val('').show();
 			$("#cust_name").val('').hide();
 			$("#cust_num").val('').hide();
 			if(check_contact == 1){
 				$("#club_name").html("<option value=''>Select Customer/club</option>").show();
 			}
 		}
 	});
    $( "#cust_name" ).autocomplete({
       source: customer_name,
      	autoFocus: true,
       select: function( event, ui ) {
       	  var selected_value = ui.item.value;
       	  console.log(selected_value);
       	  Object.keys(customer_arr).forEach(function(k){
			   // console.log(k + ' - ' + customer_arr[k]);
			    if($.trim(selected_value) == $.trim(customer_arr[k])){
			    	$("#cust_num").val(k);
			    	    $.ajax({
					      type:"post",
					      url:"getContact.php?cust_num="+k,
					      datatype:"text",
					      success:function(data)
					      {
							$("#select_contact").html(data);
					      }
					    });
			    }
			});
       	}
    });

    // get affilaite contact list
    $("#affiliation_drop").change(function(){
    	var this_drop = $(this).val();
    	var checked_val = $("input[name='check_contact']:checked").val();
    	if(checked_val  == 1){
    		if(this_drop != ''){
	    		 $.ajax({
			      type:"post",
			      url:"getAffiContact.php?aff_id="+this_drop+"&checked=new",
			      datatype:"text",
			      success:function(data)
			      {
					$("#club_name").html(data);
			      }
			    });
    		}else{
    			$("#club_name").html("<option vaue=''>Select customer/club</option>");
    		}
    	}else{
    		if(this_drop != ''){
	    		 $.ajax({
			      type:"post",
			      url:"getAffiContact.php?aff_id="+this_drop,
			      datatype:"text",
			      success:function(data)
			      {
					$("#select_contact").html(data);
			      }
			    });
    		}else{
    			$("#select_contact").html("<option vaue=''>Select contact</option>");
    		}
    	}
		
    });

    $( "#cust_num" ).autocomplete({
       	source: customer_bind_arr,
      	autoFocus: true,
       	select: function( event, ui ) {
       	  var selected_value = ui.item.value;
       	 
       	  var selected_val_arr = selected_value.split("--");
       	 
       	 
       	  Object.keys(customer_arr).forEach(function(k){
			   // console.log(k + ' - ' + customer_arr[k]);
			    if(selected_val_arr[1] == customer_arr[k]){
			    	$("#cust_name").val(customer_arr[k]);
			    	    $.ajax({
					      type:"post",
					      url:"getContact.php?cust_num="+k,
					      datatype:"text",
					      success:function(data)
					      {
							$("#select_contact").html(data);
					      }
					    });
			    }
			});

       	},
       	change: function( event, ui ) {
       	  var selected_value = ui.item.value;
       	 
       	  var selected_val_arr = selected_value.split("--");
       	 
       	 $("#cust_num").val(selected_val_arr[0]);
       	  Object.keys(customer_arr).forEach(function(k){
			   // console.log(k + ' - ' + customer_arr[k]);
			    if(selected_val_arr[1] == customer_arr[k]){
			    	$("#cust_name").val(customer_arr[k]);
			    	    $.ajax({
					      type:"post",
					      url:"getContact.php?cust_num="+k,
					      datatype:"text",
					      success:function(data)
					      {
							$("#select_contact").html(data);
					      }
					    });
			    }
			});

       	}
    });
    var issue_array = <?php echo json_encode($issue_array); ?>;
	   var issue_name =[];
	  	for(var i in issue_array){
	  		issue_name.push(issue_array[i]);
	  	}
	if(issue_array != null){  	
	    $( ".issue_text" ).autocomplete({
	       source: issue_name,
	      	autoFocus: true,
	       select: function( event, ui ) {
	       	  var this_id = $(event.target).attr('id');
	       	  var thisIdval = this_id.split('call_issue');
	       	  var selected_value = ui.item.value;
	       	  Object.keys(issue_array).forEach(function(k){
				    console.log(k + ' - ' + issue_array[k]);
				    if(selected_value == issue_array[k])
				    	$("#issue_id"+thisIdval[1]).val(k);
				});
	       	},
	       	change: function( event, ui){
	       		var this_id = $(event.target).attr('id');
	       	    var thisIdval = this_id.split('call_issue');
	       		if(ui.item == null){
	       			$("#issue_id"+thisIdval[1]).val('');
	       		}
	       	}
	    });
	}
  // change options dynamically


 
  var department_array = <?php echo json_encode($department_array); ?>;

	$(document).on("change",".department_cls",function(){
		var options = "<option value=''>Select category</option>";
		var dept_id = $(this).attr('id');
		var dept_id_val = $(this).val();
		for(var i in department_array){
			var deptID = department_array[i].department_id;
			if(dept_id_val == deptID){
				options += "<option value="+department_array[i].cat_id+">"+department_array[i].category+"</option>";
			}
		}
		$(this).parent().parent().next().find('.department_cat').html(options);
	});

	 // user multiselect autocomplete  
	 var user_arr = <?php echo json_encode($user_arr)  ?>;
	
	 	 var user_name =[];
	  	for(var i in user_arr){
	  		user_name.push(user_arr[i]);
	  	}
	
	$( function() {
		
		function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}

		$( ".multi_users" )
			// don't navigate away from the field on tab when selecting an item
			.on( "keydown", function( event ) {
				if ( event.keyCode === $.ui.keyCode.TAB &&
						$( this ).autocomplete( "instance" ).menu.active ) {
					event.preventDefault();
				}
			})
			.autocomplete({
				minLength: 0,
				source: function( request, response ) {
					// delegate back to autocomplete, but extract the last term
					response( $.ui.autocomplete.filter(
						user_name, extractLast( request.term ) ) );
				},
				focus: function(event, ui) {
					// prevent value inserted on focus
					return false;
				},
				select: function( event, ui ) {
					var terms = split( this.value );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
				  var this_id = $(event.target).attr('id');
		       	  var thisIdval = this_id.split('userselect');
					var user_ids = [];	
					Object.keys(user_arr).forEach(function(k){
						for(var i in terms){
							if(terms[i] != '' && terms[i] == user_arr[k]){
								user_ids.push(k);
							}
						}
					});
					
					$("#user_ids"+thisIdval[1]).val(user_ids.join());
					return false;
				},
				change: function( event, ui ) {
					var terms = split( this.value );
					console.log(terms);
					// remove the current input
					terms.pop();
					
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
					var this_id = $(event.target).attr('id');
			       	  var thisIdval = this_id.split('user_ids');
					
					var user_ids = [];	
					Object.keys(user_arr).forEach(function(k){
						for(var i in terms){
							if(terms[i] != '' && terms[i] == user_arr[k]){
								user_ids.push(k);
							}
						}
					});
					$("#user_ids"+thisIdval[1]).val(user_ids.join());
					return false;
				}
			});
	} );
	// get name and emaild of customer
	$("#select_contact").change(function(){
		var this_id = $(this).val();
		if(this_id != ''){
			$.ajax({
				type: "POST",
				url: "getContactDetails.php",
				data: {"id": this_id},
				datatype: 'json',
				success: function(result){
					$("#contact_num").val(result.contact_number);
					$("#contact_email").val(result.contact_email);
				}

			});
		}
	});

 $('#check_contact').on('click',function () {
        if ($(this).is(':checked')) {
           $("#select_contact").val('').hide();
           $("#contact_new").show();
           $("#crole").show();
           $("#contact_num").attr('readonly', false);
           $("#contact_email").attr('readonly', false);
           var selected_call = $("input[name='choose_call_type']:checked").val();
           if(selected_call == 1){
           	  $("#club_name").show();
           	  var affliate = $("#affiliation_drop").val();
           	  if(affliate != ''){
		            $.ajax({
				      type:"post",
				      url:"getAffiContact.php?aff_id="+affliate+"&checked=new",
				      datatype:"text",
				      success:function(data)
				      {
						$("#club_name").html(data);
				      }
				    });
	        	}else{
	        		$("#club_name").html("<option value=''>Select Customer/Club</option>");
	        	}
           }else{
           		$("#club_name").val('').hide();
           }
        } else {
          $("#select_contact").show();
           $("#contact_new").hide();
            $("#crole").hide();
            $("#club_name").hide();
            $("#contact_num").attr('readonly', true);
            $("#contact_email").attr('readonly', true);
        }
    });

  </script>
