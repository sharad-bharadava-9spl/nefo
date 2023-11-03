<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	/** Include PHPExcel */
	require_once 'vendor/PHPExcel/Classes/PHPExcel.php';
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
	if (isset($_GET['filter']) && !empty($_GET['filter'])) {
				
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
			
			$timeLimit = "WHERE MONTH(scanin) = $month AND YEAR(scanin) = $year";
			
			$optionList = "<option value='100'>{$lang['last']} 100</option>
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
	if (isset($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		
		$limitVar = '';

		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "WHERE DATE(scanin) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}
	
	// Look up visits
	$scanInR = "SELECT COUNT(DISTINCT userid) FROM newvisits WHERE DATE(scanin) = DATE(NOW()) AND scanout IS NULL";
		try
		{
			$result = $pdo3->prepare("$scanInR");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowR = $result->fetch();
		$peopleInside = $rowR['COUNT(DISTINCT userid)'];
	
	// Look up visits
	$scanIn = "SELECT visitNo, userid, scanin, scanout, completed, duration FROM newvisits $timeLimit ORDER BY scanin DESC $limitVar";
		try
		{
			$result2 = $pdo3->prepare("$scanIn");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
		
		
	// Create month-by-month split
	$findStartDate = "SELECT scanin FROM newvisits ORDER BY scanin ASC LIMIT 1";
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


			  $chk =1;
			  $i=0;
			  $startIndex = $i+2;
			  $activeclass='';
			while ($scaninData = $result2->fetch()) {
				$visitNo = $scaninData['visitNo'];
				$userid = $scaninData['userid'];
				$scanin = $scaninData['scanin'];
				$scanout = $scaninData['scanout'];
				$duration = $scaninData['duration'];
				$completed = $scaninData['completed'];
				
				$scantimeReadable = date('H:i', strtotime($scanin."+$offsetSec seconds"));
				
				setlocale(LC_ALL, 'es_ES');

				$dateOnly = ucfirst(strftime("%A %d %B %Y", strtotime($scanin)));

				$userDetails = "SELECT memberno, first_name, last_name from users WHERE user_id = $userid";
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
				
					$user = $result->fetch();
					$member = "#" . $user['memberno'] . " - " . $user['first_name'] . " " . $user['last_name'];
				 if($i == 0){
					 	$activeclass='active';
					 }	

					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B1','Socio');
					$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C1','Entrada');
					$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D1','Salida');
					$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('E1','Duración');
					$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); 

				if (date('d', strtotime($scanin)) != date('d', strtotime($prevScantime))) {
					   $chk =1;
					  

                      $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $dateOnly);    
					// Insert row with date.
				}
				$startIndex1 = $startIndex + 1;
				 if($chk%2 == 0){
				 	$bgcolor = "";
				 }else{
				 	$bgcolor = "bgcolor";
				 } 
				if ($scanout == '') {

							// KONSTANT CODE UPDATE BEGIN
					  		$objPHPExcel->getActiveSheet()
						                ->setCellValue('B'.$startIndex, $member);
						    $objPHPExcel->getActiveSheet()
						                ->setCellValue('C'.$startIndex, $scantimeReadable);
						    $objPHPExcel->getActiveSheet()
						                ->setCellValue('D'.$startIndex,  '');
						    $objPHPExcel->getActiveSheet()
						                ->setCellValue('E'.$startIndex, ''); 

				} else {
					
					// Determine visit duration	
					$hours  = floor($duration/60); //round down to nearest minute. 
					$minutes = $duration % 60;
					
					$signoutReadable = date('H:i', strtotime($scanout."+$offsetSec seconds"));

							$objPHPExcel->getActiveSheet()
						                ->setCellValue('B'.$startIndex, $member);
						    $objPHPExcel->getActiveSheet()
						                ->setCellValue('C'.$startIndex, $scantimeReadable);
						    $objPHPExcel->getActiveSheet()
						                ->setCellValue('D'.$startIndex,  $signoutReadable);
						    $objPHPExcel->getActiveSheet()
						                ->setCellValue('E'.$startIndex, $hours."h ".$minutes."m "); 
					  
				}

				//echo $expense_row;

				$prevScantime = $scanin;
				$i++;
				$chk++;
				$startIndex++;
			  }

				ob_end_clean();
			    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			    header('Content-type: application/vnd.ms-excel');
			    header('Content-Disposition: attachment;filename=visits.xlsx');
			    header("Content-Type: application/download");
			    //header('Cache-Control: max-age = 0');
			    $objWriter->save('php://output');
    			die;
	?>


