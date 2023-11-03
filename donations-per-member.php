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
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1 FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' AND u.userGroup < 6 ORDER by u.memberno ASC";
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
	
		
	
		
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "currency"
					}
				}
			}); 
		
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart($lang['global-donations'], NULL, $memberScript, "pmembership", NULL, $lang['global-donations'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<center>
 <a href="#" id="xllink"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th>Aportado</th>
	   </tr>
	  </thead>
	  <tbody>
	  <?php
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
		            ->setCellValue('D1','Aportado');
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
	?>
	  <?php
 $startIndex = 2;  
		while ($user = $results->fetch()) {
	

	$query = "SELECT SUM(amount) FROM donations WHERE userid = {$user['user_id']}";
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
	
		$rowD = $result->fetch();
		$donated = $rowD['SUM(amount)'];
	
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.2f {$_SESSION['currencyoperator']}</td></tr>",
	  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], $donated);
  	  	  // KONSTANT CODE UPDATE BEGIN
	  		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $user['memberno']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex,  $user['first_name']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $user['last_name']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $donated." ".$_SESSION['currencyoperator']);		    
		    $startIndex++; 
}

 if(isset($_GET['action'])){
    ob_end_clean();
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename='.$lang['global-donations'].'.xlsx');
    header("Content-Type: application/download");
    //header('Cache-Control: max-age = 0');
    $objWriter->save('php://output');
    header("location:donations-per-member.php");  // KONSTANT CODE UPDATE END 
    die;
}
	  
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
<script type="text/javascript">
	$("#xllink").click(function(){
	    $("#load").show();
	    window.location.href = "donations-per-member.php?action=xls"; 
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);     
	 });
</script>	 
