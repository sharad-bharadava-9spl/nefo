<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['searchfield'])) {
		
		$phrase = $_POST['searchfield'];
	
		$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.paidUntil, u.userGroup, ug.groupName, u.credit, u.usageType FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.userGroup < 6 AND (u.first_name LIKE ('%$phrase%') OR u.last_name LIKE ('%$phrase%') OR u.memberno LIKE ('%$phrase%')) ORDER by u.memberno ASC";
					
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
		
		
			
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			});
			
		});
EOD;

		pageStart($lang['title-dispense'], NULL, $memberScript, "pmembership", NULL, $lang['title-dispense'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>		
		
	 <table class="default" id="mainTable">
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['expiry']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $result->fetch()) {
	
		$paidUntil = $user['paidUntil'];
		$exento = $user['exento'];
		
		if ($user['usageType'] == '1') {
			$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
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
	

		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow centered' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $usageType, $user['user_id'], $membertill
		  );
		  
	  echo $user_row;
  }?>

	 </tbody>
	 </table>		
	
<?php

	} else {
	
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
	  $('#searchForm').validate({
		  rules: {
			  searchfield: {
				  required: true,
				  minlength: 3
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate

	  	$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			});
		    
});			
EOD;
		pageStart($lang['title-dispense'], NULL, $memberScript, "psales", "dispensepre", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
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

    $total_pages_sql = "SELECT COUNT(user_id) FROM users WHERE memberno <> '0' AND userGroup < 6";
	$rowCount = $pdo3->query("$total_pages_sql")->fetchColumn();
    
    $total_pages = ceil($rowCount / $no_of_records_per_page);
    
	// Query to look up users
	$selectUsers = "SELECT user_id, memberno, first_name, last_name, registeredSince, gender, day, month, year, paidUntil, userGroup, usageType, exento FROM users WHERE memberno <> '0' AND userGroup < 6 ORDER by memberno ASC LIMIT $offset, $no_of_records_per_page";
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

?>

 <div id='mainbox'>
  <div id='mainboxheader'>
  <?php echo $lang['add-aval-new']; ?>
  </div>
  
  <div class='twoboxessmall'>
   <div class='boxheader'>
    <?php echo $lang['scan-chip']; ?>
   </div>
   <form id="registerForm" action="new-dispense-2.php" method="POST">
   <input type="text" name="cardid" id="input1" maxlength="20" autofocus />
   <button name='oneClick' type="submit" style="display: none;"><?php echo $lang['global-select']; ?></button>
   </form>
  </div>
  <div class='twoboxessmall'>
   <div class='boxheader'>
    <?php echo $lang['or-search']; ?>
   </div>
<form id="registerForm" action="" method="POST">
  <input type="text" name="searchfield" id="input2" autofocus placeholder="Numero, nombre o apellido" />  
  <button type="submit" id="button2">Buscar</button>
   </form>
  </div>
 <br />

 <br /><br />	
<center>
	 <table class="default" id="mainTable">
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['expiry']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $result->fetch()) {
	
		$paidUntil = $user['paidUntil'];
		$exento = $user['exento'];
		
		if ($user['usageType'] == '1') {
			$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
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
	

		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow centered' href='new-dispense-2.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='new-dispense-2.php?user_id=%d'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $usageType, $user['user_id'], $membertill
		  );
		  
	  echo $user_row;
  }
?>

	 </tbody>
	 </table>
	 </center>
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
 </div>



<?php }
 displayFooter(); ?>
	
