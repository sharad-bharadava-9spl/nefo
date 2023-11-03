<?php
	ob_start();
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	require_once  'PHPExcel/Classes/PHPExcel.php';

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Lokesh Nayak")
		                             ->setLastModifiedBy("Lokesh Nayak")
		                             ->setTitle("Test Document")
		                             ->setSubject("Test Document")
		                             ->setDescription("Test document for PHPExcel")
		                             ->setKeywords("office")
		                             ->setCategory("Test result file");

	  
	try	{
		$dbh = new PDO('mysql:host=127.0.0.1:3306;', 'root', 'uqgj5nif5OqjtO3z');
 		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$dbh->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
		
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
 		
	}

$rows=array();
$page_size = 10;
$count = $_GET['totalCount'];
$fileExcel = "inv.xlsx";	


if($_GET['count'] == 0){
	// excel count query
	$excelCountQuery  =   "SELECT schema_name FROM information_schema.schemata";
   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
   $countResults= $dbh->prepare("$excelCountQuery");
   $countResults->execute();

	$total_records=$countResults->rowCount();

    $count=ceil($total_records/$page_size);

	$filepath = "excel/".$fileExcel;

	if(file_exists($filepath)){
		unlink($filepath);
	}
	//$count =4;
}	
	if($_GET['count'] == 0){
        $objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1',"#");
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1','Club');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1','City');
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1','Province');
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1', 'Fsales');
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1', 'Sales');
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1','TBI');
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1','Log');
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1','Last Log');
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1','Last Login');
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1','Launched');
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);

	}
$countItem = $_GET['count'];

if($_GET['count'] <= $_GET['totalCount']){
	$offset_var = $countItem * $page_size;
	$sql = $dbh->query('SELECT schema_name FROM information_schema.schemata Limit '.$page_size.' OFFSET '.$offset_var);
	$sql->execute();
	$startIndex = 2; 

	if(file_exists("excel/$fileExcel")){
   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
   	}

while($getAllDbs = $sql->fetch()){
		$database = $getAllDbs['schema_name'];
	
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_irena' && $database != 'ccs_masterdb' && $database != 'ccs_andyclub1' && $database != 'ccs_andyclub2' && $database != 'ccs_andysclub3' && $database != 'ccs_andyshouse' && $database != 'ccs_berryscorner' && $database != 'ccs_berryshole' && $database != 'ccs_ccstest' && $database != 'ccs_demo' && $database != 'ccs_demo1' && $database != 'ccs_demo2' && $database != 'ccs_demo3' && $database != 'ccs_demo4' && $database != 'ccs_demo5' && $database != 'ccs_demo6' && $database != 'ccs_demo7' && $database != 'ccs_g13viejo' && $database != 'ccs_iuhhfisud' && $database != 'ccs_jazzhuset' && $database != 'ccs_jazzhusetnano' && $database != 'ccs_kjell' && $database != 'ccs_weedbunny' && $database != 'ccs_testtest' && $database != 'ccs_levelclub' && $database != 'ccs_levelclub1' && $database != 'ccs_levelclub2' && $database != 'ccs_lokeshtest11' && $database != 'ccs_us' && $database != 'ccs_bettyboopold' && $database != 'ccs_monoloco2' && $database != 'ccs_gerry' && $database != 'ccs_demox' && $database != 'ccs_hazyhaus' && $database != 'ccs_pakalolos') {
		
	$domain = substr($database,4);
		
	$query = "SELECT db_pwd, customer FROM db_access WHERE domain = '$domain'";
		try
		{
			$resultA = $pdo->prepare("$query");
			$resultA->execute();
			$data = $resultA->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($data) {

			$rowA = $data[0];
				$db_pwd = $rowA['db_pwd'];
				$number = $rowA['customer'];
				
		$query = "SELECT city, state, launchdate FROM customers WHERE number = '$number'";
		try
		{
			$resultB = $pdo2->prepare("$query");
			$resultB->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowB = $resultB->fetch();
			$city = $rowB['city'];
			$state = $rowB['state'];
			$launchdate = $rowB['launchdate'];


	$db_name = "ccs_" . $domain;
	$db_user = $db_name . "u";

		try	{
	 		$pdo4 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo4->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo4->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
	
	 		echo $output;
	 		exit();
		}	   		
		
		$selectUsersU = "SELECT COUNT( DISTINCT userid )
FROM f_sales
WHERE DATE(saletime)
BETWEEN DATE('2022-04-01')
AND DATE('2022-04-30');";
		
		try
		{
			$result1 = $pdo4->prepare("$selectUsersU");
			$result1->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row1 = $result1->fetch();
			$fsales = $row1['COUNT( DISTINCT userid )'];
			
		$selectUsersU = "SELECT COUNT( DISTINCT userid )
FROM sales
WHERE DATE(saletime)
BETWEEN DATE('2022-04-01')
AND DATE('2022-04-30');";
		
		try
		{
			$result2 = $pdo4->prepare("$selectUsersU");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row2 = $result2->fetch();
			$sales = $row2['COUNT( DISTINCT userid )'];
			
		if ($fsales > $sales) {
			$opr = 'Check';
		} else if ($sales > 500) {
			$opr = '500';
		} else {
			$opr = $sales;
		}
		
		$selectUsersU = "SELECT COUNT( DISTINCT id )
FROM log
WHERE DATE(logtime)
BETWEEN DATE('2022-04-01')
AND DATE('2022-04-30');";
		
		try
		{
			$result3 = $pdo4->prepare("$selectUsersU");
			$result3->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row3 = $result3->fetch();
			$log = $row3['COUNT( DISTINCT id )'];
			
		$selectUsersU = "SELECT logtime
FROM log
WHERE DATE(logtime)
BETWEEN DATE('2022-04-01')
AND DATE('2022-04-30') ORDER BY LOGTIME DESC LIMIT 1;";
		
		try
		{
			$result4 = $pdo4->prepare("$selectUsersU");
			$result4->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row4 = $result4->fetch();
			$lastLog = $row4['logtime'];
			
		$selectUsersU = "SELECT COUNT( DISTINCT userid )
FROM sales
WHERE DATE(saletime)
BETWEEN DATE('2022-04-01')
AND DATE('2022-04-30');";

		$query = "SELECT time FROM logins WHERE domain = '$domain' ORDER BY time DESC LIMIT 1";
		try
		{
			$result5 = $pdo->prepare("$query");
			$result5->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row5 = $result5->fetch();
			$lastLogin = $row5['time'];


		$objPHPExcel->getActiveSheet()
        			->setCellValue('A'.$startIndex, $number); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('B'.$startIndex, $database);
        $objPHPExcel->getActiveSheet()
        			->setCellValue('C'.$startIndex, $city); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('D'.$startIndex, $state); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('E'.$startIndex, $fsales); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('F'.$startIndex, $sales); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('G'.$startIndex, $opr); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('H'.$startIndex, $log); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('I'.$startIndex, $lastLog); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('J'.$startIndex, $lastLogin); 
        $objPHPExcel->getActiveSheet()
        			->setCellValue('K'.$startIndex, $launchdate);
        
	   $startIndex++; 

}
	}
}
		ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
       	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		//$objWriterLoop = $objWriter.$excelIndex; 
	    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	   	// header('Content-type: application/vnd.ms-excel');
	   	// header('Content-Disposition: attachment;filename=dispenses.xlsx');
	    //header("Content-Type: application/download");
	    //header('Cache-Control: max-age = 0');
	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	    $redirectURL = 'inv-report.php?count='.$countItem.'&totalCount='.$count.'&redirect=1';
		 echo "<h2>Processing, please wait....</h2>";
		header('Refresh: 0; inv-report.php?count='.$countItem.'&totalCount='.$count.'&redirect=1');
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

