<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	require_once 'vendor/PHPExcel/Classes/PHPExcel.php';
	// Authenticate & authorize
	authorizeUser($accessLevel);
	$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("Lokesh Nayak")
                             ->setLastModifiedBy("Lokesh Nayak")
                             ->setTitle("Test Document")
                             ->setSubject("Test Document")
                             ->setDescription("Test document for PHPExcel")
                             ->setKeywords("office")
                             ->setCategory("Test result file");
  // Add some data
$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1','#');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['global-name']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['member-lastnames']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['global-registered']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-credit']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['member-group']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['global-type']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1',$lang['expiry']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',$lang['signature']);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1',$lang['dni-scan']);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1',$lang['old-number']);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('L1',$lang['global-comment']);
		$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true); 
	if (isset($_GET['searchfield']) && $_GET['searchfield'] != '') {
		
		$phrase = $_GET['searchfield'];
	
		$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.paidUntil, u.userGroup, ug.groupName, u.credit, u.oldNumber, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND (u.first_name LIKE ('%$phrase%') OR u.last_name LIKE ('%$phrase%') OR u.oldNumber LIKE ('%$phrase%') OR u.memberno LIKE ('%$phrase%') OR u.cardid LIKE ('%$phrase%') OR u.dni LIKE ('%$phrase%')) ORDER by u.memberno ASC";
					
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
		
$startIndex =2;
	while ($user = $results->fetch()) {

	
		$paidUntil = $user['paidUntil'];
		$memberType = $user['memberType'];
		$oldNumber = $user['oldNumber'];
		$exento = $user['exento'];
		
		if ($memberType == 0) {
			$mType = '';
		} else if ($memberType == 1) {
			$mType = 'Veterano';
		} else if ($memberType == 2) {
			$mType = 'Terapeutico';
		} else if ($memberType == 3) {
			$mType = 'VIP';
		} else if ($memberType == 4) {
			$mType = 'Cuota anual';
		} else if ($memberType == 5) {
			$mType = 'Invitado';
		} else if ($memberType == 6) {
			$mType = 'Junta';
		} else if ($memberType == 7) {
			$mType = 'Mat, Che, Da';
		} else if ($memberType == 8) {
			$mType = 'Mensual';
		} else if ($memberType == 9) {
			$mType = '3 meses';
		} else if ($memberType == 10) {
			$mType = 'Rojo';
		}
		
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
	// Find out if DNI has been scanned:
	$file = 'images/ID/' . $user['user_id'] . '-front.' . $user['dniext1'];
	
/*	if (!file_exists($file)) {
		$dnicolour = 'negative';
		$dniScan = $lang['global-no'];
	} else {
		$dnicolour = '';
		$dniScan = $lang['global-yes'];
	}*/
	$object_exist =  object_exist($google_bucket, $google_root_folder.$file);
	if($object_exist){
		$dnicolour = '';
		$dniScan = $lang['global-yes'];
		
	}else{
		$dnicolour = 'negative';
		$dniScan = $lang['global-no'];
	}
	// Find out if member has signed:
	$file2 = 'images/sigs/' . $user['user_id'] . '.png';
	
	$object_exist2 = object_exist($google_bucket, $google_root_folder.$file2);

	if ($object_exist2 === false) {
		$form1colour = 'negative';
		$form1 = $lang['global-no'];
	} else {
		$form1colour = '';
		$form1 = $lang['global-yes'];
	}

		

	if ($user['userGroup'] > 4 && $exento == 0) {
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			$membertill = "$memberExpReadable";
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	$membertill = "$memberExpReadable";
		} else if (strtotime($memberExp) > strtotime($timeNow)) {
		  	$membertill = "$memberExpReadable";
		}
		
	} else {
		
		$membertill = "00-00-0000";
		
	}
	
	// Does user have comments?
	$getNotes = "SELECT noteid, notetime, userid, note FROM usernotes WHERE userid = '{$user['user_id']}' ORDER by notetime DESC";
	
	try
	{
		$result = $pdo3->prepare("$getNotes");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$noteid = $row['noteid'];
	if(!$row) {
   		$comment = '';
	} else {
   		$comment = "<img src='images/note.png' width='16' /><span style='display:none'>1</span>";
	}
	
/*	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])));
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.1f $_SESSION['currencyoperator']</td>",
  	  $user['user_id'], $user['credit']);
  	  
}

	echo sprintf("
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], $user['groupName'], $user['user_id'], $mType);

if ($_SESSION['membershipFees'] == 1) {
	  
	echo sprintf("<td class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>",
   $paidClass, $user['user_id'], $membertill);
	    
}

/*	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan);*/
	  
/*	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center; color: red;' class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan, $user['user_id'], $oldNumber);
	  
	  
	echo sprintf("
  	   <td style='text-align: center;' class='clickableRow' href='mini-profile.php?user_id=%d&openComment'>%s</td>
	  </tr>",
	  $user['user_id'], $comment
	  );*/
	  		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $user['memberno']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $user['first_name']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $user['last_name']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, date("d-m-Y",strtotime($user['registeredSince']))); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $user['credit']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $user['groupName']); 
           				 $objPHPExcel->getActiveSheet()
           				 ->setCellValue('G'.$startIndex, $mType); 
           				 $objPHPExcel->getActiveSheet()
           				 ->setCellValue('H'.$startIndex, $membertill); 
           				 $objPHPExcel->getActiveSheet()
           				 ->setCellValue('I'.$startIndex, $form1); 
           				 $objPHPExcel->getActiveSheet()
           				 ->setCellValue('J'.$startIndex, $dniScan); 
           				 $objPHPExcel->getActiveSheet()
           				 ->setCellValue('K'.$startIndex, $oldNumber); 
           				 $objPHPExcel->getActiveSheet()
           				 ->setCellValue('L'.$startIndex, $row['note']);
$startIndex++;

  }
?>

	
	
<?php

	} 

	  	ob_end_clean();
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=scan-profile.xlsx');
            header("Content-Type: application/download");                        
			//header('Cache-Control: max-age = 0');
			$objWriter->save('php://output');die;
	
