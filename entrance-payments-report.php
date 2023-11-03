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
	$currencyoperator = $_SESSION['currencyoperator'];
	$currencyoperator = html_entity_decode($currencyoperator,ENT_QUOTES,'UTF-8');
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
	


	// Check if 'entre fechas' was utilised
	if (isset($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "WHERE DATE(paymentdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}else{
		$timeLimit = '';
	}
	
	// Query to look up past payments
/*	$selectExpenses = "SELECT paymentid, paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment, operator, creditBefore, creditAfter FROM memberpayments $timeLimit ORDER by paymentdate DESC";
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
				$fileExcel = $domain."-entrance-payments.xlsx";
				
		    if($_GET['count'] == 0){
				// excel count query
				$excelCountQuery  =   "SELECT paymentid, paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment, operator, creditBefore, creditAfter FROM entrancepayments $timeLimit ORDER by paymentdate DESC";
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
	$findStartDate = "SELECT paymentdate FROM entrancepayments ORDER BY paymentdate ASC LIMIT 1";
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
		$startDate = date('01-m-Y', strtotime($row['paymentdate']));
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
		            ->setCellValue('A1', $lang['global-time']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['paid-by']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1','#');
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1', $lang['global-member']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-amount']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['old-expiry']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['new-expiry']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1', $lang['dispense-oldcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',$lang['dispense-newcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1',$lang['operator']);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);  

	}
		   
$countItem = $_GET['count'];
if($_GET['count'] <= $_GET['totalCount']){
   	$offset_var = $countItem * $page_size;
   	$query  =   "SELECT paymentid, paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment, operator, creditBefore, creditAfter FROM entrancepayments $timeLimit ORDER by paymentdate DESC Limit ".$page_size." OFFSET ".$offset_var;
   	//$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
   	$results= $pdo3->prepare("$query");
   	$results->execute();
 $startIndex = 2;

    if(file_exists("excel/$fileExcel")){
   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
   	}
		while ($donation = $results->fetch()) {
	
	$paymentid = $donation['paymentid'];
	$paymentdate = date("d-m-Y H:i", strtotime($donation['paymentdate'] . "+$offsetSec seconds"));
	$amount = $donation['amountPaid'];
	$paidTo = $donation['paidTo'];
	
	$creditBefore = $donation['creditBefore'];
	$creditAfter = $donation['creditAfter'];
	$operatorID = $donation['operator'];

	if ($operatorID == 0) {
		$operator = '';
	} else {
		$operator = getOperator($operatorID);
	}

	
	if ($donation['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$paymentid' /><div id='helpBox$paymentid' class='helpBox'>{$donation['comment']}</div>
		                <script>
		                  	$('#comment$paymentid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$paymentid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$paymentid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

	
	if ($donation['oldExpiry'] == NULL) {
		$oldExpiry = "<span class='white'>00-00-0000</span>";
	} else {
		$oldExpiry = date("d-m-Y", strtotime($donation['oldExpiry']));
	}
	$newExpiry = date("d-m-Y", strtotime($donation['newExpiry']));
	
	
	if ($paidTo == '2') {
		$paidTo = $lang['card'];
	} else if ($paidTo == '3') {
		$paidTo = $lang['global-credit'];
	} else if ($paidTo == '4') {
		$paidTo = "CashDro";
	} else if ($paidTo == '5') {
		$paidTo = $lang['changed-expiry'];
	} else {
		$paidTo = $lang['cash'];
	}
	
		$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_payment($paymentid)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></td>";
		
	// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = {$donation['userid']}";
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
		                ->setCellValue('A'.$startIndex, $paymentdate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $paidTo);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $memberno);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $first_name." ".$last_name); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('E'.$startIndex, $amount); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, date("d-m-Y", strtotime($donation['oldExpiry']))); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, date("d-m-Y", strtotime($donation['newExpiry'])));  
			$objPHPExcel->getActiveSheet()
						->setCellValue('H'.$startIndex, $creditBefore." ".$currencyoperator); 
			 $objPHPExcel->getActiveSheet()
						->setCellValue('I'.$startIndex, $creditAfter." ".$currencyoperator); 			 
			$objPHPExcel->getActiveSheet()
						->setCellValue('K'.$startIndex, $operator);  
		    $startIndex++;
	  
  }

        ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
       	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	    
		//header('Location:member-payments-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
   
    	echo "<h2>".$lang['report-generate']."</h2>";
		header('Refresh: 0; entrance-payments-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
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

 ?>	
