<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-no-warnings.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	/* DEBUG:
	$_SESSION['scanner'] = 2;
	$_SESSION['iPadReaders'] = 6;
	*/
	
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
	
		$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.paidUntil, u.userGroup, ug.groupName, u.credit, u.usageType FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND (u.first_name LIKE ('%$phrase%') OR u.last_name LIKE ('%$phrase%') OR u.oldNumber LIKE ('%$phrase%')) ORDER by u.memberno ASC";
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
	
			
		pageStart($lang['aval'], NULL, $validationScript, "psales", "dispensepre memberlist", $lang['member-newmembercaps'] . " - " . $lang['avalC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
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

while ($user = $results->fetch()) {
	
		$paidUntil = $user['paidUntil'];
		
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

	if ($user['userGroup'] > 4) {
		if ($memberExp == $timeNow) {
			$membertill = "<span class='mid'>$memberExpReadable</span>";
	  	} else if ($memberExp < $timeNow) {
		  	$membertill = "<span class='negative'>$memberExpReadable</span>";
		} else if ($memberExp > $timeNow) {
		  	$membertill = "<span class='positive'>$memberExpReadable</span>";
		}
	} else {
		$membertill = "<span class='white'>00-00-0000</span>";
	}
	
	if (isset($_GET['twoavals'])) {
		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow centered' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $user['usageType'], $user['user_id'], $membertill
		  );
		  
	} else {
		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow centered' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $user['usageType'], $user['user_id'], $membertill
		  );
		  
  	}
	  echo $user_row;
  }
  
  
		exit();
			
	}
		if ($_SESSION['domain'] == 'choko' || $_SESSION['domain'] == 'greenpoint') {		
			$_SESSION['newUserId'] = $_GET['user_id'];
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
		    
	        window.location.replace("reg-4.php?twoavals&user_id="+data);
	        
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
		    
	        window.location.replace("reg-4.php?user_id="+data);
	        
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
	

	// Query to look up users
	$selectUsers = "SELECT user_id, memberno, first_name, last_name, registeredSince, gender, day, month, year, paidUntil, userGroup, usageType FROM users WHERE memberno <> '0' AND userGroup < 6 ORDER by memberno ASC";
	
	if ($_SESSION['domain'] != 'choko') {
		
		try
		{
			$resultsX = $pdo3->prepare("$selectUsers");
			$resultsX->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
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

	pageStart($lang['avalista'], NULL, $memberScript, "psales", "dispensepre memberlist", $lang['member-newmembercaps'] . " - " . $lang['avalista'] . " #1", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	echo "<center>";
	if (isset($_GET['twoavals'])) {
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='reg-4.php?twoavals' method='POST'>";
	} else {
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='reg-4.php' method='POST'>";
	}
?>
	
<a class='cta red' href='reg-5.php?noAval' style='background-color: red;'>NO AVALISTA</a>
<?php 
if ($_SESSION['domain'] != 'royaldream') {
?>

 <h2><?php echo $lang['scan-chip']; ?>:</h2><br />

 <div id="overview">
   <input type="text" name="cardid" maxlength="20" autofocus placeholder="<?php echo $lang['global-scankeyfob']; ?>" /><br />
   <button name='oneClick' type="submit" style="display: none;"><?php echo $lang['global-select']; ?></button>
 </div> <!-- END OVERVIEW -->
 </form>
 <center><br /><br />
 <?php 
}
?>

 <h2><?php echo $lang['or-search']; ?>:</h2><br />
<form id="registerForm" action="" method="POST">
 <div id="overview">
  <input type="text" name="searchfield" autofocus placeholder="Numero, nombre o apellido" /><br /><br /><br />  
  <button type="submit">Buscar</button>
 </div> <!-- END OVERVIEW -->
</form>
</center>
 <br /><br />	
 <h2><?php echo $lang['or-choose-list']; ?>:</h2><br />

</center>

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

		while ($user = $resultsX->fetch()) {
	
		$paidUntil = $user['paidUntil'];
		
		if ($user['usageType'] == '1') {
			$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
		} else {
			$usageType = '';
		}
		
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

	if ($user['userGroup'] > 4) {
		if ($memberExp == $timeNow) {
			$membertill = "<span class='mid'>$memberExpReadable</span>";
	  	} else if ($memberExp < $timeNow) {
		  	$membertill = "<span class='negative'>$memberExpReadable</span>";
		} else if ($memberExp > $timeNow) {
		  	$membertill = "<span class='positive'>$memberExpReadable</span>";
		}
	} else {
		$membertill = "<span class='white'>00-00-0000</span>";
	}
	
	if (isset($_GET['twoavals'])) {
		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow centered' href='reg-4.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d&twoavals'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $usageType, $user['user_id'], $membertill
		  );
		  
	} else {
		
		$user_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow centered' href='reg-4.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='reg-4.php?user_id=%d'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $usageType, $user['user_id'], $membertill
		  );
		  
  	}
	  echo $user_row;
  }
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
	
