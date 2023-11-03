
<?php
	// created by konstant for Task-15077699 on 12-04-2022
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
	getSettings();	
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
	// Query to look up users
	// $selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' AND u.userGroup < 6 ORDER by u.memberno ASC LIMIT 1000";
	
	// Check if 'entre fechas' was utilised
	if (isset($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
			
	} else {
		
		$fromDate = date("Y-m-d");
		$untilDate = date("Y-m-d");
				
	}

	$timeLimit = "DATE(d.donationTime) BETWEEN '$fromDate' AND '$untilDate 23:59:59'";
	$timeLimit2 = "DATE(donationTime) BETWEEN '$fromDate' AND '$untilDate 23:59:59'";
	$timeLimit3 = "DATE(saletime) BETWEEN '$fromDate' AND '$untilDate 23:59:59'";


   	$rows=array();
		$page_size=1000;
		$count = $_GET['totalCount'];
		$domain = $_SESSION['domain'];
		$fileExcel = $domain."-donation-vs-dispenses.xlsx";

		if($_GET['count'] == 0){
				// excel count query
				$excelCountQuery  =   "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, donations d WHERE u.user_id = d.userid AND u.memberno <> '0' AND u.userGroup < 6 AND $timeLimit GROUP BY u.user_id ORDER by u.memberno ASC";
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
	

	
	
	
	$nowDate = date('d-m-Y');
if($_GET['count'] == 0){
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
		            ->setCellValue('E1',$lang['global-donations']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['bar']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['dispensary']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1',$lang['global-delta']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',"Donation Date");
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);  
	}
	
$countItem = $_GET['count'];

if($_GET['count'] <= $_GET['totalCount']){		
	$offset_var = $countItem * $page_size;
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, donations d WHERE u.user_id = d.userid AND u.memberno <> '0' AND u.userGroup < 6 AND $timeLimit GROUP BY u.user_id ORDER by u.memberno ASC Limit ".$page_size." OFFSET ".$offset_var;
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
	
	
				$starCat = $user['starCat'];
				$oldNumber = $user['oldNumber'];
				$user_id = $user['user_id'];

				$selectDonation = "SELECT donationTime FROM donations WHERE userid = $user_id";
				try
				{
					$dresult = $pdo3->prepare("$selectDonation");
					$dresult->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$drow = $dresult->fetch();

				$donationTime = date("d-m-Y H:i:s", strtotime($drow['donationTime']));
				
				$donatedQuery = "SELECT SUM(amount) FROM donations WHERE $timeLimit2 AND userid = $user_id";
					try
					{
						$result = $pdo3->prepare("$donatedQuery");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
					$donated = $row['SUM(amount)'];

				$dispensedQuery = "SELECT SUM(amount) FROM sales WHERE $timeLimit3 AND userid = $user_id";
					try
					{
						$result = $pdo3->prepare("$dispensedQuery");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
					$dispensed = $row['SUM(amount)'];

				$barQuery = "SELECT SUM(amount) FROM b_sales WHERE $timeLimit3 AND userid = $user_id";
					try
					{
						$result = $pdo3->prepare("$barQuery");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
					$bar = $row['SUM(amount)'];

				
				if ($starCat == 1) {
			   		$userStar = "<img src='images/star-yellow.png' width='16' /><span style='display:none'>1</span>";
				} else if ($starCat == 2) {
			   		$userStar = "<img src='images/star-black.png' width='16' /><span style='display:none'>2</span>";
				} else if ($starCat == 3) {
			   		$userStar = "<img src='images/star-green.png' width='16' /><span style='display:none'>3</span>";
				} else if ($starCat == 4) {
			   		$userStar = "<img src='images/star-red.png' width='16' /><span style='display:none'>4</span>";
				} else {
			   		$userStar = "<span style='display:none'>0</span>";
				}
				
				if ($donated > 0) {

								// KONSTANT CODE UPDATE BEGIN
				  		$objPHPExcel->getActiveSheet()
					                ->setCellValue('A'.$startIndex, $starCat);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('B'.$startIndex, $user['memberno']);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('C'.$startIndex,  $user['first_name']);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('D'.$startIndex, $user['last_name']); 
			            $objPHPExcel->getActiveSheet()
			            			->setCellValue('E'.$startIndex, sprintf("%0.02f", $donated).$currencyoperator); 
			            $objPHPExcel->getActiveSheet()
			           				 ->setCellValue('F'.$startIndex, sprintf("%0.02f",$bar).$currencyoperator); 
			            $objPHPExcel->getActiveSheet()
			            			->setCellValue('G'.$startIndex, sprintf("%0.02f", $dispensed).$currencyoperator); 
			            $objPHPExcel->getActiveSheet()
			            			->setCellValue('H'.$startIndex, sprintf("%0.02f", $donated - $bar - $dispensed).$currencyoperator);
			            $objPHPExcel->getActiveSheet()
			            			->setCellValue('I'.$startIndex, $donationTime);		    		    
					    $startIndex++; 
				  	  
			  	  }
			 }

		ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	    $redirectURL = 'donated-dispensed-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1';
	    echo "<h2>Processing, please wait....</h2>";
		header('Refresh: 0; donated-dispensed-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
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

