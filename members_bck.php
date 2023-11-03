<?php
	ob_start();
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();

	?>
	<!-- KONSTANT CODE UPDATE BEGIN -->
	<style type="text/css">
		.ui-widget-header,.ui-state-default, ui-button {
            background:#b9cd6d !important;
            border: 1px solid #b9cd6d !important;
            color: #FFFFFF !important;
            font-weight: bold !important;
         }
         .ui-dialog .ui-dialog-titlebar-close {
			    top: 16% !important;
			}
		
		#load
		{
		   
		    display: none;
		    position : fixed;
		    z-index: 100;
		    background-image : url('images/loading-small.gif');
		    background-color:#666;
		    opacity : 0.4;
		    background-repeat : no-repeat;
		    background-position : center;
		    left : 0;
		    bottom : 0;
		    right : 0;
		    top : 0;
		}

	</style>
	<!-- KONSTANT CODE UPDATE END -->
	<?php
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
	    
	// Query to look up users
	// KONSTANT CODE UPDATE BEGIN
	if(isset($_GET['action']) && $_GET['list'] == 'full'){
		$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento, u.cardid FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup $filter $dateOperator ORDER by $sortby"; 
	}else{

	  $selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento, u.cardid FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup $filter $dateOperator ORDER by $sortby LIMIT $offset, $no_of_records_per_page"; 
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
	
		
	
		
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Socios",
			    filename: "Socios" //do not include extension
		
			  });
		
			});
		    
		    
		    
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
			

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
	 <div id="load">
	 </div>
<center>
	 <table id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
       		<!-- KONSTANT CODE UPDATE BEGIN -->
         <a href="javascript:void(0);" id="openCOnfirm"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
          <!-- KONSTANT CODE UPDATE END -->
       </td>
      </tr>
     </table>
<br />
	<!-- KONSTANT CODE UPDATE BEGIN -->
	<?php
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
if ($_SESSION['domain'] == 'londres') {
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('M1','Chip');
		$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);   
}
		// KONSTANT CODE UPDATE END

		// Dabulance customization
		if ($_SESSION['domain'] == 'dabulance') { ?>
 <a href="scan-profile.php" class="cta1">Search</a>
 <a href="new-member-0.php" class="cta1"><?php echo $lang['index-newmember']; ?></a>
 
<?php		}

	?>
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr>
	    <th class='centered'><a href="?sort=0<?php if ($sortorder == '0' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont">C</a></th>
	    <th class='centered'><a href="?sort=1<?php if ($sortorder == '1' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont">#</a></th>
	    <th><a href="?sort=2<?php if ($sortorder == '2' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['global-name']; ?></a></th>
	    <th><a href="?sort=3<?php if ($sortorder == '3' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['member-lastnames']; ?></a></th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><a href="?sort=4<?php if ($sortorder == '4' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['global-credit']; ?></a></th>
	    <th style='text-align: center;'><a href="?sort=5<?php if ($sortorder == '5' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont">*</a></th>
<?php } ?>
	    <th><a href="?sort=6<?php if ($sortorder == '6' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['global-registered']; ?></a></th>
	    <th><a href="?sort=7<?php if ($sortorder == '7' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['member-gender']; ?></a></th>
	    <th><a href="?sort=8<?php if ($sortorder == '8' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['age']; ?></a></th>
	    <th><a href="?sort=9<?php if ($sortorder == '9' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['global-type']; ?></a></th>
	    <th><a href="?sort=10<?php if ($sortorder == '10' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['member-group']; ?></a></th>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
	    <th><a href="?sort=11<?php if ($sortorder == '11' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>&filter=<?php echo $_GET['filter']; ?>&period=<?php echo $_GET['period']; ?>&limit=<?php echo $_GET['limit']; ?>" class="greenFont"><?php echo $lang['expiry']; ?></a></th>
<?php } ?>
	    <th class='centered'><?php echo $lang['signature']; ?></th>
	    <th><?php echo $lang['dni-scan']; ?></th>
<?php if ($_SESSION['domain'] == 'londres') { ?>
	    <th>Chip</th>
<?php } ?>
	    <!--<th style='color: red;'><?php echo $lang['old-number']; ?></th>-->
	    <th class='noExl'><?php echo $lang['global-comment']; ?></th>
	    <th class='noExl'><?php echo $lang['global-edit']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	   // KONSTANT CODE UPDATE BEGIN 
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
	$cardid = $user['cardid'];
	
	
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
	$object_exist2 = object_exist($google_bucket, $google_root_folder.$file2);

	if ($object_exist2 === false) {
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
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>",
	  $user['user_id'], $userStar, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRow right' href='$linkVar?user_id=%d'>%0.1f {$_SESSION['currencyoperator']}</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>",
  	  $user['user_id'], $user['credit'], $user['user_id'], $creditEligible);
  	  
}

	echo sprintf("
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%d</td>
  	   <td class='clickableRow' style='text-align: center;' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $user['gender'], $user['user_id'], $age, $user['user_id'], $usageType, $user['user_id'], $user['groupName']);

if ($_SESSION['membershipFees'] == 1) {
	  
	echo sprintf("<td class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>",
   $paidClass, $user['user_id'], $membertill);
	    
}

  	   
// OLD NUMBER EXCLUDED:

if ($_SESSION['domain'] == 'londres') {
	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow' href='$linkVar?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan, $user['user_id'], $cardid);
	  
} else {
	
	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan);
	  
}


/* OLD NUMBER INCLUDED:
	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>
  	   <td style='text-align: center; color: red;' class='clickableRow' href='$linkVar?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan, $user['user_id'], $oldNumber);
*/

  	
	echo sprintf("
  	   <td style='text-align: center;' class='clickableRow noExl' href='profile.php?user_id=%d&openComment'><span class='relativeitem'>%s</span></td>
  	   <td style='text-align: center;' class='noExl'><a href='edit-profile.php?user_id=%d'><img src='images/edit.png' height='15' title='Edit user' /></a></td>
	  </tr>",
	  $user['user_id'], $comment, $user['user_id']
	  );
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
if ($_SESSION['domain'] == 'londres') {
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('M'.$startIndex, $cardid);
}
		    $startIndex++; 
		   
  }
  if(isset($_GET['action'])){
    ob_end_clean();
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $exportname . '.xlsx"');
    header("Content-Type: application/download");
    //header('Cache-Control: max-age = 0');
    $objWriter->save('php://output');
    header("location:members.php");  // KONSTANT CODE UPDATE END 
    die;
}?>

	 </tbody>
	 </table>
	 </center>
	 <!-- KONSTANT CODE UPDATE BEGIN -->
	  		<div  class="actionbox-npr" id = "dialog-3" title = "<?php echo $lang['dialog-title']; ?>">
     			
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
<!-- KONSTANT CODE UPDATE BEGIN -->
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
    window.location.href = "members.php?action=xls&list=full"; 
    setTimeout(function () {
        $("#load").hide();
    }, 2000);     
 });
 $("#currentList").click(function(){
    $("#load").show();
    $( "#dialog-3" ).dialog( "close" );
    window.location.href = "members.php?action=xls&list=screen&pageno=<?php echo $_GET['pageno']?>&sort=<?php echo $_GET['sort'] ?>&filter=<?php echo $_GET['filter'] ?>&period=<?php echo $_GET['period'] ?>&limit=<?php echo $_GET['limit'] ?>"; 
    setTimeout(function () {
        $("#load").hide();
    }, 1000);     
    
 });
</script>
<!-- KONSTANT CODE UPDATE END  -->
<?php  displayFooter(); ?>
