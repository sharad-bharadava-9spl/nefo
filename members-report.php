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
	//  KONSTANT CODE UPDATE BEGIN
	if ($_SESSION['lang'] == 'es') {
	     $exportname = 'Miembros';
	} else {
	     $exportname = 'Members';
	}	
	//  KONSTANT CODE UPDATE END
	getSettings(); 
	
	// Sortable headers
	// Pagination in sys settings
	// Remember sorting
	// KONSTANT CODE UPDATE BEGIN
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
	// KONSTANT CODE UPDATE END
	foreach ($_GET as $name => $value) {
		if ($name != 'pageno') {
	    	$sortparam .= '&amp;' . $name . '=' . $value;	    	
    	}
	}
	
	// if sort order is set
	if (isset($_GET['sort']) && $_GET['sort'] != '') {
		
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
		} else if ($sortorder == 12) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.userGroup2 DESC';
			} else {
				$sortby = 'u.userGroup2 ASC';
			}
		}
		
	} else {
		$sortorder = 'a';
		$sortby = 'u.memberno';
	}
	
	// Pagination
	if (isset($_GET['pageno']) && $_GET['pageno'] != '') {
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
    
    // Add filter based on requests from member-statistics.php
    if (isset($_GET['filter']) && $_GET['filter'] != '') {
	    
	    $filter = $_GET['filter'];
	    
	    if ($filter == 'all') {
	    
		    $filter = "AND memberno <> '0' AND u.userGroup < 6";
	    
	    } else if ($filter == 'active') {
	    
		    $filter = "AND memberno <> '0' AND u.userGroup < 6 AND DATE(paidUntil) >= DATE(NOW())";
	    
	    } else if ($filter == '') {
		    
	    	$filter = "AND memberno <> '0' AND u.userGroup < 6";
	    	
    	} else {
	    	
	    	$filter = "AND u.userGroup = " . $filter;
		    
	    }
	    
    } else {
	    
	    	$filter = "AND memberno <> '0' AND u.userGroup < 6";
	    	
    }
    
    // Add filter based on requests from member-signups.php
    if (isset($_GET['period']) && $_GET['period'] != '') {
	    
	    $period = $_GET['period'];
	    $periodvar = $_GET['limit'];
	    $filter = "";
	    
	    if ($period == 'day') {
		    
		    if ($periodvar == 0) {
				$dateOperator = "AND DATE(registeredSince) = DATE(NOW())"; 
		    } else {
				$dateOperator = "AND DATE(registeredSince) = DATE_ADD(DATE(NOW()), INTERVAL -$periodvar DAY)";
		    }
		    
	    } else if ($period == 'week') {
		    
		    if ($periodvar == 0) {
				$dateOperator = "AND WEEK(registeredSince,1) = WEEK(NOW(),1) AND YEAR(registeredSince) = YEAR(NOW())"; 
		    } else if ($periodvar == 1) {
				$dateOperator = "AND WEEK(registeredSince,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$periodvar WEEK),1) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -$periodvar WEEK))"; 
		    } else {
				$dateOperator = "AND WEEK(registeredSince,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$periodvar WEEK),1) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -$periodvar WEEK))";
		    }
		    
	    } else if ($period == 'month') {
		    
		    if ($periodvar == 0) {
				$dateOperator = "AND MONTH(registeredSince) = MONTH(NOW()) AND YEAR(registeredSince) = YEAR(NOW())"; 
		    } else {
				$dateOperator = "AND MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -$periodvar MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -$periodvar MONTH))";
		    }
		    
	    }
	    
    }
	$rows=array();
	$page_size=1000;
	$count = $_GET['totalCount'];
	$domain = $_SESSION['domain'];
	$fileExcel = $domain."-members.xlsx";  


if($_GET['count'] == 0){

		 // If downloading full list, send mail to admin + insert into feature table
	 
		  
			$query = "INSERT INTO features (domain, user_id, userGroup, feature) VALUES ('{$_SESSION['domain']}', '{$_SESSION['user_id']}', '{$_SESSION['userGroup']}', 'Member list')";
			try
			{
				$result = $pdo->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$mailBody = "Dear admin,<br /><br />Club {$_SESSION['domain']} has just downloaded a normal members list from their SW.";
		  
			try {
				
			// Send e-mail(s)
			require_once 'PHPMailerAutoload.php';
			
			
			$mail = new PHPMailer(true);
			$mail->CharSet = 'UTF-8';
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "mail.cannabisclub.systems";
			$mail->SMTPAuth = true;
			$mail->Username = "info@cannabisclub.systems";
			$mail->Password = "Insjormafon9191";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('info@cannabisclub.systems', 'CCSNube');
			$mail->addAddress("andreas@cannabisclub.systems", "CCS");
			$mail->addAddress("info@cannabisclub.systems", "CCS");
			$mail->Subject = "Club {$_SESSION['domain']} has just downloaded a normal members list";
			$mail->isHTML(true);
			$mail->Body = $mailBody;
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "HAU";
			}
		  
	  
			// excel count query
	if ($_SESSION['domain'] == 'santafe') {
		
			$excelCountQuery = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.userGroup2, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento, u.cardid FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND ((u.usergroup = 5 AND (u.paidUntil >= DATE(NOW()) OR u.exento = 1)) OR u.userGroup < 5) $filter $dateOperator ORDER by $sortby"; 
		  
	  } else {
		
			$excelCountQuery = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.userGroup2, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento, u.cardid FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup $filter $dateOperator ORDER by $sortby"; 
		  
	  }
		   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
		   $countResults= $pdo3->prepare("$excelCountQuery");
		   $countResults->execute();

			$total_records=$countResults->rowCount();

            $count=ceil($total_records/$page_size);

			$filepath = "excel/".$fileExcel;

			if(file_exists($filepath)){
				unlink($filepath);
			}
			//$count =4;

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
			            ->setCellValue('E1',$lang['global-registered']);
			$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('F1', $lang['member-gender']);
			$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('G1', $lang['age']);
			$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('H1',$lang['global-type']);
			$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('I1',$lang['member-group']);
			$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('J1',$lang['expiry']);
			$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('K1',$lang['signature']);
			$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('L1',$lang['dni-scan']);
			$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);   
			if ($_SESSION['domain'] == 'londres' || $_SESSION['domain'] == 'paradox') {
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('M1','Chip');
					$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);   
			}
	}
	// Query to look up users
	// KONSTANT CODE UPDATE BEGIN
$countItem = $_GET['count'];
if($_GET['count'] <= $_GET['totalCount']){
	$offset_var = $countItem * $page_size;	
	if ($_SESSION['domain'] == 'santafe') {
		
			$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.userGroup2, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento, u.cardid FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND ((u.usergroup = 5 AND (u.paidUntil >= DATE(NOW()) OR u.exento = 1)) OR u.userGroup < 5) $filter $dateOperator ORDER by $sortby Limit ".$page_size." OFFSET ".$offset_var; 
		  
	  } else {
		
			$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.userGroup2, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento, u.cardid FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup $filter $dateOperator ORDER by $sortby Limit ".$page_size." OFFSET ".$offset_var; 
		  
	  }
	// KONSTANT CODE UPDATE END	
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
	

  $startIndex = 2;  

     if(file_exists("excel/$fileExcel")){
   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
   	}

	while ($user = $results->fetch()) {

// Calculate Age:
	$day = $user['day'];
	$month = $user['month'];
	$year = $user['year'];
	$paidUntil = $user['paidUntil'];
	$starCat = $user['starCat'];
	$oldNumber = $user['oldNumber'];
	$exento = $user['exento'];
	$cardid = $user['cardid'];
	$userGroup2 = $user['userGroup2'];
	
	$query = "SELECT name FROM usergroups2 WHERE id = '$userGroup2'";
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
		$groupName = $row['name'];	
		
/*	
$bdayraw = $day . "." . $month . "." . $year;
$bday = new DateTime($bdayraw);
$today = new DateTime(); // for testing purposes
$diff = $today->diff($bday);
$age = $diff->y;*/
$birthDate = $month . "/" . $day . "/" . $year;
  $birthDate = explode("/", $birthDate);
  //get age from date or birthdate
  $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
    ? ((date("Y") - $birthDate[2]) - 1)
    : (date("Y") - $birthDate[2]));

	// Find out if DNI has been scanned:
	$file = 'images/_' . $_SESSION['domain'] . '/ID/' . $user['user_id'] . '-front.' . $user['dniext1'];
	
	if (!file_exists($file)) {
		$dnicolour = 'negative';
		$dniScan = $lang['global-no'];
	} else {
		$dnicolour = '';
		$dniScan = $lang['global-yes'];
	}

	// Find out if member has signed:
	$file2 = 'images/_' . $_SESSION['domain'] . '/sigs/' . $user['user_id'] . '.png';
	
	if (!file_exists($file2)) {
		$form1colour = 'negative';
		$form1 = $lang['global-no'];
	} else {
		$form1colour = '';
		$form1 = $lang['global-yes'];
	}

	if ($user['usageType'] == '1') {
		$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
		$usageName = "Medical";
	} else {
		$usageType = '';
		$usageName = "";
	}
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

	if ($user['userGroup'] > 4 && $exento == 0) {
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			$membertill = "<span class='mid'>$memberExpReadable</span>";
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	$membertill = "<span class='negative'>$memberExpReadable</span>";
		} else if (strtotime($memberExp) > strtotime($timeNow)) {
		  	$membertill = "<span class='positive'>$memberExpReadable</span>";
		}
		
	} else {
		
		$membertill = "<span class='white'>00-00-0000</span>";
		
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
   		$comment = "<img src='images/note.png' width='16' /><span style='display:none'>1</span>";
   		$comment = "
		                <img src='images/note.png' id='comment$noteid' /><div id='helpBox$noteid' class='helpBox'>{$row['note']}</div>
		                <script>
		                  	$('#comment$noteid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$noteid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$noteid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
	}
	
		// Dabulance customization
		if ($_SESSION['domain'] == 'dabulance' || $_SESSION['domain'] == 'royalgreen') {
			$linkVar = "profile.php";
		} else {
			$linkVar = "mini-profile.php";
		}
	  



		$langoperator = $_SESSION['lang'];
		$query = "SELECT $langoperator FROM usergroups WHERE userGroup =  {$user['userGroup']}";
		try
		{
			$resultU = $pdo->prepare("$query");
			$resultU->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowU = $resultU->fetch();
			$userGroupName = $rowU[$langoperator];
			


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
		                ->setCellValue('E'.$startIndex, date("d-m-Y",strtotime($user['registeredSince'])));		    
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $user['gender']);		    
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('G'.$startIndex, $age);		    
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('H'.$startIndex, $usageName);		    
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex, $user['groupName']);		    
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $memberExpDate);

           //
           	   /*$objDrawing = new PHPExcel_Worksheet_Drawing();
                            $objDrawing->setName('Customer Signature');
                            $objDrawing->setDescription('Customer Signature');
                            //Path to signature .jpg file
                            $signature = 'images/logo.png';    
                            $objDrawing->setPath($signature);
                           // $objDrawing->setOffsetX(10);                     //setOffsetX works properly
                          //  $objDrawing->setOffsetY(10);                     //setOffsetY works properly
                            $objDrawing->setCoordinates('K'.$startIndex);             //set image to cell
                          //  $objDrawing->setWidth(32);  
                            $objDrawing->setHeight(20);                     //signature height  
                            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());  //save*/
                        

           // 			
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('K'.$startIndex, $form1);		    
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('L'.$startIndex, $dniScan);
			if ($_SESSION['domain'] == 'londres' || $_SESSION['domain'] == 'paradox') {
			            $objPHPExcel->getActiveSheet()
			           				 ->setCellValue('M'.$startIndex, $cardid);
			}
		    $startIndex++; 
		   
  }
         ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
       	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	   
		//header('Location:bar-sales-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');

		echo "<h2>Please wait...</h2>";
		header('Refresh: 0; members-report.php?sort='.$_GET['sort'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
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