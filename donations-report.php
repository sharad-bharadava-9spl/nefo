<?php
	ob_start();
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	// Authenticate & authorize
	authorizeUser($accessLevel);
	require_once  'vendor/PHPExcel/Classes/PHPExcel.php';	
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
	getSettings();
	// Check if new Filter value was submitted, and assign query variable accordingly
	
		
	// Check if 'entre fechas' was utilised
	if (!empty($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "AND DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}else{
		$timeLimit = '';
	}
	// usergroup filter
	$selectedUsergroup = "1,2,3";
	if(isset($_POST['submitted'])){
		$firstSelect = 'false';
		//  code to filter the sales from usergroups
		if(isset($_POST['cashBox'])){
			$selectedUserArr = $_POST['cashBox'];
			$selectedUsergroup = implode(",", $selectedUserArr);
			$getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
			$result = $pdo3->prepare("$getUsers");
			$result->execute();
			while($user_ids = $result->fetch()){
				$userArr[] = $user_ids['user_id'];
			}
			if(!empty($userArr)){
				array_push($userArr, 999999);
			}
			$selectedUsers = implode(',',$userArr);
			if(empty($selectedUsers) || $selectedUsers ==''){
				$selectedUsers = -1;
			}
			$user_limit = "AND operator IN ($selectedUsers)";
		}else{
			$user_limit = 'AND operator IN (0)';
		}
	}else{
		 $firstSelect = 'true';
		  $getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
			$result = $pdo3->prepare("$getUsers");
			$result->execute();
			while($user_ids = $result->fetch()){
				$userArr[] = $user_ids['user_id'];
			}
			array_push($userArr, 999999);
		    $selectedUsers = implode(',',$userArr);
		    if(empty($selectedUsers) || $selectedUsers == ''){
				$selectedUsers = -1;		    	
		    }else{
		    	$selectedUsers .= ',0';
		    }

		   $user_limit = "AND operator IN ($selectedUsers)";
	}
	// Query to look up past payments
/*	 $selectExpenses = "SELECT donationid, donationTime, userid, amount, creditBefore, creditAfter, donatedTo, operator, type, comment FROM donations WHERE 1 $timeLimit $user_limit ORDER by donationTime DESC $limitVar";
		try
		{
			$results = $pdo3->prepare("$selectExpenses");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}*/
	
			    $rows=array();
				$page_size=1000;
				$count = $_GET['totalCount'];
				$domain = $_SESSION['domain'];
				$fileExcel = $domain."-donations.xlsx";
				
		    if($_GET['count'] == 0){
				// excel count query
				$excelCountQuery  =   "SELECT donationid, donationTime, userid, amount, creditBefore, creditAfter, donatedTo, operator, type, comment FROM donations WHERE 1 $timeLimit ORDER by donationTime DESC";
			   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
			   $countResults= $pdo3->prepare("$excelCountQuery");
			   $countResults->execute();

				$total_records=$countResults->rowCount();

				$count=ceil($total_records/$page_size);

				$filepath = "excel/".$fileExcel;

				if(file_exists($filepath)){
					unlink($filepath);
				}
				//$count = 3;
			}	
		
	// Create month-by-month split
	$findStartDate = "SELECT donationTime FROM donations ORDER BY donationTime ASC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$findStartDate");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$startDate = date('01-m-Y', strtotime($row['donationTime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
	while (strtotime($genDateFull) > strtotime($startDate)) {
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}
	}
	
	
if($_GET['count'] == 0){
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1',$lang['global-time']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['global-type']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['donated-to']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',"#");
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-member']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['global-amount']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['donation-creditbefore']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1',$lang['donation-creditafter']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',$lang['operator']);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1',$lang['global-comment']);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);  
	}
$countItem = $_GET['count'];
if($_GET['count'] <= $_GET['totalCount']){
   	$offset_var = $countItem * $page_size;
   	$query  =   "SELECT donationid, donationTime, userid, amount, creditBefore, creditAfter, donatedTo, operator, type, comment FROM donations WHERE 1 $timeLimit ORDER by donationTime DESC Limit ".$page_size." OFFSET ".$offset_var;
   	//$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
   	$results= $pdo3->prepare("$query");
   	$results->execute();
	  $startIndex = 2;

	if(file_exists("excel/$fileExcel")){
   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
   	}
   	
while ($donation = $results->fetch()) {
	$donationid = $donation['donationid'];
	$donationTime = date("d-m-Y H:i", strtotime($donation['donationTime'] . "+$offsetSec seconds"));
	$amount = $donation['amount'];
	$creditBefore = $donation['creditBefore'];
	$creditAfter = $donation['creditAfter'];
	$donatedTo = $donation['donatedTo'];
	$user_id = $donation['userid'];
	$operatorID = $donation['operator'];
	$type = $donation['type'];
	
	if ($type == 1) {
		$operationType = $lang['donation-donation'];
	} else if ($type == 2) {
		$operationType = $lang['changed-credit'];
	} else if ($type == 3) {
		$operationType = $lang['global-edit'];
	}
	
	if ($operatorID == 0) {
		$operator = '';
	} else {
		$operator = getOperator($operatorID);
	}
	
	if ($donation['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$donationid' /><div id='helpBox$donationid' class='helpBox'>{$donation['comment']}</div>
		                <script>
		                  	$('#comment$donationid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$donationid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$donationid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
	
	
	if ($donatedTo == '2') {
		$donatedTo = $lang['global-bank'];
	} else if ($donatedTo == '3') {
		$donatedTo = '';
	} else if ($donatedTo == '4') {
		$donatedTo = 'CashDro';
	} else {
		$donatedTo = $lang['global-till'];
	}

		if ($_SESSION['domain'] == 'granvalle') {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		} else if ($type != 2) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><!--<a href='edit-donation.php?donationid=$donationid&userid=$user_id'><img src='images/edit.png' height='15' /></a>-->&nbsp;&nbsp;<a href='javascript:delete_donation($donationid,$amount,$user_id)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
		
		// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userDetails");
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
			$last_name = $row['last_name'];

	  	  // KONSTANT CODE UPDATE BEGIN
	  		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $donationTime);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $operationType);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $donatedTo);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $memberno); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('E'.$startIndex, $first_name." ".$last_name); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $amount." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, $creditBefore." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$startIndex, $creditAfter." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex, $operator);	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $donation['comment']);		    
		    $startIndex++; 
	  
  }

  
    ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
       	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	   
		//header('Location:donations-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
			    echo "<h2>".$lang['report-generate']."</h2>";
		header('Refresh: 0; donations-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
		exit();
		ob_end_flush();
   		
}

	//  ignore_user_abort ();
$f=$fileExcel;   
$file = ("excel/$f");

	    if(file_exists($file)){
?>
	 <script type="text/javascript">
	 	
	 	window.opener.location.replace("<?php echo $file ?>");
	 	setTimeout(function(){ window.close(); }, 1000);
	 	
	 </script>
<?php
}
