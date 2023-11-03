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

	$domain = $_SESSION['domain'];

	if (isset($_POST['bajaReady'])) {
		
		foreach($_POST['giveBaja'] as $toBaja) {
			
			$bajaList .= $toBaja . ',';
			
		}
		
		$bajaList = rtrim($bajaList, ",");
		$bajaTime = date('Y-m-d H:i:s');
		
		if ($_SESSION['keepNumber'] == 1) {
			
			$bajaQuery = "UPDATE users SET userGroup = 9, bajaDate = '$bajaTime' WHERE user_id IN ($bajaList)";
			
		} else {
			
			$bajaQuery = "UPDATE users SET userGroup = 9, bajaDate = '$bajaTime', memberno = '0' WHERE user_id IN ($bajaList)";
			
		}
		
		
		try
		{
			$result = $pdo3->prepare("$bajaQuery")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// On success: redirect.
		$_SESSION['successMessage'] = "Socios bajado con &eacute;xito!";
		header("Location: members.php");
		exit();
		
	}

	// delete members

	if(isset($_POST['deleteReady'])){
		
			foreach($_POST['deleteMember'] as $toDelete) {
				
				$deleteList .= $toDelete . ',';
				
			}
		
		    $deleteList = rtrim($deleteList, ",");
			$deleteTime = date('Y-m-d H:i:s');
			$paymentTime = date('Y-m-d H:i:s');
			
			
			$updateUser = sprintf("UPDATE users SET memberno = '0', userGroup = '8', first_name = 'DELETED', last_name = 'DELETED', email = 'DELETED', dni = 'DELETED', street = 'DELETED', streetnumber = 0, flat = '', telephone = 'DELETED', cardid = '', cardid2 = '', cardid3 = '', friend = '', form1 = '0', form2 = '0', deleteTime = '%s' WHERE user_id IN (%s);",
			$deleteTime,
			$deleteList
			);

				try
				{
					$result = $pdo3->prepare("$updateUser")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
								
			// Look up photoext for image deleting
			$userDetails = "SELECT photoext, dniext1, dniext2 FROM users WHERE user_id IN ($deleteList)";
				try
				{
					$result = $pdo3->prepare("$userDetails");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while($row = $result->fetch()){
					$photoExt = $row['photoext'];
					$dniext1 = $row['dniext1'];
					$dniext2 = $row['dniext2'];
					
					$imgname = "images/_$domain/members/" . $user_id . "." . $photoExt;
					if (file_exists($imgname)) { unlink ($imgname); }
					
					$imgname2 = "images/_$domain/ID/" . $user_id . "-front." . $dniext1;
					if (file_exists($imgname2)) { unlink ($imgname2); }
					
					$imgname3 = "images/_$domain/ID/" . $user_id . "-back." . $dniext2;
					if (file_exists($imgname3)) { unlink ($imgname3); }
					
					$imgname4 = "images/_$domain/sigs/" . $user_id . ".png";
					if (file_exists($imgname4)) { unlink ($imgname4); }
				}

			// On success: redirect.
			$_SESSION['successMessage'] = "Members deleted successfully!";
			header("Location: members.php");
			exit();		
	}
	
	
	if (!isset($_POST['untilDate'])) {
		
		echo "No date selected. Please try again.";
		exit();
		
	}
	
	$untilDate = $_POST['untilDate'];

		// Check if 'entre fechas' was utilised
	if (!empty($_POST['untilDate'])) {
		
		$limitVar = "";
		
		//$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(u.registeredSince) < DATE('$untilDate')";
		$limitVar = "";
			
	}else{
		$timeLimit = '';
	}
	
/*	SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.exento, (SELECT s.saletime FROM sales2 as s WHERE s.userid = u.user_id ORDER BY s.saletime DESC LIMIT 1) as saletime FROM users2 u ,usergroups ug WHERE u.userGroup = ug.userGroup AND u.userGroup BETWEEN 5 AND 6 ORDER by u.memberno ASC*/
	// Query to look up users
	//$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.exento FROM users2 u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.userGroup BETWEEN 5 AND 6 ORDER by u.memberno ASC limit 5";
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.exento, (SELECT s.saletime FROM sales as s WHERE s.userid = u.user_id AND DATE(s.saletime) < DATE('$untilDate') ORDER BY s.saletime DESC LIMIT 1)  as saletime  FROM users u ,usergroups ug WHERE u.userGroup = ug.userGroup AND u.userGroup BETWEEN 5 AND 6 $timeLimit HAVING saletime IS NOT NULL ORDER by u.memberno ASC";
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
				sortList: [[11,0],[5,0]],
				headers: {
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "dates"
					},
					10: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 0) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				sortList: [[10,0],[5,0]],
				headers: {
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "dates"
					},
					10: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 0 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				sortList: [[10,0],[4,0]],
				headers: {
					4: {
						sorter: "dates"
					},
					9: {
						sorter: "dates"
					},
					10: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				sortList: [[9,0],[4,0]],
				headers: {
					4: {
						sorter: "dates"
					},
					9: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	}
	
	$memberScript .= <<<EOD
		});
		
		
EOD;


	pageStart($lang['give-baja'], NULL, $memberScript, "pmembership", 'dev-align-center', $lang['give-baja'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<br />
	<form action='' method='POST' name='registerForm'>
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	   	<?php if($_POST['selectType'] == 1){  ?>
	    	<th><?php echo $lang['make-inactive']; ?></th>
		<?php }else{  ?>
			<th>Delete Member</th>
		<?php } ?>	
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><?php echo $lang['global-credit']; ?></th>
<?php } ?>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['member-gender']; ?></th>
	    <th><?php echo $lang['age']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['member-group']; ?></th>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
	    <th><?php echo $lang['expiry']; ?></th>
<?php } ?>
	    <th><?php echo $lang['last-dispense']; ?></th>
	   </tr>
	  </thead>
	  <tbody id="inactive-member-data">
<?php	  		while ($user = $results->fetch()) {
// Calculate Age:
	$day = $user['day'];
	$month = $user['month'];
	$year = $user['year'];
	$paidUntil = $user['paidUntil'];
	$exento = $user['exento'];
$bdayraw = $day . "." . $month . "." . $year;
$bday = new DateTime($bdayraw);
$today = new DateTime(); // for testing purposes
$diff = $today->diff($bday);
$age = $diff->y;

	// Look up last dispense date
/*	$dispQuery = "SELECT saletime FROM sales2 WHERE userid = {$user['user_id']} ORDER BY saletime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$dispQuery");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}*/
			
		//if ($user['saletime'] == '') {
		
		$lastDispense = "<span class='white'>00-00-0000</span>";
		
	//} 
	if($user['saletime'] != '') {
		
		//$rowD = $data[0];
			$lastDispense = date("d-m-Y", strtotime($user['saletime']));
			
	}
	
	// Decide whether or not to check the checkbox: && regdate - 30
	$untilD = strtotime(date('Y-m-d', strtotime($untilDate)));
	$lastD = strtotime(date('Y-m-d', strtotime($lastDispense)));
	$regD = strtotime(date('Y-m-d', strtotime($user['registeredSince'])));
	$nowMinusThirty = strtotime(date('Y-m-d H:m:s',strtotime('-30 day')));
	
	// Today - 30
	// Compare with regdate
/*	if (($lastD < $untilD) && ($regD < $nowMinusThirty)) {
		$checkOrNot = 'checked';
	} else {
		$checkOrNot = '';
	}*/
	$checkOrNot = 'checked';

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

	if($_POST['selectType'] == 1){
		$inputCheck = "<input type='checkbox' name='giveBaja[".$user['user_id']."]' value='".$user['user_id']."' style='width: 12px;' $checkOrNot />";
	}else{
		$inputCheck = "<input type='checkbox' name='deleteMember[".$user['user_id']."]' value='".$user['user_id']."' style='width: 12px;' $checkOrNot />";
	}
	
	echo sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>",
	  $inputCheck, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRowNew right' href='profile.php?user_id=%d'>%0.1f {$_SESSION['currencyoperator']}</td>",
  	  $user['user_id'], $user['credit']);
  	  
}

	echo sprintf("
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%d</td>
  	   <td class='clickableRowNew' style='text-align: center;' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $user['gender'], $user['user_id'], $age, $user['user_id'], $usageType, $user['user_id'], $user['groupName']);

if ($_SESSION['membershipFees'] == 1) {
	  
	echo sprintf("<td class='clickableRowNew %s' href='profile.php?user_id=%d'>%s</td>",
   $paidClass, $user['user_id'], $membertill);
	    
}

	echo sprintf("<td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>",
   $user['user_id'], $lastDispense);


}

?>
	 </tbody>
	 </table>
    <?php if($_POST['selectType'] == 1){ ?>
	 <input type='hidden' name='bajaReady' />
	 <br />
	 <button type='input' class="cta1"><?php echo $lang['make-inactive']; ?></button>
	<?php }else{ ?>
		<input type='hidden' name='deleteReady' />
		 <br />
		 <button type='input' class="cta1">Delete Member</button>
	<?php } ?>
	</form>

<?php  displayFooter(); ?>
<script type="text/javascript">
	$(document).ready(function(){
/*		$.ajax({
			type: "POST",
			url: 'getInactiveMembers.php',
			data:{untilDate: "<?php //echo $untilDate; ?>"},
			success: function(data){
				$("#inactive-member-data").html(data);
			}
		});*/
	});
</script>
<script type="text/javascript">

/*var busy = false;
var limit = 100
var offset = 1;

function displayRecords(lim, off) {
        $.ajax({
          type: "POST",
          async: false,
          url: "getInactiveMembers.php",
          data:{untilDate: "<?php echo $untilDate; ?>", limit: lim, offset: off},
          cache: false,
          beforeSend: function() {
            $("#loader_message").html("").hide();
            $('#loader_image').show();
          },
          success: function(html) {
            $("#inactive-member-data").append(html);
           // $('#loader_image').hide();
            if (html == "") {
              $("#loader_message").html('').show();
            } else {
              $("#loader_message").html('Loading please wait...').show();
            }
            window.busy = false;

          }
        });
}
$(document).ready(function() {

	$(window).scroll(function() {
	          // make sure u give the container id of the data to be loaded in.
	          if ($(window).scrollTop() + $(window).height() > $("#inactive-member-data").height() && !busy) {
	            busy = true;
	            offset = limit + offset;

	            displayRecords(limit, offset);

	          }
	});

});*/	
</script>