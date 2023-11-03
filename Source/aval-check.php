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
	
	if ($_SESSION['iPadReaders'] > 0) {
		
		// Delete old leftover scans
		$deleteScans = "DELETE FROM newscan WHERE type = '{$_SESSION['scanner']}'";
		try
		{
			$result = $pdo3->prepare("$deleteScans")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
	}
		
	if (isset($_POST['searchfield'])) {
		
		$phrase = $_POST['searchfield'];
	
		$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.paidUntil, u.userGroup, ug.groupName, u.credit, u.usageType, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND (u.first_name LIKE ('%$phrase%') OR u.last_name LIKE ('%$phrase%') OR u.memberno LIKE ('%$phrase%')) ORDER by u.memberno ASC";
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
	
		
		pageStart($lang['aval'], NULL, $validationScript, "aval1", "dispensepre memberlist", $lang['member-newmembercaps'] . " - " . $lang['avalC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>
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
	
	if (isset($_GET['twoavals'])) {
		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow centered' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $user['usageType'], $user['user_id'], $membertill
		  );
		  
	} else {
		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow centered' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $user['usageType'], $user['user_id'], $membertill
		  );
		  
  	}
	  echo $user_row;
  }
  
  	echo "</tbody></table></center>";
  
		exit();
			
	}
		
	if ($_SESSION['iPadReaders'] > 0) {
		
		if (isset($_GET['twoavals'])) {
			
	echo <<<EOD
<script>
setInterval(function()
{ 
    $.ajax({
      type:"post",
      url:"scansearch-aval.php",
      datatype:"text",
      success:function(data)
      {
        if( data == 'false' ) {

	    } else if ( data == 'notregistered' ) {
		    
	        window.location.replace("aval-check.php?twoavals&notregistered");
		    
	    } else {
		    
	        window.location.replace("aval-details.php?twoavals&user_id="+data);
	        
	    }
      }
    });
}, 500);
</script>
EOD;
			
		} else {
	
	echo <<<EOD
<script>
setInterval(function()
{ 
    $.ajax({
      type:"post",
      url:"scansearch-aval.php",
      datatype:"text",
      success:function(data)
      {
        if( data == 'false' ) {

	    } else if ( data == 'notregistered' ) {
		    
	        window.location.replace("aval-check.php?notregistered");
		    
	    } else {
		    
	        window.location.replace("aval-details.php?user_id="+data);
	        
	    }
      }
    });
}, 500);
</script>
EOD;

		}

	}
	
		if (isset($_GET['notregistered'])) {
			
			$_SESSION['errorMessage'] = $lang['error-keyfob'];
				
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
	
		
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
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
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 

		});
		
EOD;

	pageStart($lang['avalista'], NULL, $memberScript, "aval1", "dispensepre memberlist", $lang['member-newmembercaps'] . " - " . $lang['avalista'] . " #1", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

	if (isset($_GET['twoavals'])) {
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='aval-details.php?twoavals' method='POST'>";
	} else {
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='aval-details.php' method='POST'>";
	}
?>
	

<div id='progress'>
 <div id='progressinside1'>
 </div>
</div>
<br />
 <div id='progresstext1'>
 1. <?php echo $lang['avalista']; ?>
 </div>
 
 <div id='mainbox'>
  <div id='mainboxheader'>
  <?php echo $lang['add-aval-new']; ?>
  </div>
  
  <div class='threeboxes'>
   <div class='boxheader'>
    <?php echo $lang['scan-chip']; ?>
   </div>
   
   <input type="text" name="cardid" id="input1" maxlength="20" autofocus />
   <button name='oneClick' type="submit" style="display: none;"><?php echo $lang['global-select']; ?></button>
   </form>
  </div>
  <div class='threeboxes'>
   <div class='boxheader'>
    <?php echo $lang['or-search']; ?>
   </div>
<form id="registerForm" action="" method="POST">
  <input type="text" name="searchfield" id="input2" autofocus placeholder="Numero, nombre o apellido" />  
  <button type="submit" id="button2">Buscar</button>
   </form>
  </div>
  <div class='threeboxes'>
   <div class='boxheader'>
    <?php echo $lang['or-finger']; ?>
   </div>
<form id="registerForm" action="" method="POST">
   <input type="text" name="cardid" maxlength="20" id="input3"  />
   <button name='oneClick' type="submit" style="display: none;"><?php echo $lang['global-select']; ?></button>
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
	
	if (isset($_GET['twoavals'])) {
		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow centered' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $usageType, $user['user_id'], $membertill
		  );
		  
	} else {
		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow centered' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $usageType, $user['user_id'], $membertill
		  );
		  
  	}
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



<?php  displayFooter(); ?>
	
