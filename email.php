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
	
	foreach ($_GET as $name => $value) {
		if ($name != 'pageno') {
	    	$sortparam .= '&amp;' . $name . '=' . $value;	    	
    	}
	}
	
	// if sort order is set
	if (isset($_GET['sort']) && $_GET['sort'] == '') {
		
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
				$sortby = 'u.email DESC';
			} else {
				$sortby = 'u.email ASC';
			}
		} else if ($sortorder == 12) {
			if ($_GET['order'] == 'desc') {
				$sortby = 'u.telephone DESC';
			} else {
				$sortby = 'u.telephone ASC';
			}
		}else{
			$sortorder = 'a';
			$sortby = 'u.memberno';
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

    $total_pages_sql = "SELECT COUNT(*) FROM users WHERE userGroup <> 8";
	$rowCount = $pdo3->query("$total_pages_sql")->fetchColumn();
    
    $total_pages = ceil($rowCount / $no_of_records_per_page);

	    
	// Query to look up users
	if(isset($_GET['list']) && $_GET['list'] == 'full'){
		$selectUsers = "SELECT u.memberno, u.first_name, u.last_name, ug.groupName, u.email, u.telephone, u.starCat FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.userGroup <> 8 ORDER by $sortby";
	}else{
		$selectUsers = "SELECT u.memberno, u.first_name, u.last_name, ug.groupName, u.email, u.telephone, u.starCat FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.userGroup <> 8 ORDER by $sortby LIMIT $offset, $no_of_records_per_page";
	}

	try
	{
		$result = $pdo3->prepare("$selectUsers");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
		
	$tableScript = <<<EOD
	
	    $(document).ready(function() {
		    
			$('#cloneTable').width($('#mainTable').width());
			
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
	});

EOD;

		
	
	pageStart($lang['title-members'], NULL, $tableScript, "pusers", "memberlist export-dialog", $lang['member-memberemails'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<div id="load"></div>
	 <?php
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1', 'C');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1','#');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['global-name']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1', $lang['member-lastnames']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['member-group']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['member-email']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['member-telephone']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
	?>
<center><a href="javascript:void(0);" id="openCOnfirm" ><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>     <br />
	 <table class="default" id="mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th class='centered'><a href="?sort=0<?php if ($sortorder == '0' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont">C</a></th>
	    <th class='centered'><a href="?sort=1<?php if ($sortorder == '1' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont">#</a></th>
	    <th><a href="?sort=2<?php if ($sortorder == '2' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['global-name']; ?></a></th>
	    <th><a href="?sort=3<?php if ($sortorder == '3' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['member-lastnames']; ?></a></th>
	    <th><a href="?sort=10<?php if ($sortorder == '10' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['member-group']; ?></a></th>
	    <th><a href="?sort=11<?php if ($sortorder == '11' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['member-email']; ?></th>
	    <th><a href="?sort=12<?php if ($sortorder == '12' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['member-telephone']; ?></th>
	   </tr>
	  </thead>
	  
	  <?php
$startIndex = 2;
while ($user = $result->fetch()) {

	$starCat = $user['starCat'];
	
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
	
	$user_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
	  </tr>",
	  $userStar, $user['memberno'], $user['first_name'], $user['last_name'], $user['groupName'], $user['email'], $user['telephone']
	  );
	  echo $user_row;
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
           				 ->setCellValue('E'.$startIndex, $user['groupName']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $user['email']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, $user['telephone']); 
		    $startIndex++; 
  }
  if(isset($_GET['action'])){
    ob_end_clean();
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename=email.xlsx');
    header("Content-Type: application/download");
    //header('Cache-Control: max-age = 0');
    $objWriter->save('php://output');
    header("location:email.php");  // KONSTANT CODE UPDATE END 
    die;
}
?>

	 </tbody>
	 </table>

	 	<!-- KONSTANT CODE UPDATE BEGIN -->
		<div  class="actionbox-npr" id = "dialog-3" title = "<?php echo $lang['member-memberemails']; ?>">
			
			<div class='boxcomtemt'>
				<p><?php echo $lang['dialog-text']; ?></p><br>
 			<button class='cta1' id="fullList"><?php echo $lang['dialog-button-full']; ?></button>
 			<button class='cta1' id="currentList"><?php echo $lang['dialog-button-current']; ?></button>
			</div>
		</div>
<!-- KONSTANT CODE UPDATE END -->

<!-- Pagination code BEGIN -->
<style>
a.pagination {
	display: inline-block;
	background-color: #eee;
	border: 1px solid #ccc;
	width: 50px;
	height: 50px;
	line-height: 50px;
	margin: 5px;
	color: #333;
}
a.pagination.disabled {
	background-color: #ccc;
	border: 1px solid #aaa;
}
</style>
<center>
<br />
<a href="?pageno=1<?php echo $sortparam; ?>" class='pagination <?php if ($pageno == 1 || (!isset($_GET['pageno']))) { echo 'disabled'; } ?>'>&laquo;</a>
<a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } echo $sortparam; ?>" class='pagination <?php if($pageno <= 1){ echo 'disabled'; } ?>'>Prev</a>
<a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } echo $sortparam; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>Next</a>
<a href="?pageno=<?php echo $total_pages; echo $sortparam; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>&raquo;</a>
</center>
<!-- Pagination code END -->

<?php  displayFooter(); ?>
<script type="text/javascript">

	$( "#dialog-3" ).dialog({
	    autoOpen: false, 
	    hide: "puff",
	    show : "slide",
	     position: {
	       my: "top top",
	       at: "top top"
	    }      
	 });
	 $( "#openCOnfirm" ).click(function() {
	    $( "#dialog-3" ).dialog( "open" );
	 });

 $("#fullList").click(function(){
    $("#load").show();
    $( "#dialog-3" ).dialog( "close" );
    var sort = "<?php echo $sortorder ?>";
    var order = "<?php echo $_GET['order'] ?>";
    window.location.href = "email.php?action=xls&list=full&sort="+sort+"&order="+order; 
    setTimeout(function () {
        $("#load").hide();
    }, 2000);     
 });

 $("#currentList").click(function(){
    $("#load").show();
    $( "#dialog-3" ).dialog( "close" );
    var pageno = "<?php echo $pageno; ?>";
    var sort = "<?php echo $sortorder; ?>";
    var order = "<?php echo $_GET['order'] ?>";
    window.location.href = "email.php?action=xls&list=screen&pageno="+pageno+"&sort="+sort+"&order="+order; 
    setTimeout(function () {
        $("#load").hide();
    }, 1000);     
    
 });

</script>	
