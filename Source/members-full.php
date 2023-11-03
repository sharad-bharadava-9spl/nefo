<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();	
	
	foreach ($_GET as $name => $value) {
		if ($name != 'pageno') {
	    	$sortparam .= '&amp;' . $name . '=' . $value;	    	
    	}
	}
	
	// if sort order is set
	if (isset($_GET['sort'])) {
		
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
	if (isset($_GET['pageno'])) {
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
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.exento, u.starCat, u.nationality FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' AND u.userGroup < 6 ORDER by $sortby LIMIT $offset, $no_of_records_per_page";
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
			    exclude_img: false,
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
			
EOD;

	if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "currency"
					},
					5: {
						sorter: "dates"
					},
					10: {
						sorter: "dates"
					},
					14: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 0) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "currency"
					},
					5: {
						sorter: "dates"
					},
					13: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 0 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					8: {
						sorter: "dates"
					},
					12: {
						sorter: "dates"
					}

				}
			}); 

		
EOD;
		
	} else {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	}
	
	$memberScript .= <<<EOD
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
	 <table id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'><a href="?sort=0<?php if ($sortorder == '0' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont">C</a></th>
	    <th class='centered'><a href="?sort=1<?php if ($sortorder == '1' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont">#</a></th>
	    <th><a href="?sort=2<?php if ($sortorder == '2' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['global-name']; ?></a></th>
	    <th><a href="?sort=3<?php if ($sortorder == '3' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['member-lastnames']; ?></a></th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><a href="?sort=4<?php if ($sortorder == '4' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['global-credit']; ?></a></th>
	    <th style='text-align: center;'><a href="?sort=5<?php if ($sortorder == '5' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont">*</a></th>
<?php } ?>
	    <th><a href="?sort=6<?php if ($sortorder == '6' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['global-registered']; ?></a></th>
	    <th><a href="?sort=7<?php if ($sortorder == '7' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['member-gender']; ?></a></th>
	    <th><a href="?sort=8<?php if ($sortorder == '8' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['age']; ?></a></th>
	    <th><a href="?sort=9<?php if ($sortorder == '9' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['global-type']; ?></a></th>
	    <th><a href="?sort=10<?php if ($sortorder == '10' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['member-group']; ?></a></th>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
	    <th><a href="?sort=11<?php if ($sortorder == '11' && $_GET['order'] != 'desc') { echo '&order=desc'; } ?>" class="greenFont"><?php echo $lang['expiry']; ?></a></th>
<?php } ?>
	    <th class='centered'><?php echo $lang['signature']; ?></th>

	    <th class='noExl'><?php echo $lang['dni-scan']; ?></th>
	    <th class='centered'>DNI #</th>
	    <th>Nac.</th>
	    <th>Nacionalidad</th>
	    <th class='noExl'><?php echo $lang['global-comment']; ?></th>
	    <th class='noExl'><?php echo $lang['global-edit']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {
// Calculate Age:
	$day = $user['day'];
	$month = $user['month'];
	$year = $user['year'];
	$paidUntil = $user['paidUntil'];
	$exento = $user['exento'];
	$starCat = $user['starCat'];
	$nationality = $user['nationality'];
	
	
	
$bdayraw = $day . "." . $month . "." . $year;
$bday = new DateTime($bdayraw);
$today = new DateTime(); // for testing purposes
$diff = $today->diff($bday);
$age = $diff->y;

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
	
	if (!file_exists($file2) || filesize($file2) == 0) {
		$sigFile = $lang['global-no'];
		$form1colour = 'negative';
	} else {
		$sigFile = "<a href='images/_" . $_SESSION['domain'] . "/sigs/{$user['user_id']}.png'><img src='images/_" . $_SESSION['domain'] . "/sigs/{$user['user_id']}.png' height='40' /></a>";
		$form1colour = '';
	}

	if ($user['usageType'] == 'Medicinal') {
		$usageType = "<img src='images/medical.png' width='16' />";
	} else {
		$usageType = '';
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
	}
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $userStar, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.1f &euro;</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], $user['credit'], $user['user_id'], $creditEligible);
  	  
}

	echo sprintf("
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%d</td>
  	   <td class='clickableRow' style='text-align: center;' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $user['gender'], $user['user_id'], $age, $user['user_id'], $usageType, $user['user_id'], $user['groupName']);

if ($_SESSION['membershipFees'] == 1) {
	  
	echo sprintf("<td class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>",
   $paidClass, $user['user_id'], $membertill);
	    
}

  	   
	echo sprintf("
	   <td style='text-align: center;' class='noExl %s'>%s</td>
  	   <td style='text-align: center;' class='noExl clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
	  $form1colour, $sigFile, $dnicolour, $user['user_id'], $dniScan, $user['user_id'], $user['dni'], $user['user_id'], date("d-m-Y", strtotime($day . "-" . $month . "-" . $year)), $user['user_id'], $nationality);

  	
	echo sprintf("
  	   <td style='text-align: center;' class='clickableRow noExl' href='profile.php?user_id=%d&openComment'>%s</td>
  	   <td style='text-align: center;' class='noExl'><a href='edit-profile.php?user_id=%d'><img src='images/edit.png' height='15' title='Edit user' /></a></td>
	  </tr>",
	  $user['user_id'], $comment, $user['user_id']
	  );
	  
  }
?>

	 </tbody>
	 </table>
	 
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
