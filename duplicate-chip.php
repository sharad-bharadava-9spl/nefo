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
	
	if ($_POST['newScan'] == 'yes') {
		
		foreach($_POST as $key => $value) {
			
		    $pos = strpos($key , "newchip");
  			if ($pos === 0) {
	  			
	  			$uid = substr($key,7);
	  		
		  		if ($value != '') {
			  		
					try
					{
						$result = $pdo3->prepare("UPDATE users SET cardid = '$value' WHERE user_id = '$uid'");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					
					$_SESSION['successMessage'] = $lang['new-chip-assigned'];
					
		    	}
	    	}
		  
		}
	}
	
	$cardid = $_GET['cardid'];
	
	try
	{
		$result = $pdo3->prepare("SELECT user_id, memberno, first_name, last_name, userGroup, cardid, starCat FROM users WHERE cardid = '$cardid'");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$deleteDonationScript = <<<EOD
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
function delete_chip(cardid,userid) {
	if (confirm("{$lang['chip-deleteconfirm']}")) {
				window.location = "uTil/delete-chip.php?cardid=" + cardid + "&user_id=" + userid;
				}
}
EOD;
	
		pageStart("CCS", NULL, $deleteDonationScript, "pindex", "notSelected", $lang['chip-duplicated'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="" autocomplete="off" method="POST">
 <input type='hidden' name='newScan' value='yes' />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>C</th>
	    <th class='centered'>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['member-group']; ?></th>
	    <th><?php echo $lang['chip']; ?></th>
	    <th><?php echo $lang['delete-chip']; ?></th>
	    <th><?php echo $lang['assign-new-chip']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
<?php
	
	while ($row = $result->fetch()) {
		
		
		$user_id = $row['user_id'];
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$userGroup = $row['userGroup'];
		$cardid = $row['cardid'];
		$starCat = $row['starCat'];
		
		if ($_SESSION['lang'] == 'es') {
			$query = "SELECT groupName_es AS groupname from usergroups WHERE userGroup = $userGroup";						
		} else {
			$query = "SELECT groupName AS groupname from usergroups WHERE userGroup = $userGroup";			
		}
		
		try
		{
			$resultUG = $pdo3->prepare($query);
			$resultUG->execute();
		}
		catch (PDOException $eUG)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	
		$rowUG = $resultUG->fetch();
			$groupname = $rowUG['groupname'];
		
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
		
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='centered'><a href='javascript:delete_chip($cardid,$user_id)'><img src='images/delete.png' height='15' /></a></td>
  	   <td href='profile.php?user_id='><input type='text' class='defaultinput' name='newchip$user_id' placeholder='%s' /></td></tr>",
	  $user_id, $userStar, $user_id, $memberno, $user_id, $first_name, $user_id, $last_name, $user_id, $groupname, $user_id, $cardid, $lang['scan-new-chip']);

	}
	echo "</tbody></table><button class='cta1' type='submit' style='visibility: hidden;'>Submit</button></form>";
	
displayFooter();
