<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	//require "../vendor/autoload.php";
	require_once '../PHPMailerAutoload.php';
	$accessLevel = '3';
	$current_useremail = $_SESSION['username'];
	$current_username = $_SESSION['first_name'];
	//ini_set("display_errors", 'on');
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
	function sendEmail($mail, $to, $body, $subject) {
	    //Enable SMTP debugging
	    // 0 = off (for production use)
	    // 1 = client messages
	    // 2 = client and server messages
	    $mail->SMTPDebug = 0;
	    //Ask for HTML-friendly debug output
	    $mail->Debugoutput = 'html';
	    //Set the hostname of the mail server
	    $mail->Host = 'mail.cannabisclub.systems';
	    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
	    $mail->Port = 465;
	    //Set the encryption system to use - ssl (deprecated) or tls
	    $mail->SMTPSecure = 'ssl';
	    //Whether to use SMTP authentication
	    $mail->SMTPAuth = true;
	    //Username to use for SMTP authentication - use full email address for gmail
	    $mail->Username = "info@cannabisclub.systems";
	    //Password to use for SMTP authentication
	    $mail->Password = "Insjormafon9191";
	    //Set who the message is to be sent from
	    $mail->setFrom('info@cannabisclub.systems', 'CCSNube');
	    //Set who the message is to be sent to
	    $mail->addAddress($to, $to);
	    //Set the subject line
	    $mail->Subject = $subject;
	    //Read an HTML message body from an external file, convert referenced images to embedded,
	    //convert HTML into a basic plain-text alternative body
	    $mail->isHTML(true);
	    $mail->Body = $body;
	    //Replace the plain text body with one created manually
	    $mail->AltBody = 'This is a plain-text message body';
	    $sucess = $mail->send();
	    // //send the message, check for errors
		  /*  if (!$sucess) {
		    echo "Mailer Error: " . $mail -> ErrorInfo;
		    die;
		    } else {
		    echo "Message sent!";
		    die;
		    }*/
	}
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['save_call'])) {
		
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
	      		$post_colleague_arr[$k1] = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['colleague'][$k1])));
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
		$colleague = serialize($post_slected_users_arr);
		$colleague2 = serialize($post_colleague_arr);
		$priority = serialize($post_priority_arr);
		$task_created = serialize($post_task_created_arr);
	    $task_url = serialize($post_task_url_arr);  

		
		  //  look up for us echo "<pre>";
		$recipients_arr  = array();
	     $colleagues = unserialize($colleague);
	    
	     $i = 1;
	     foreach($colleagues as $colleague_val){
				if(!empty($colleague_val) || $colleague_val != ''){
					$selectUsers = "SELECT * FROM users WHERE user_id IN ($colleague_val)";
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
							$recipients_arr[$i][$userRow['email']] = $userRow['first_name']." ".$userRow['last_name'];
							
						}
						
				}
				$i++;
		}
		
		
		/*foreach ($recipients_arr as $recipient_val) {
			foreach ($recipient_val as $key => $value) {
				 $recipients[$key]  = $value;
			}
		}*/
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
		 $insertUser = sprintf("INSERT INTO calls (created, operator_id, call_type, affiliation, customerNumber, customerName, customerContact, issue, duration, save_duration, department, department_cat, comment, nextaction_desc, nextaction_end, nextaction_users, nextaction_priority, task_created, call_method, task_url) VALUES ('%s', '%d','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')",
		
					$time_stamp,
					$operator_id,
					$choose_call_type,
					$affiliation_id,
					$customer_number,
					$customer_name,
					$contact_id,
					$issue_id,
					$timer,
					$type_manual,
					$department,
					$department_cat,
					$comment,
					$next_desc,
					$deadline,
					$colleague,
					$priority,
					$task_created,
					$call_method,
					$task_url
					);   
		try
		{
			$result = $pdo3->prepare("$insertUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}	
		// get contact details
		$getContact = "SELECT name, telephone, email from contacts where id=".$contact_id;
		try
		{
			$resultContact = $pdo3->prepare("$getContact");
			$resultContact->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}	
		$contactRow = $resultContact->fetch();
			$contact_name = $contactRow['name'];
			$contact_phone = $contactRow['telephone'];
			$contact_email = $contactRow['email'];
		// get departments
		

		$issue_arr = mb_unserialize($issue);
		$duraion_arr = mb_unserialize($timer);
		$save_duration_arr = mb_unserialize($type_manual);
		$department_arr = mb_unserialize($department);
		$department_cat_arr = mb_unserialize($department_cat);
		$comment_arr = mb_unserialize($comment);
		$nextaction_desc_arr = mb_unserialize($next_desc);
		$nextaction_end_arr = mb_unserialize($deadline);
		$nextaction_users_arr = mb_unserialize($colleague2);
		$nextaction_priority_arr = mb_unserialize($priority);
		$task_created_arr = mb_unserialize($task_created);
		$task_url_arr = mb_unserialize($task_url);
        
/*        $recipients_arr[1] = 
		 	array(
			   'test1@yopmail.com' => 'Person One',
			   'person2@yopmail.com' => 'Person Two',
			);

		$recipients_arr[2] =	array( 'person3@yopmail.com' => 'Person Three');*/
	

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
       		$issue_parent_arr[$i]['recipients'] =  $recipients_arr[$i];
       }

       
		// Send success emails
		
	    $maiAdmin = $current_useremail;
	    $subject = $current_username." has created a new call log entry that requires your attention";
		$adminmail = new PHPMailer();
		
		$adminmail->isSMTP();
		

			$bodyAdmin = "<!DOCTYPE html>
			<html>
			   <head>
			      <style>
			        table {
			          font-family: arial, sans-serif;
			          border-collapse: collapse;
			          width: 100%;
			        }
			        
			        td, th {
			          border: 1px solid #dddddd;
			          text-align: left;
			          padding: 8px;
			        }
			        
			        tr:nth-child(even) {
			          background-color: #dddddd;
			        }
			      </style>
			   </head>
			   <body>
			      <h1>Call Log</h1>
			      <table>
			         <tr>
			            <td>TimeStamp(Created)</td>
			            <td>".$time_stamp."</td>
			         </tr>";
			      if($choose_call_type == 1){
				     $selectAffiliate = "SELECT name from affiliations where id=".$affiliation_id;
					try
					{
						$aff_results = $pdo3->prepare("$selectAffiliate");
						$aff_results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
						$affRow = $aff_results->fetch();
							$aff_name = $affRow['name'];
			      	$bodyAdmin .= "<tr>
			      			<td>Affiliation</td>
			      			<td>".$aff_name."</td>
			      	</tr>";
			      }   			         
			      $bodyAdmin .=  "<tr>
			            <td>Customer name</td>
			            <td>".$customer_name."</td>
			         </tr>			         
			         <tr>
			            <td>Contact Name</td>
			            <td>".$contact_name."</td>
			         </tr>
			         <tr>
			            <td>Contact Phone</td>
			            <td>".$contact_phone."</td>
			         </tr>				        
			          <tr>
			            <td>Contact Email</td>
			            <td>".$contact_email."</td>
			         </tr>";
			    $no= 1;
			    foreach($issue_parent_arr as $is_parent){  

			           if($is_parent['save_duration'] == 0){
			           		$type_man = 'No';
			           }else{
			           	   $type_man = 'Yes';
			           }   		


		           	// get selected departments
			  		 $selectDepartment = "SELECT name from departments where id = ".$is_parent['department']; 
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
						 $depRow = $depresults->fetch();
						 	$dep_nsme = $depRow['name'];

						$selectDepartmentCat = "SELECT category from department_cat where id = '".$is_parent['department_cat']."'";
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
							$depcatRow = $depcatresults->fetch();
								$depcat_name = $depcatRow['category'];
				         $mainBody .= "
				         <tr>
				         	<th>Issue ".$no."</th>
				         </tr>
				         <tr>
				            <td>Issue</td>
				            <td>".$is_parent['issue']."</td>
				         </tr>			         
				         <tr>
				            <td>Duration</td>
				            <td>".$is_parent['duration']." seconds</td>
				         </tr>	 
				         <tr>
				            <td>Type manual Duration</td>
				            <td>".$type_man."</td>
				         </tr>	 
				         <tr>
				            <td>Department Name</td>
				            <td>".$dep_nsme."</td>
				         </tr>			        
				          <tr>
				            <td>Department Category</td>
				            <td>".$depcat_name."</td>
				         </tr>			         
				         <tr>
				            <td>Comment</td>
				            <td>".$is_parent['comment']."</td>
				         </tr>			         
				         <tr>
				            <td>Next Action Description</td>
				            <td>".$is_parent['nextaction_desc']."</td>
				         </tr>			         
				         <tr>
				            <td>Next Action Deadline</td>
				            <td>".$is_parent['nextaction_end']."</td>
				         </tr>			         
				         <tr>
				            <td>Action Users</td>
				            <td>".$is_parent['nextaction_users']."</td>
				         </tr>			         
				         <tr>
				            <td>Priority</td>
				            <td>".$is_parent['nextaction_priority']."</td>
				         </tr>			         
				         <tr>
				            <td>Task Created ?</td>
				            <td>".$is_parent['task_created']."</td>
				         </tr>				         
				         <tr>
				            <td>Task URL</td>
				            <td>".$is_parent['task_url']."</td>
				         </tr>";
				         $no++;
			         }	
			     $bodyAdmin .= $mainBody;    		         
			    $bodyAdmin .=  "</table>
			   </body>
			</html>";
			
		sendEmail($adminmail, $maiAdmin, $bodyAdmin, $subject);
		
		 $no2= 1;
		 $k=0;
		
		foreach($issue_parent_arr as $issue_div){
			$usermail = new PHPMailer();
			$usermail->isSMTP();
			           if($is_parent['save_duration'] == 0){
			           		$type_man = 'No';
			           }else{
			           	   $type_man = 'Yes';
			           }   		

		           	// get selected departments
			  		 $selectDepartment = "SELECT name from departments where id = ".$issue_div['department']; 
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
						 $depRow = $depresults->fetch();
						 	$dep_nsme = $depRow['name'];

						$selectDepartmentCat = "SELECT category from department_cat where id = '".$issue_div['department_cat']."'";
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
							$depcatRow = $depcatresults->fetch();
								$depcat_name = $depcatRow['category'];
			$bodyUser = "<!DOCTYPE html>
			<html>
			   <head>
			      <style>
			        table {
			          font-family: arial, sans-serif;
			          border-collapse: collapse;
			          width: 100%;
			        }
			        
			        td, th {
			          border: 1px solid #dddddd;
			          text-align: left;
			          padding: 8px;
			        }
			        
			        tr:nth-child(even) {
			          background-color: #dddddd;
			        }
			      </style>
			   </head>
			   <body>
			      <h1>Call Log</h1>
			      <table>
			         <tr>
			            <td>TimeStamp(Created)</td>
			            <td>".$time_stamp."</td>
			         </tr>";
			     if($choose_call_type == 1){
				     $selectAffiliate = "SELECT name from affiliations where id=".$affiliation_id;
					try
					{
						$aff_results = $pdo3->prepare("$selectAffiliate");
						$aff_results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
						$affRow = $aff_results->fetch();
							$aff_name = $affRow['name'];
			      	$bodyUser .= "<tr>
			      			<td>Affiliation</td>
			      			<td>".$aff_name."</td>
			      	</tr>";
			      }  
			        $bodyUser.= "<tr>
			            <td>Customer name</td>
			            <td>".$customer_name."</td>
			         </tr>			         
			         <tr>
			            <td>Contact Name</td>
			            <td>".$contact_name."</td>
			         </tr>
			         <tr>
			            <td>Contact Phone</td>
			            <td>".$contact_phone."</td>
			         </tr>				        
			          <tr>
			            <td>Contact Email</td>
			            <td>".$contact_email."</td>
			         </tr>";
			   
				         $bodyUser .= "
				         <tr>
				         	<th>Issue ".$no2."</th>
				         </tr>
				         <tr>
				            <td>Issue</td>
				            <td>".$issue_div['issue']."</td>
				         </tr>			         
				         <tr>
				            <td>Duration</td>
				            <td>".$issue_div['duration']." seconds</td>
				         </tr>	 
				         <tr>
				            <td>Type manual Duration</td>
				            <td>".$type_man."</td>
				         </tr>	 
				         <tr>
				            <td>Department Name</td>
				            <td>".$dep_nsme."</td>
				         </tr>			        
				          <tr>
				            <td>Department Category</td>
				            <td>".$depcat_name."</td>
				         </tr>			         
				         <tr>
				            <td>Comment</td>
				            <td>".$issue_div['comment']."</td>
				         </tr>			         
				         <tr>
				            <td>Next Action Description</td>
				            <td>".$issue_div['nextaction_desc']."</td>
				         </tr>			         
				         <tr>
				            <td>Next Action Deadline</td>
				            <td>".$issue_div['nextaction_end']."</td>
				         </tr>			         
				         <tr>
				            <td>Action Users</td>
				            <td>".$issue_div['nextaction_users']."</td>
				         </tr>			         
				         <tr>
				            <td>Priority</td>
				            <td>".$issue_div['nextaction_priority']."</td>
				         </tr>			         
				         <tr>
				            <td>Task Created ?</td>
				            <td>".$issue_div['task_created']."</td>
				         </tr>				         
				         <tr>
				            <td>Task URL</td>
				            <td>".$issue_div['task_url']."</td>
				         </tr>";
			      		         
			    $bodyUser .=  "</table>
			   </body>
			</html>";
			$no2++;
             
				
					$yy= 0;
					foreach($issue_div['recipients'] as $email => $name)
					{
						if($yy==0){
							  $mailfirst = $email;
						}else{
					   		
					   		$usermail->addCC($email, $name);
						}

						$yy++;
					}

				
            
			$k++;	

			
				sendEmail($usermail, $mailfirst, $bodyUser, $subject);
		}
          

		// On success: redirect.
		$_SESSION['successMessage'] = "Call added successfully!";
		header("Location: calls.php");
	}
	
	/***** FORM SUBMIT END *****/