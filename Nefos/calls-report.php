<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	require_once 'PHPExcel/Classes/PHPExcel.php';

	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Lokesh Nayak")
	                             ->setLastModifiedBy("Lokesh Nayak")
	                             ->setTitle("Test Document")
	                             ->setSubject("Test Document")
	                             ->setDescription("Test document for PHPExcel")
	                             ->setKeywords("office")
	                             ->setCategory("Test result file");	
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
	if (isset($_GET['filter']) && $_GET['filter'] != '') {
				
		$filterVar = $_GET['filter'];
		
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

	if (!empty($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));

		//$_SESSION['fromDate'] =$_GET['fromDate'];
		//$_SESSION['untilDate'] =$_GET['untilDate'];

		$timeLimit = "AND DATE(created) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}else if (!empty($_SESSION['untilDate']) & !empty($_SESSION['complete'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_SESSION['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_SESSION['untilDate']));
		
		$timeLimit = "AND DATE(created) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";

			 /* if(empty($_SESSION['userfilter'])){
		      	  unset($_SESSION['complete']);
		      }*/
		$limitVar = "";
	
	}else{
		$timeLimit = '';

		/*unset($_SESSION['fromDate']);
		unset($_SESSION['untilDate']);*/
		
	}
    
		if(isset($_GET['userfilter']) && $_GET['userfilter'] != ''){

		  	  $userfilter = $_GET['userfilter'];
		  	 // $_SESSION['userfilter'] = $userfilter;
		  	  if($_GET['userfilter'] != ''){
		    	  $user_limit = "AND LOCATE('".$userfilter."', nextaction_users)>0";
		  	}else{
		  		$user_limit = '';
		  		//unset($_SESSION['userfilter']);
		  	}
		 
		}else if(!empty($_SESSION['userfilter']) && $_SESSION['userfilter'] != '' && !empty($_SESSION['complete'])){

		  	  $userfilter = $_SESSION['userfilter'];

		      $user_limit = "AND LOCATE('".$userfilter."', nextaction_users)>0";

		      /*if(empty($_SESSION['untilDate'])){
		      	  unset($_SESSION['complete']);
		      }*/
		 	
		}else{

		  $user_limit = '';
         // unset($_SESSION['userfilter']);
			
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


	
?>



<!-- 	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Timestamp</th>
	    <th>Operator</th>
	    <th>Type</th>
	    <th>Affiliation</th>
	    <th>Customer Number</th>
	    <th><?php //echo $lang['global-name']; ?></th>
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
	  <tbody> -->
	  
	  <?php
	  				$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A1','Timestamp');
					$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B1','Operator');
					$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C1','Type');
					$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D1','Affiliation');
					$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('E1','Customer Number');
					$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('F1',$lang['global-name']);
					$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('G1','Customer Contact');
					$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('H1','Issue');
					$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('I1','Duration (Seconds)');
					$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('J1','Manual Type Duration');
					$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('K1','Department Name');
					$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('L1','Department Category');
					$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('M1','Person Responsible for Next Action');
					$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('N1','Task Created');
					$objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('O1','Complete ?');
					$objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('P1','Comment');
					$objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true); 

      $i=0;
      $startIndex = 2;
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

	   		 $depName = implode("\n", $depnamearr[$i]);   

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
	   	 $depCatName = implode("\n", $depncatarr[$i]);  
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
									  $issuearr[$i][$c] = $startc.". ".$issueRow['issue'];
								  }	
								  $c++;
				   		 }	
				   		 $issueName = implode("\n", $issuearr[$i]); 
				   		

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
							$recipients[$i][$d][$j] = $startd.". ".$userRow['first_name']." ".$userRow['last_name'];
						}else{
							$recipients[$i][$d][$j] = $userRow['first_name']." ".$userRow['last_name'];
						}
						$j++;
					}
					$nextUsersArr[$i][$d] = implode(',', $recipients[$i][$d]);
					$d++;
			}
				

				$nextUsers = implode("\n", $nextUsersArr[$i]);

	     // get duration
        $durations = mb_unserialize($call['duration']);
        $e =0;
        foreach($durations as $duration){
        	$durationarr[$i][$e] = $duration;
        	$e++;
        }
        $duration_val = implode("\n", $durationarr[$i]);        

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
        $save_durationa_val = implode("\n", $save_durationarr[$i]);	

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
         		$task_url_arr[$i][$p] = "Yes (".$task_u.")";
         	}else{
        		$task_url_arr[$i][$p] = "No";
        	}
        	$p++;
        }
        $task_url_var = implode("\n", $task_url_arr[$i]);

        $comment_val = mb_unserialize($call['comment']);
        $h =0;
        
       foreach($comment_val as $comment){ 
       		$starth = $h+1;
		      if ($comment != '') {
				
				$commentRead[$i][$h] = $starth. ". {$comment}";
				
			} else {
				
				$commentRead[$i][$h] = "";
				
			}	
			$h++;
		}
		$commentBox = implode("\n", $commentRead[$i]);
		
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
			$complete = "{$lang['global-no']}";
		} else if ($call['complete'] == 1) {
			$complete = "{$lang['global-yes']}";
		}

	/*echo sprintf("
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
  	  );*/

		 	$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $call['created']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $opName);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex,  $call_method);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $aff_name); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $customerNumber); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $customerName);  
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('G'.$startIndex, $rowContact['name']);  
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('H'.$startIndex, $issueName);
           $objPHPExcel->getActiveSheet()->getStyle('H'.$startIndex)->getAlignment()->setWrapText(true);				   
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('I'.$startIndex, $duration_val); 
           $objPHPExcel->getActiveSheet()->getStyle('I'.$startIndex)->getAlignment()->setWrapText(true);				 
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('J'.$startIndex, $save_durationa_val);
           $objPHPExcel->getActiveSheet()->getStyle('J'.$startIndex)->getAlignment()->setWrapText(true);				 
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('K'.$startIndex, $depName);
           $objPHPExcel->getActiveSheet()->getStyle('K'.$startIndex)->getAlignment()->setWrapText(true);				 
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('L'.$startIndex, $depCatName);
           $objPHPExcel->getActiveSheet()->getStyle('L'.$startIndex)->getAlignment()->setWrapText(true);				 
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('M'.$startIndex, $nextUsers);
           $objPHPExcel->getActiveSheet()->getStyle('M'.$startIndex)->getAlignment()->setWrapText(true);
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('N'.$startIndex, $task_url_var);
           $objPHPExcel->getActiveSheet()->getStyle('N'.$startIndex)->getAlignment()->setWrapText(true);
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('O'.$startIndex, $complete);
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('P'.$startIndex, $commentBox);
           $objPHPExcel->getActiveSheet()->getStyle('P'.$startIndex)->getAlignment()->setWrapText(true); 

	$startIndex++;
	  $i++;
  }
	ob_end_clean();

     $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;filename=calls.xlsx");
	header('Cache-Control: max-age=0');
	header("Content-Type: application/download");
	$objWriter->save('php://output');
die;
