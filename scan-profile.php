<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
		if ($_SESSION['domain'] == 'royalgreen') {
			$linkVar = "profile.php";
		} else {
			$linkVar = "mini-profile.php";
		}
	if (isset($_POST['searchfield'])) {
		
		$selectedUserArr = $_POST['cashBox'];
		$selectedUsergroup = implode(",", $selectedUserArr);
		
		if ($selectedUsergroup == '') {
			$selectedUsergroup = '0';
		}
		
		setcookie( "ccsProfileSearch", "$selectedUsergroup", time() - 3650 );
		setcookie( "ccsProfileSearch", "$selectedUsergroup", time() + (10 * 365 * 24 * 60 * 60) );

		$phrase = $_POST['searchfield'];
	
		if ($_SESSION['domain'] == 'apatac') {
			
			$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.paidUntil, u.userGroup, ug.groupName, u.credit, u.oldNumber, u.exento, u.dniext1, u.street FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND (u.first_name LIKE ('%$phrase%') OR u.last_name LIKE ('%$phrase%') OR u.oldNumber LIKE ('%$phrase%') OR u.memberno LIKE ('%$phrase%') OR u.dni LIKE ('%$phrase%')) AND u.userGroup IN ($selectedUsergroup) ORDER by u.memberno ASC";
			 
			/* Direct search for number only (asked ot disable on the 21/10/2021):
			$selectUsers = "SELECT user_id FROM users WHERE memberno = '$phrase'";
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
		
			$row = $result->fetch();
				$user_id = $row['user_id'];
				
			if ($user_id == 0 || $user_id == 1 || $user_id == '') {
				
				pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], "Usuario no encontrado. Por favor <a href='scan-profile.php'>intentalo de nuevo</a>.");
				
				exit();
				
			}
				
			header("Location: mini-profile.php?user_id=$user_id");
			exit();
			
			*/
			
		} else {
			
			$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.paidUntil, u.userGroup, ug.groupName, u.credit, u.oldNumber, u.exento, u.dniext1, u.street FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND (u.first_name LIKE ('%$phrase%') OR u.last_name LIKE ('%$phrase%') OR u.oldNumber LIKE ('%$phrase%') OR u.memberno LIKE ('%$phrase%') OR u.cardid LIKE ('%$phrase%') OR u.email LIKE ('%$phrase%') OR u.dni LIKE ('%$phrase%') OR u.street LIKE ('%$phrase%') OR u.qrcode LIKE ('%$phrase%')) AND u.userGroup IN ($selectedUsergroup) ORDER by u.memberno ASC";
			
		}
					
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
			
EOD;

	if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					4: {
						sorter: "currency"
					},
					7: {
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
						sorter: "dates"
					},
					4: {
						sorter: "currency"
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
					6: {
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
         <a href="#" onClick="loadExcel();"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-registered']; ?></th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><?php echo $lang['global-credit']; ?></th>
<?php } ?>
	    <th><?php echo $lang['member-group']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
	    <th><?php echo $lang['expiry']; ?></th>
<?php } ?>
	    <th class='centered'><?php echo $lang['signature']; ?></th>
	    <th><?php echo $lang['dni-scan']; ?></th>
	    <th style='color: red;'><?php echo $lang['old-number']; ?></th>
	    <th><?php echo $lang['global-comment']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	while ($user = $results->fetch()) {

	
		$paidUntil = $user['paidUntil'];
		$memberType = $user['memberType'];
		$oldNumber = $user['oldNumber'];
		$exento = $user['exento'];
		
		if ($memberType == 0) {
			$mType = '';
		} else if ($memberType == 1) {
			$mType = 'Veterano';
		} else if ($memberType == 2) {
			$mType = 'Terapeutico';
		} else if ($memberType == 3) {
			$mType = 'VIP';
		} else if ($memberType == 4) {
			$mType = 'Cuota anual';
		} else if ($memberType == 5) {
			$mType = 'Invitado';
		} else if ($memberType == 6) {
			$mType = 'Junta';
		} else if ($memberType == 7) {
			$mType = 'Mat, Che, Da';
		} else if ($memberType == 8) {
			$mType = 'Mensual';
		} else if ($memberType == 9) {
			$mType = '3 meses';
		} else if ($memberType == 10) {
			$mType = 'Rojo';
		}
		
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
	// Find out if DNI has been scanned:
	$file = "images/_$domain/ID/" . $user['user_id'] . '-front.' . $user['dniext1'];
	
	if (!file_exists($file)) {
		$dnicolour = 'negative';
		$dniScan = $lang['global-no'];
	} else {
		$dnicolour = '';
		$dniScan = $lang['global-yes'];
	}

	// Find out if member has signed:
	$file2 = "images/_$domain/sigs/" . $user['user_id'] . '.png';
	
	if (!file_exists($file2)) {
		$form1colour = 'negative';
		$form1 = $lang['global-no'];
	} else {
		$form1colour = '';
		$form1 = $lang['global-yes'];
	}

		

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
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>",
	  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])));
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRow right' href='$linkVar?user_id=%d'>%0.1f {$_SESSION['currencyoperator']}</td>",
  	  $user['user_id'], $user['credit']);
  	  
}
		$langoperator = $_SESSION['lang'];
		$query = "SELECT $langoperator FROM usergroups WHERE userGroup = {$user['userGroup']}";
		try
		{
			$resultU = $pdo->prepare("$query");
			$resultU->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowU = $resultU->fetch();
			$groupName = $rowU[0];
			
	echo sprintf("
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='$linkVar?user_id=%d'>%s</td>",
  	  $user['user_id'], $groupName, $user['user_id'], $mType);

if ($_SESSION['membershipFees'] == 1) {
	  
	echo sprintf("<td class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>",
   $paidClass, $user['user_id'], $membertill);
	    
}

/*	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan);*/
	  
	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='$linkVar?user_id=%d'>%s</td>
  	   <td style='text-align: center; color: red;' class='clickableRow' href='$linkVar?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan, $user['user_id'], $oldNumber);
	  
	  
	echo sprintf("
  	   <td style='text-align: center;' class='clickableRow' href='$linkVar?user_id=%d&openComment'>%s</td>
	  </tr>",
	  $user['user_id'], $comment
	  );
	  
  }
?>

	 </tbody>
	 </table>		
	
<?php

	} else {
	
		pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

<center>
<form id="registerForm" action="" method="POST">
   <div id="mainbox-no-width">
   <div class='mainboxheader'>
    <img src="images/lupe.png" style='margin-bottom: -8px; margin-right: 5px;' /> <?php echo $lang['search-member']; ?>
   </div>
   <div class='mainboxcontent' style='text-align: left;'>
 
<?php
	if ($_SESSION['iPadReaders'] > 0) {
?>
  <input type="text" name="searchfield" placeholder="<?php echo $lang['searchplaceholder']; ?>" class='defaultinput' style='width: 500px;' />
<?php
	} else {
?>
  <input type="text" name="searchfield" placeholder="<?php echo $lang['searchplaceholder']; ?>" autofocus class='defaultinput' style='width: 500px;' />
<?php
	}
?>
		<br />
		<br />
<?php
	
	$langoperator = $_SESSION['lang'];
	$query = "SELECT userGroup, $langoperator from usergroups WHERE userGroup < 11";
	try
	{
		$results = $pdo->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if (isset($_COOKIE['ccsProfileSearch'])) {
		
		$ccsProfileSearch = $_COOKIE['ccsProfileSearch'];
				
	}


	while ($row = $results->fetch()) {
		
		$userGroup = $row['userGroup'];
		$groupName = $row[$langoperator];
		
		
	if (strpos($ccsProfileSearch, $userGroup) !== false) {
		
		$checkOperator = 'checked';
		
	} else {
		
		$checkOperator = '';
		
	}
		
		echo <<<EOD
		
	<div class='fakeboxholder firstbox' style='margin-left: 170px;'>	
	 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  $groupName
	  <input type="checkbox" name="cashBox[]" id="accept1" value='$userGroup' $checkOperator/>
	  <div class="fakebox"></div>
	 </label>
	</div>
		<br />
		<br />
EOD;
		
	}
	
?>


 </div> <!-- END OVERVIEW -->
 </div> <!-- END OVERVIEW -->
		<br />
  <button type="submit" class='cta1'><?php echo $lang['search']; ?></button>
</form>
</center>

<?php } displayFooter();  ?>
<script type="text/javascript">
 function loadExcel(){
 			$("#load").show();
 			var searchfield = "<?php echo $phrase; ?>";
 			var ugroups = "<?php echo $selectedUsergroup; ?>";
       		window.location.href = 'scan-profile-report.php?searchfield='+searchfield+'&usergroup='+ugroups;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>