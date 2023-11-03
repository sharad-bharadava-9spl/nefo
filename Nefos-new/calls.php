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
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
			
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "AND MONTH(created) = $month AND YEAR(created) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";	
					
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Check if 'entre fechas' was utilised

	if (!empty($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));

		$_SESSION['fromDate'] =$_POST['fromDate'];
		$_SESSION['untilDate'] =$_POST['untilDate'];

		$timeLimit = "AND DATE(created) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}else if (!empty($_SESSION['untilDate']) & !empty($_SESSION['complete'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_SESSION['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_SESSION['untilDate']));
		
		$timeLimit = "AND DATE(created) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";

			  if(empty($_SESSION['userfilter'])){
		      	  unset($_SESSION['complete']);
		      }
		$limitVar = "";
	
	}else{
		$timeLimit = '';

		unset($_SESSION['fromDate']);
		unset($_SESSION['untilDate']);
		
	}
    
		if(isset($_POST['userfilter'])){

		  	  $userfilter = $_POST['userfilter'];
		  	  $_SESSION['userfilter'] = $userfilter;
		  	  if($_POST['userfilter'] != ''){
		    	  $user_limit = "AND LOCATE('".$userfilter."', nextaction_users)>0";
		  	}else{
		  		$user_limit = '';
		  		unset($_SESSION['userfilter']);
		  	}
		 
		}else if(!empty($_SESSION['userfilter']) && $_SESSION['userfilter'] != '' && !empty($_SESSION['complete'])){

		  	  $userfilter = $_SESSION['userfilter'];

		      $user_limit = "AND LOCATE('".$userfilter."', nextaction_users)>0";

		      if(empty($_SESSION['untilDate'])){
		      	  unset($_SESSION['complete']);
		      }
		 	
		}else{

		  $user_limit = '';
          unset($_SESSION['userfilter']);
			
		}
	if (isset($_GET['userid'])) {
		
		// Query to look up calls
		
		$query = "SELECT number FROM customers WHERE id = {$_GET['userid']} $timeLimit $limitVar";
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
			$number = $row['number'];
			
		$selectUsers = "SELECT * FROM calls WHERE customerNumber = $number $timeLimit order by id DESC $limitVar";
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
		
	} else {
		
		// Query to look up calls
	 $selectUsers = "SELECT * FROM calls WHERE 1 $timeLimit $user_limit order by id DESC $limitVar";
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
		
	}

   // get next action users

	$selectActionUsers = "SELECT user_id, first_name, last_name FROM users order by first_name ASC";

	try
		{
			$action_results = $pdo3->prepare("$selectActionUsers");
			$action_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		/*while($userRow = $results->fetch()){
			$user_arr[$userRow['user_id']] = $userRow['first_name']." ".$userRow['last_name'];
		}*/


	$memberScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });	    

	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Customers",
			    filename: "Customers" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			 //    var dateArray = s.split('-');
			 //    var timeArray = dateArray[2].split(':');
			 //    var lastField = (timeArray[0].replace(/\s/g, '')+timeArray[1].replace(/\s/g, ''));
			 //    //alert(dateArray[0] + dateArray[1] + lastField);
			 //    var newDate = myDate[1]+","+myDate[0]+","+lastField;
				// console.log(new Date(newDate).getTime());​
			 //    return ($.tablesorter.formatFloat(dateArray[0] + dateArray[1] + lastField));

			  	 var date,
				dateTimeParts = s.split(' '),
			    timeParts = dateTimeParts[1].split(':'),
			    dateParts = dateTimeParts[0].split('-');
				date = new Date(dateParts[2], parseInt(dateParts[1], 10) - 1, dateParts[0], timeParts[0], timeParts[1]);
				//return dateParts[2], parseInt(dateParts[1], 10) - 1, dateParts[0], timeParts[0], timeParts[1];
				return date.getTime();

			  },
			  type: 'numeric'

			});
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Calls", NULL, $memberScript, "pmembership", NULL, "Calls", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='new-call.php' class='cta'>New Call</a><a href='departments.php' class='cta'>Departments</a><a href='department-category.php' class='cta' style="width: auto;">Department Categories</a><a href='issues.php' class='cta'>Issues</a></center>

	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" onClick="loadExcel();"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a><br /><br />
		<div style='display: inline-block; border: 2px solid #5aa242; padding: 10px;'>
		&nbsp;<strong><?php echo $lang['filter']; ?>:</strong><br /> 
        <form action='' method='POST' style='margin-top: 3px;'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" placeholder="{$lang['from-date']}" value="{$_SESSION['fromDate']}"/>
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" placeholder={$lang['to-date']}" onchange='this.form.submit()' value="{$_SESSION['untilDate']}"/>
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}
?>	 	<br />
<?php if(isset($_POST['userfilter'])){
         $postUserId = $_POST['userfilter'];
  ?>
	   <select id='action_users' name='userfilter' onchange='this.form.submit()'>
	      <option value="">Select Next Action User</option>	
		     <?php while($userRow = $action_results->fetch()){  ?>
		       <option value="<?php echo $userRow['user_id']; ?>" <?php  if($postUserId == $userRow['user_id']){  echo 'selected';  }  ?>  ><?php echo $userRow['first_name']." ".$userRow['last_name']; ?></option>
		    <?php } ?>
		 </select>
<?php }else{ ?>	
	 	 <select id='action_users' name='userfilter' onchange='this.form.submit()'>
	      <option value="">Select Next Action User</option>	
		     <?php while($userRow = $action_results->fetch()){  ?>
		       <option value="<?php echo $userRow['user_id']; ?>" <?php  if($_SESSION['userfilter'] == $userRow['user_id']){  echo 'selected';  }  ?>><?php echo $userRow['first_name']." ".$userRow['last_name']; ?></option>
		    <?php } ?>
		 </select>
	<?php } ?>	 
        </form>
        </div>
       </td>
      </tr>
     </table>


<!--	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>-->
<br />

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Timestamp</th>
	    <th>Operator</th>
	    <th>Type</th>
	    <th>Affiliation</th>
	    <th>Customer Number</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Customer Contact</th>
	    <th>Issue</th>
	    <th>Duration (Seconds)</th>
	    <th>Manual Type Duration</th>
	    <th>Department Name</th>
	    <th>Department Category</th>
	    <th>Person Responsible for Next Action</th>
	    <th>Task Created</th>
	    <th>Complete?</th>
	    <th>Comment</th>
	    <th>Edit</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
      $i=0;
		while ($call = $results->fetch()) {

			 $callId = $call['id'];
		$choose_call = $call['call_type'];
		if($choose_call == 1){
			$call_type = 'Affiliate';
		}else{
			$call_type = 'Club';
		}

		//select affiliate name
		if($choose_call == 1){
			$selectAffiliate = "SELECT name from affiliations where id=".$call['affiliation'];
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
		}else{
			$aff_name = '';
		}

		
			$customerName = $call['customerName'];
			$customerNumber = $call['customerNumber'];
		
		// select operator name
		$selectOperator = 	"SELECT first_name, last_name from users WHERE user_id=".$call['operator_id']; 
			try
			{
				$op_results = $pdo3->prepare("$selectOperator");
				$op_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$opRow = $op_results->fetch();
				$opName = $opRow['first_name']." ".$opRow['last_name'];
		// Query to look up users
		 $getDepartment = "SELECT department FROM calls  WHERE  id=".$call['id'];

			try
			{
				$dept_results = $pdo3->prepare("$getDepartment");
				$dept_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
	   $getRow = $dept_results->fetch();
	   		 $departments = mb_unserialize($getRow['department']);  
	   	//get names
	   	$a=0;	 
	   	foreach ($departments as $department_id) {
	   		 	// fetch department name
	   			$selectdepartmentName  = "SELECT name from departments where id=".$department_id;
				   		try
						{
							$dept_name_results = $pdo3->prepare("$selectdepartmentName");
							$dept_name_results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					$nameRow = $dept_name_results->fetch();
					  $depnamearr[$i][$a] = $nameRow['name'];	
					  $a++;
	   		 }	

	   		 $depName = implode("<br>", $depnamearr[$i]);   

	   // Query to look up users
	 $getDepartmentCat = "SELECT department_cat FROM calls  WHERE id=".$call['id'];

			try
			{
				$depcat_results = $pdo3->prepare("$getDepartmentCat");
				$depcat_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
	   $getRow2 = $depcat_results->fetch();
	   	$department_cats = mb_unserialize($getRow2['department_cat']);  
	   	//get names
	   	$b=0;
	   	foreach ($department_cats as $department_cat_id) {
	   		 	// fetch department name
	   			$selectdepartmentCatName  = "SELECT category from department_cat where id=".$department_cat_id;
				   		try
						{
							$dept_cat_results = $pdo3->prepare("$selectdepartmentCatName");
							$dept_cat_results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					$catRow = $dept_cat_results->fetch();
					  $depncatarr[$i][$b] = $catRow['category'];	
					  $b++;
	   		 }	
	   	 $depCatName = implode("<br>", $depncatarr[$i]);  
     // get contact

	   $getContactInfo =  "SELECT name, telephone from contacts WHERE id=".$call['customerContact'];
	   		try
			{
				$contact_results = $pdo3->prepare("$getContactInfo");
				$contact_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$rowContact = $contact_results->fetch();

			     // get issue

	   $getIssueInfo =  "SELECT issue from calls WHERE id=".$call['id'];
	   		try
			{
				$issue_results = $pdo3->prepare("$getIssueInfo");
				$issue_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$rowIssue = $issue_results->fetch();
		   		$issues = mb_unserialize($rowIssue['issue']);  
		   		
		   	//get issues
		   			$c =0;
				   	foreach ($issues as $issue_id) {
				   		$startc =$c+1;
				   		 	// fetch department name
				   		if(!empty($issue_id)){
					   			 $selectissue  = "SELECT issue from issues where id=".$issue_id;  
								   		try
										{
											$issueName_results = $pdo3->prepare("$selectissue");
											$issueName_results->execute();
										}
										catch (PDOException $e)
										{
												$error = 'Error fetching user: ' . $e->getMessage();
												echo $error;
												exit();
										}
									$issueRow = $issueName_results->fetch();
									  $issuearr[$i][$c] = "<strong>".$startc.".</strong> ".$issueRow['issue'];
								  }	
								  $c++;
				   		 }	
				   		 $issueName = implode("<br>", $issuearr[$i]); 
				   		

			// next action users
         $nextaction_users[$i] = mb_unserialize($call['nextaction_users']);
         $d =0;
         foreach($nextaction_users[$i] as $nextaction_user){
         	$startd = $d+1;
	         if($nextaction_user != ''){
	         	$nextaction_user = $nextaction_user;
	         }else{
	         	$nextaction_user = -1;
	         }
			 $selectUsers = "SELECT * FROM users WHERE user_id IN (".$nextaction_user.")"; 
				try
					{
						$user_results = $pdo3->prepare("$selectUsers");
						$user_results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$j=0;
					while($userRow = $user_results->fetch()){
						if($j == 0){
							$recipients[$i][$d][$j] = "<strong>".$startd.".</strong> ".$userRow['first_name']." ".$userRow['last_name'];
						}else{
							$recipients[$i][$d][$j] = $userRow['first_name']." ".$userRow['last_name'];
						}
						$j++;
					}
					$nextUsersArr[$i][$d] = implode(',', $recipients[$i][$d]);
					$d++;
			}
				

				$nextUsers = implode("<br>", $nextUsersArr[$i])."<br>";

	     // get duration
        $durations = mb_unserialize($call['duration']);
        $e =0;
        foreach($durations as $duration){
        	$durationarr[$i][$e] = $duration;
        	$e++;
        }
        $duration_val = implode("<br>", $durationarr[$i]);        

        $save_durations = mb_unserialize($call['save_duration']);
        $f =0;
        foreach($save_durations as $save_duration){
        	if($save_duration == 0){
        		$manual_type = 'No';
        	}else{
        		$manual_type = 'Yes';
        	}
        	$save_durationarr[$i][$f] = $manual_type;
        	$f++;
        }
        $save_durationa_val = implode("<br>", $save_durationarr[$i]);	

        $task_created = mb_unserialize($call['task_created']);
        $task_url = mb_unserialize($call['task_url']);
        $g =0;
         foreach($task_created as $task){
        	$task_arr[$i][$g] = $task;
        	$g++;
        }
        $task_val = implode("<br>", $task_arr[$i]);	

        $task_url = mb_unserialize($call['task_url']);
        $p =0;
         foreach($task_url as $task_u){
         	if($task_u != ''){
         		$task_url_arr[$i][$p] = "<a href='".$task_u."' target='_blank'>Yes</a>";
         	}else{
        		$task_url_arr[$i][$p] = "<a href='javascript:void(0)'>No</a>";
        	}
        	$p++;
        }
        $task_url_var = implode("<br>", $task_url_arr[$i]);

        $comment_val = mb_unserialize($call['comment']);
        $h =0;
       foreach($comment_val as $comment){ 
		      if ($comment != '') {
				
				$commentRead[$i][$h] = "<table><tr><td style='position:relative;border-bottom:none;padding:0px'>
				                <a href='#'><img src='images/comments.png' id='comment".$h.$callId."' /></a><div id='helpBox".$h.$callId."' class='helpBox'>{$comment}</div>
				                <script>
				                  	$('#comment".$h.$callId."').on({
								 		'mouseover' : function() {
										 	$('#helpBox".$h.$callId."').css('display', 'block');
								  		},
								  		'mouseout' : function() {
										 	$('#helpBox".$h.$callId."').css('display', 'none');
									  	}
								  	});
								</script></td></tr></table>
				                ";
				
			} else {
				
				$commentRead[$i][$h] = "";
				
			}	
			$h++;
		}
		$commentBox = implode("<br><br>", $commentRead[$i]);
		
		$call_method = $call['call_method'];
		if ($call_method == 0) {
			$call_method = 'N/A';
		} else if ($call_method == 1) {
			$call_method = 'Normal';
		} else if ($call_method == 2) {
			$call_method = 'Skype';
		} else if ($call_method == 3) {
			$call_method = 'Whatsapp';
		} else if ($call_method == 4) {
			$call_method = 'Signal';
		} else if ($call_method == 5) {
			$call_method = 'Telegram';
		} else if ($call_method == 6) {
			$call_method = 'Wickr';
		} else {
			$call_method = 'UNKNOWN';
		}
		
		if ($call['complete'] == 0) {
			$complete = "<a href='uTil/complete.php?callid={$call['id']}&menu=no'>{$lang['global-no']}</a>";
		} else if ($call['complete'] == 1) {
			$complete = "<a href='uTil/complete.php?callid={$call['id']}&menu=yes'><img src='images/complete.png' width='16' /></a>";
		}

	echo sprintf("
  	   <tr>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-call.php?callid=%d'>%s</td>
  	   <td class='centered'>%s</td>
  	   <td class='centered'>$complete</td>
  	   <td style=''>%s</td>
		<td class='clickableRow' href='edit-call.php?callid=%d'><img src='images/edit.png' height='15' title='Edit user'></td></tr>",
  	  $call['id'], $call['created'], $call['id'], $opName, $call['id'], $call_method, $call['id'], $aff_name, $call['id'], $customerNumber, $call['id'], $customerName, $call['id'], $rowContact['name'], $call['id'], $issueName, $call['id'], $duration_val, $call['id'], $save_durationa_val, $call['id'], $depName, $call['id'], $depCatName, $call['id'], $nextUsers, $task_url_var, $commentBox, $call['id']
  	  );
	  $i++;
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter(); ?>

<script type="text/javascript">
	 function loadExcel(){
 			$("#load").show();
 			var filter = "<?php echo $_POST['filter']; ?>";
 			var fromDate = "<?php echo $fromDate; ?>";
 			var untilDate = "<?php echo $untilDate; ?>";
 			var userfilter = "<?php echo $userfilter; ?>";
       		window.location.href = 'calls-report.php?filter='+filter+'&fromDate='+fromDate+'&untilDate='+untilDate+'&userfilter='+userfilter;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>