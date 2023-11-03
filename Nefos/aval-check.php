<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	// Query to look up users
	$selectUsers = "SELECT user_id, memberno, first_name, last_name, registeredSince, gender, day, month, year, paidUntil, userGroup, usageType FROM users WHERE memberno <> '0' AND userGroup < 6 ORDER by memberno ASC";
	
		
	$result = mysql_query($selectUsers)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		
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
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='aval-details.php?twoavals' method='POST'>";
	} else {
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='aval-details.php' method='POST'>";
	}
?>
	


 <h2><?php echo $lang['scan-chip']; ?>:</h2><br />

 <div id="overview">
   <input type="text" name="cardid" maxlength="20" autofocus placeholder="<?php echo $lang['global-scankeyfob']; ?>" /><br />
   <button name='oneClick' type="submit" style="display: none;"><?php echo $lang['global-select']; ?></button>
 </div> <!-- END OVERVIEW -->
 <br /><br />	
 <h2><?php echo $lang['or-choose-list']; ?>:</h2><br />

</form>
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

while ($user = mysql_fetch_array($result)) {
	
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
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d&twoavals'>%s</td>
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
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='aval-details.php?user_id=%d'>%s</td>
		  </tr>",
		  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-y",strtotime($user['registeredSince'])), $user['user_id'], $user['usageType'], $user['user_id'], $membertill
		  );
		  
  	}
	  echo $user_row;
  }
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
	
