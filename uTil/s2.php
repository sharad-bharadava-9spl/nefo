<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up users
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.yearGroup, u.form, u.fptemplate1, u.parent, u.nationality, u.photoext, u.family FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.userGroup = 4 ORDER by u.last_name ASC";
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


	pageStart("Students", NULL, $memberScript, "pmembership", NULL, "STUDENTS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th>Nationality</th>
	    <th><?php echo $lang['global-credit']; ?></th>
	    <th><?php echo $lang['member-gender']; ?></th>
	    <th><?php echo $lang['age']; ?></th>
	    <th>Year group</th>
	    <th>Form</th>
	    <th>Fingerprint</th>
	    <th>Photo</th>
	    <th>Parent(s)</th>
	    <th>Portal</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	while ($user = $results->fetch()) {
	
	// Calculate Age:
	$day = $user['day'];
	$month = $user['month'];
	$year = $user['year'];	
	$fptemplate1 = $user['fptemplate1'];	
	$family = $user['family'];
	
	$parents = "SELECT COUNT(user_id) FROM users WHERE family = $family AND userGroup <> 4";
		try
		{
			$result = $pdo3->prepare("$parents");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$parent = $row['COUNT(user_id)'];
		
	$parents = "SELECT userPass FROM users WHERE family = $family AND userGroup <> 4";
		try
		{
			$results2 = $pdo3->prepare("$parents");
			$results2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	$portal = 0;
		
		while ($check = $results2->fetch()) {
		
		$userPass = $check['userPass'];
		
		if ($userPass != NULL) {
			
			$portal = 1;
			
		}
		
	}
		
	

	// Find out if member has registered fingerprint:
	if ($fptemplate1 == '') {
		$form1colour = 'negative';
		$form1 = $lang['global-no'];
	} else {
		$form1colour = '';
		$form1 = $lang['global-yes'];
	}
	
	// Find out if photo has been taken
	$file = 'images/members/' . $user['user_id'] . '.' . $user['photoext'];

	$file_exist = object_exist($google_bucket, $google_root_folder.$file); 
	
	if (!$file_exist) {
		$dnicolour = 'negative';
		$dniScan = $lang['global-no'];
	} else {
		$dnicolour = '';
		$dniScan = $lang['global-yes'];
	}

	// Find out if member has registered parent(s):
	if ($parent == 0) {
		$form2colour = 'negative';
		$form2 = $parent;
	} else {
		$form2colour = '';
		$form2 = $parent;
	}
	
	if ($portal == 0) {
		$portalcolour = 'negative';
		$portal = $lang['global-no'];
	} else {
		$portalcolour = '';
		$portal = $lang['global-yes'];
	}
	
	// Does user have comments?
	$getNotes = "SELECT noteid, notetime, userid, note FROM usernotes WHERE userid = {$user['user_id']} ORDER by notetime DESC";
	try
	{
		$result = $pdo3->prepare("$getNotes");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if (!$data) {
   		$comment = '';
	} else {
   		$comment = "<img src='images/note.png' width='16' /><span style='display:none'>1</span>";
	}
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow right' href='profile.php?user_id=%d'>%0.1f {$_SESSION['currencyoperator']}</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
	   <td style='text-align: center;' class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>
	   <td style='text-align: center;' class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>
	   <td style='text-align: center;' class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>
	   <td style='text-align: center;' class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], $user['nationality'], $user['user_id'], $user['credit'], $user['user_id'], $user['gender'], $user['user_id'], $age, $user['user_id'], $user['yearGroup'], $user['user_id'], $user['form'], $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan, $form2colour, $user['user_id'], $form2, $portalcolour, $user['user_id'], $portal);
	  
  	}
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
