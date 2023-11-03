<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
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
	foreach ($_GET as $name => $value) {
		if ($name != 'pageno') {
	    	$sortparam .= '&amp;' . $name . '=' . $value;	    	
    	}
	}
	
	// if sort order is set
	if (isset($_GET['sort']) && !empty($_GET['sort'])) {
		
		$sortorder = $_GET['sort'];
		
		if ($sortorder == 0) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.starCat DESC';
			} else {
				$sortby = 'u.starCat ASC';
			}
		} else if ($sortorder == 1) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.memberno DESC';
			} else {
				$sortby = 'u.memberno ASC';
			}
		} else if ($sortorder == 2) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.first_name DESC';
			} else {
				$sortby = 'u.first_name ASC';
			}
		} else if ($sortorder == 3) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.last_name DESC';
			} else {
				$sortby = 'u.last_name ASC';
			}
		} else if ($sortorder == 4) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.credit DESC';
			} else {
				$sortby = 'u.credit ASC';
			}
		} else if ($sortorder == 5) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.creditEligible DESC';
			} else {
				$sortby = 'u.creditEligible ASC';
			}
		} else if ($sortorder == 6) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.registeredSince DESC';
			} else {
				$sortby = 'u.registeredSince ASC';
			}
		} else if ($sortorder == 7) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.gender DESC';
			} else {
				$sortby = 'u.gender ASC';
			}
		} else if ($sortorder == 8) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.year DESC, u.month DESC';
			} else {
				$sortby = 'u.year ASC, u.month ASC';
			}
		} else if ($sortorder == 9) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.usageType DESC';
			} else {
				$sortby = 'u.usageType ASC';
			}
		} else if ($sortorder == 10) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.userGroup DESC';
			} else {
				$sortby = 'u.userGroup ASC';
			}
		} else if ($sortorder == 11) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.paidUntil DESC';
			} else {
				$sortby = 'u.paidUntil ASC';
			}
		}
		
	} else {
		$sortorder = 'a';
		$sortby = 'u.memberno';
	}
	
	// Pagination
	if (isset($_GET['pageno']) && !empty($_GET['pageno'])) {
    	$pageno = $_GET['pageno'];
    } else {
    	$pageno = 1;
    }
    if (isset($_SESSION['pagination'])) {
    	$no_of_records_per_page = $_SESSION['pagination'];
	} else {
    	$no_of_records_per_page = 200;
	}
	
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_pages_sql = "SELECT COUNT(*) FROM users WHERE memberno <> '0' AND userGroup < 6";
	$rowCount = $pdo3->query("$total_pages_sql")->fetchColumn();
    
    $total_pages = ceil($rowCount / $no_of_records_per_page);
    
	// Query to look up users

	if(isset($_GET['list']) && $_GET['list'] == 'full'){

		$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND (memberno = '0' OR u.userGroup > 5) AND u.userGroup <> 8 ORDER by $sortby";

	}else{

		$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND (memberno = '0' OR u.userGroup > 5) AND u.userGroup <> 8 ORDER by $sortby LIMIT $offset, $no_of_records_per_page";

	}
		
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
	

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1','C');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1','#');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['global-name']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['member-lastnames']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-credit']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1','*');
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1', $lang['global-registered']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1',$lang['member-gender']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',$lang['age']);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1',$lang['global-type']);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1',$lang['member-group']);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('L1',$lang['expiry']);
		$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('M1',$lang['signature']);
		$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('N1',$lang['dni-scan']);
		$objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('O1', $lang['global-comment']);
		$objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);  

$startIndex = 2;
	while ($user = $results->fetch()) {

// Calculate Age:
	$day = $user['day'];
	$month = $user['month'];
	$year = $user['year'];
	$paidUntil = $user['paidUntil'];
	$starCat = $user['starCat'];
	$oldNumber = $user['oldNumber'];
	$exento = $user['exento'];
	
	
	
$bdayraw = $day . "." . $month . "." . $year;
$bday = new DateTime($bdayraw);
$today = new DateTime(); // for testing purposes
$diff = $today->diff($bday);
$age = $diff->y;

	// Find out if DNI has been scanned:
	$file = 'images/_' . $_SESSION['domain'] . '/ID/' . $user['user_id'] . '-front.' . $user['dniext1'];
	
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
	$file2 = 'images/_' . $_SESSION['domain'] . '/sigs/' . $user['user_id'] . '.png';

	$object_exist2 =  object_exist($google_bucket, $google_root_folder.$file2);
	
	if ($object_exist2 === false) {
		$form1colour = 'negative';
		$form1 = $lang['global-no'];
	} else {
		$form1colour = '';
		$form1 = $lang['global-yes'];
	}

	if ($user['usageType'] == '1') {
		$usageType = "1";
	} else {
		$usageType = '';
	}
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

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
	
	if ($user['creditEligible'] == 1) {
		$creditEligible = $lang['global-yes'];
	} else {
		$creditEligible = '';
	}
	
	if ($starCat == 1) {
   		$userStar = "<img src='images/star-yellow.png' width='16' /><span style='display:none'>1</span>";
	} else if ($starCat == 2) {
   		$userStar = "<img src='images/star-black.png' width='16' /><span style='display:none'>2</span>";
	} else if ($starCat == 3) {
   		$userStar = "<img src='images/star-green.png' width='16' /><span style='display:none'>3</span>";
	} else if ($starCat == 4) {
   		$userStar = "<img src='images/star-red.png' width='16' /><span style='display:none'>4</span>";
	} else if ($starCat == 5) {
   		$userStar = "<img src='images/star-purple.png' width='16' /><span style='display:none'>5</span>";
	} else if ($starCat == 6) {
   		$userStar = "<img src='images/star-blue.png' width='16' /><span style='display:none'>6</span>";
	} else {
   		$userStar = "<span style='display:none'>0</span>";
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
   		//$comment = "<img src='images/note.png' width='16' /><span style='display:none'>1</span>";
   		   		$comment = "
		                <img src='images/note.png' id='comment$noteid' /><div id='helpBox$noteid' class='helpBox'>{$row['note']}</div>
		                <script>
		                  	$('#comment$noteid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$noteid').css('display', 'inline-block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$noteid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
	}


	  
	  	  // KONSTANT CODE UPDATE BEGIN
	  		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $starCat);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $user['memberno']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $user['first_name']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $user['last_name']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('E'.$startIndex, $user['credit']." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $creditEligible); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, date("d-m-Y",strtotime($user['registeredSince']))); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$startIndex, $user['gender']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex,  $age);	
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $usageType);  
			$objPHPExcel->getActiveSheet()
						->setCellValue('K'.$startIndex, $user['groupName']);  
			$objPHPExcel->getActiveSheet()
						->setCellValue('L'.$startIndex,  $membertill);  
			$objPHPExcel->getActiveSheet()
						->setCellValue('M'.$startIndex, '');  
			$objPHPExcel->getActiveSheet()
						->setCellValue('N'.$startIndex, $form1);  
			$objPHPExcel->getActiveSheet()
						->setCellValue('O'.$startIndex, $row['note']);  
		    $startIndex++; 

 }

 		  	ob_end_clean();
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=non-members.xlsx');
            header("Content-Type: application/download");                        
			//header('Cache-Control: max-age = 0');
			$objWriter->save('php://output');die;
			//header("location:dispensary-history.php");
?>
