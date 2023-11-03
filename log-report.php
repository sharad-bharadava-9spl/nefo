<?php
	// file created by konstant for Task-15007167 on 31-12-2021
	ob_start();

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
/*	ini_set("log_errors", TRUE);  
  
	// setting the logging file in php.ini 
	ini_set('error_log', "error.log"); */
	$accessLevel = '1';

	$currencyoperator = $_SESSION['currencyoperator'];
	$currencyoperator = html_entity_decode($currencyoperator,ENT_QUOTES,'UTF-8');
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
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
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
			
			$timeLimit = "WHERE MONTH(logtime) = $month AND YEAR(logtime) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";		
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Check if 'entre fechas' was utilised
	$timeLimit = "";
	if (!empty($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "WHERE DATE(logtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}
 	$rows=array();
	$page_size=1000;
	$count = $_GET['totalCount'];
	$domain = $_SESSION['domain'];
	$fileExcel = $domain."-log.xlsx";
	
	if($_GET['count'] == 0){
		// excel count query
		$excelCountQuery  =   "SELECT id, logtype, logtime, user_id, operator, amount, oldCredit, newCredit, oldExpiry, newExpiry, comment FROM log $timeLimit ORDER by logtime DESC";
	   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
	   $countResults= $pdo3->prepare("$excelCountQuery");
	   $countResults->execute();

		$total_records=$countResults->rowCount();

	          $count=ceil($total_records/$page_size);

		$filepath = "excel/".$fileExcel;

		if(file_exists($filepath)){
			unlink($filepath);
		}
	}
	
	
	if($_GET['count'] == 0){
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1', $lang['global-time']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['action']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['operator']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1', $lang['global-member']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-amount']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['donation-creditbefore']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['donation-creditafter']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1', $lang['old-expiry']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',$lang['new-expiry']);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
	}

$countItem = $_GET['count']; 
if($_GET['count'] <= $_GET['totalCount']){
		// Query to look up log items
		$offset_var = $countItem * $page_size;
		$selectLog = "SELECT id, logtype, logtime, user_id, operator, amount, oldCredit, newCredit, oldExpiry, newExpiry, comment FROM log $timeLimit ORDER by logtime DESC Limit ".$page_size." OFFSET ".$offset_var;
			try
			{
				$results = $pdo3->prepare("$selectLog");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

	$startIndex = 2;
	$ex = 0; 
	if(file_exists("excel/$fileExcel")){
   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
   	}
	while ($logItem = $results->fetch()) {
		
		
		$id = $logItem['id'];
		$logtype = $logItem['logtype'];
		$formattedDate = date("d M H:i", strtotime($logItem['logtime'] . "+$offsetSec seconds"));
		$user_id = $logItem['user_id'];
		$operator = $logItem['operator'];
		$operatorID = $logItem['operator'];
		
		if ($logItem['comment'] != '') {
			
			$commentRead = "
			                <img src='images/comments.png' id='comment$id' /><div id='helpBox$id' class='helpBox'>{$logItem['comment']}</div>
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
			
			$commentRead = "";
			
		}
		
		
		//if ($logItem['amount'] == 0) {
		//	$amount = '';
		//} else {
			$amount = number_format($logItem['amount'],2) . " {$currencyoperator}";
		//}
		
		//if ($logItem['oldCredit'] > 0 || $logItem['newCredit'] > 0) {
			$oldCredit = number_format($logItem['oldCredit'],2) . "<span class='smallerfont'>{$_SESSION['currencyoperator']}</span>";
			if ($logtype == 14) {
				$newCredit = '';			
			} else {
				$newCredit = number_format($logItem['newCredit'],2) . "<span class='smallerfont'>{$_SESSION['currencyoperator']}</span>";
			}
		//} else {
		//	$oldCredit = '';
		//	$newCredit = '';
		//}
		
		if ($logItem['newExpiry'] != '') {
			$oldExpiry = date('d M Y', strtotime($logItem['oldExpiry']));
			$newExpiry = date('d M Y', strtotime($logItem['newExpiry']));
		} else {
			$oldExpiry = '';
			$newExpiry = '';
		}
		
		$member = getUser($user_id);
		$operator = getUser($operator);
		
		// Look up logtype
		if ($_SESSION['lang'] == 'es') {
			
			$selectLogType = "SELECT namees, descriptiones FROM logtypes WHERE id = $logtype";
			try
			{
				$result = $pdo3->prepare("$selectLogType");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$logType = $row['namees'];
				$description = $row['descriptiones'];
							
		} else {
			
			$selectLogType = "SELECT nameen, descriptionen FROM logtypes WHERE id = $logtype";
			try
			{
				$result = $pdo3->prepare("$selectLogType");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$logType = $row['nameen'];
				$description = $row['descriptionen'];
				
		}
		
		  	  	// KONSTANT CODE UPDATE BEGIN
		  		$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex, $formattedDate);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex, $logType);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex, $operator);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex, $member); 
	            $objPHPExcel->getActiveSheet()
	           				 ->setCellValue('E'.$startIndex, $amount); 
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('F'.$startIndex, number_format($logItem['oldCredit'],2)." ".$currencyoperator); 
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('G'.$startIndex, number_format($logItem['newCredit'],2)." ".$currencyoperator);  
				$objPHPExcel->getActiveSheet()
							->setCellValue('H'.$startIndex, $oldExpiry); 
				 $objPHPExcel->getActiveSheet()
							->setCellValue('I'.$startIndex, $newExpiry);  
			    $startIndex++;
  	}

        ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
       	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	    echo "<h2>Generating Report....</h2>";
		header('Refresh: 0; log-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
		exit();
		ob_end_flush();
}


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