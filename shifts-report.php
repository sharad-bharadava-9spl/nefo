<?php
	
	// Here we should check if the openings/closings were fully done, and then do a flag!
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';

	$currencyoperator = $_SESSION['currencyoperator'];
	$currencyoperator = html_entity_decode($currencyoperator,ENT_QUOTES,'UTF-8');
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

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
	
	
	// Day by day, but start with today for experimentation
	
	// First open day, opened by
	// Closed shift #1, closed by
	// Opened shift #2, opened by
	
	// Query to look up openings
	
	// Also look up closings!
	
	$selectOpenings = "SELECT 'Cerrar dia' AS type, 'CloseDay' AS typeshort, closingid AS openingid, closingtime AS shiftStart, cashintill AS tillBalance, tillDelta, closedby AS openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM closing UNION ALL SELECT 'Abrir dia' AS type, 'OpenDay' AS typeshort, openingid, openingtime AS shiftStart, tillBalance, tillDelta, openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM opening UNION ALL SELECT 'Cerrar turno' AS type, 'CloseShift' AS typeshort, closingid AS openingid, shiftEnd AS shiftStart, cashintill AS tillBalance, tillDelta, closedby AS openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM shiftclose UNION ALL SELECT 'Comenzar turno' AS type, 'StartShift' AS typeshort, openingid, openingtime AS shiftStart, tillBalance, tillDelta, openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract FROM shiftopen ORDER by shiftStart DESC";
		try
		{
			$results = $pdo3->prepare("$selectOpenings");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	
?>

	  
	  <?php

	  	$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1',$lang['global-type']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['global-time']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['responsible']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['global-till']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-delta']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['global-product']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['global-delta']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1', $lang['global-flowers']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',$lang['global-delta']);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1',$lang['global-extracts']);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1',$lang['global-delta']);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true); 

	$startIndex = 2;

		while ($opening = $results->fetch()) {

	$type = $opening['type'];
	$shiftStartOrig = $opening['shiftStart'];
	$typeshort = $opening['typeshort'];
	$openingid = $opening['openingid'];
	$shiftStart = date("d-m-Y H:i", strtotime($opening['shiftStart'] . "+$offsetSec seconds"));
	$tillBalance = $opening['tillBalance'];
	$tillDelta = $opening['tillDelta'];
	$openedby = $opening['openedby'];
	$prodStock = $opening['prodStock'];
	$prodStockFlower = $opening['prodStockFlower'];
	$prodStockExtract = $opening['prodStockExtract'];
	$stockDelta = $opening['stockDelta'];
	$stockDeltaFlower = $opening['stockDeltaFlower'];
	$stockDeltaExtract = $opening['stockDeltaExtract'];
	
	$user = getUser($openedby);

	if ($typeshort == 'CloseDay') {
		
		$shifttype = 1;
		$type = $lang['closeday-main'];
		
		if ($_SESSION['openAndClose'] > 2) {
			
			$checkFirst = "SELECT dayClosed FROM opening WHERE openingtime < '$shiftStartOrig' ORDER BY openingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$checkFirst");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayClosed = $row['dayClosed'];
				
			if ($dayClosed == 2) {
				
				$completedFlag = "<img src='images/checkmark.png' width='15' />";
				
			} else {
				
				$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
				
			}

		}
		
	} else if ($typeshort == 'OpenDay') {
		
		$shifttype = 2;
		$type = $lang['openday'];
		
		$selectUsers = "SELECT COUNT(firstDayOpen) FROM opening";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
		$checkFirst = "SELECT firstDayOpen FROM opening";
		try
		{
			$result = $pdo3->prepare("$checkFirst");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
				
		if ($rowCount == 1) {
			
			// Only 1 opening exists - first opening
			$row = $result->fetch();
				$firstDayOpen = $row['firstDayOpen'];
				
			if ($firstDayOpen == 2) {
				
				$completedFlag = "<img src='images/checkmark.png' width='15' />";
				
			} else {
				
				$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
				
			}
			
		} else {
			
			// Not first opening
			$checkFirst = "SELECT dayOpened FROM closing WHERE closingtime < '$shiftStartOrig' ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$checkFirst");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayOpened = $row['dayOpened'];
			
			if ($dayOpened == 2) {
				
				$completedFlag = "<img src='images/checkmark.png' width='15' />";
				
			} else {
				
				$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
				
			}
			
		}
				
		
	} else if ($typeshort == 'CloseShift') {
		
		$shifttype = 3;
		$type = $lang['close-shift'];
		
	} else if ($typeshort == 'StartShift') {
		
		$shifttype = 4;
		$type = $lang['start-shift'];
		
	}
	
	if ($tillDelta < 0) {
		
		$tillColour = 'negative';
		$tillColourCode = '#ff0000';
		
	} else if ($tillDelta > 0) {
		
		$tillColour = 'positive';
		$tillColourCode = '#5aa242';
		
		
	} else {
		
		$tillColour = '';	
		$tillColourCode = '#000000';
		
	}

	
	if ($shifttype == 2) {
		
/*		$expense_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #507c3d;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #507c3d;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #507c3d;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
	  	   <td class='clickableRow %s' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <!--<td style='border-bottom: 4px solid #507c3d;'>%s</td>-->
		  </tr>
",
		  $shifttype, $openingid, $type, $shifttype, $openingid, $shiftStart, $shifttype, $openingid, $user, $shifttype, $openingid, $tillBalance, $tillColour, $shifttype, $openingid, $tillDelta, $shifttype, $openingid, $prodStock, $shifttype, $openingid, $stockDelta, $shifttype, $openingid, $prodStockFlower, $shifttype, $openingid, $stockDeltaFlower, $shifttype, $openingid, $prodStockExtract, $shifttype, $openingid, $stockDeltaExtract, $completedFlag
		  );*/

			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $type);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $shiftStart);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $user);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $tillBalance." ".$currencyoperator); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('E'.$startIndex, $tillDelta." ".$currencyoperator);
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $prodStock." g."); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, $stockDelta." g."); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$startIndex, $prodStockFlower." g."); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex,  $stockDeltaFlower." g.");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $prodStockExtract." g.");  
			$objPHPExcel->getActiveSheet()
						->setCellValue('K'.$startIndex, $stockDeltaExtract." g.");  
		  
	} else {
		
	
/*		$expense_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
	  	   <td class='clickableRow %s' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <!--<td>%s</td>-->
		  </tr>",
		  $shifttype, $openingid, $type, $shifttype, $openingid, $shiftStart, $shifttype, $openingid, $user, $shifttype, $openingid, $tillBalance, $tillColour, $shifttype, $openingid, $tillDelta, $shifttype, $openingid, $prodStock, $shifttype, $openingid, $stockDelta, $shifttype, $openingid, $prodStockFlower, $shifttype, $openingid, $stockDeltaFlower, $shifttype, $openingid, $prodStockExtract, $shifttype, $openingid, $stockDeltaExtract, $completedFlag
		  );*/

			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $type);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $shiftStart);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $user);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $tillBalance." ".$currencyoperator); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('E'.$startIndex, $tillDelta." ".$currencyoperator);
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $prodStock." g."); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, $stockDelta." g."); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$startIndex, $prodStockFlower." g."); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex,  $stockDeltaFlower." g.");	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $prodStockExtract." g.");  
			$objPHPExcel->getActiveSheet()
						->setCellValue('K'.$startIndex, $stockDeltaExtract." g."); 
		  
	}
	$startIndex++;
	//echo $expense_row;
	
  }

	 ob_end_clean();
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename='.$lang['shifts'].'.xlsx');
    header("Content-Type: application/download");                        
	//header('Cache-Control: max-age = 0');
	$objWriter->save('php://output');die;
