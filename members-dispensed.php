<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();	
	
	$month_ini = new DateTime("first day of last month");
	$month_end = new DateTime("last day of last month");
	
	$monthBegin = $month_ini->format('Y-m-d'); // 2012-02-01
	$monthEnd = $month_end->format('Y-m-d'); // 2012-02-29
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		$optionList = "<option value='filterVar'>$filterVar</option>";
		
		// Grab month and year number
		$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
		$year = substr($filterVar, strrpos($filterVar, '-') + 1);

		$newDate = $year . "-" . $month;

		$monthBegin = date("Y-m-1", strtotime($newDate));
		$monthEnd = date("Y-m-t", strtotime($newDate));
		
		$timeLimit = "WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";

	} else if ($_GET['filter'] == 'this') {
				
		$monthBegin = date("Y-m-1");
		$monthEnd = date("Y-m-t");
		
		$timeLimit = "WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";

	} else {
		
		$month_ini = new DateTime("first day of last month");
		$month_end = new DateTime("last day of last month");
		
		$monthBegin = $month_ini->format('Y-m-d'); // 2012-02-01
		$monthEnd = $month_end->format('Y-m-d'); // 2012-02-29
		$timeLimit = "WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";
		
	}
		
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$monthBegin = date("Y-m-d", strtotime($_POST['fromDate']));
		$monthEnd = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";

	}
	
	// Create month-by-month split
	$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
	try
	{
		$result = $pdo3->prepare("$findStartDate");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$startDate = date('01-m-Y', strtotime($row['saletime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
	/*if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}*/
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
	
	while (strtotime($genDateFull) > strtotime($startDate)) {
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}

	}
	
	
	$selectRealActives = "SELECT DISTINCT userid FROM sales $timeLimit";
	try
	{
		$result = $pdo3->prepare("$selectRealActives");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($user = $result->fetch()) {		
		$user = $user['userid'];
		$userArray .= $user . ",";
		
	}
	
	$userArray = substr($userArray, 0, -1);
	
	if ($userArray != '') {
		
		// Query to look up users
		$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id IN ($userArray) ORDER by u.memberno ASC";
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
		
	}
	
		$selectRealActives = "SELECT COUNT( DISTINCT userid ) FROM sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";
		try
		{
			$resultReal = $pdo3->prepare("$selectRealActives");
			$resultReal->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowReal = $resultReal->fetch();
			$realActiveMembersNow = $rowReal['COUNT( DISTINCT userid )'];

		
		
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
					4: {
						sorter: "currency"
					},
					6: {
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
				headers: {
					4: {
						sorter: "currency"
					},
					6: {
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
					4: {
						sorter: "dates"
					},
					9: {
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


	pageStart($lang['members-dispensed-history'], NULL, $memberScript, "pmembership", NULL, $lang['members-dispensed-history'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href="new-expense.php" class="cta1"><?php echo $lang['expense-newexpense']; ?></a> <a href="expenses-summary.php" class="cta1"><?php echo $lang['summary']; ?></a><br />
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent' style='padding-bottom: 0;'>
  <form action='' method='POST' style='margin-top: 3px;'>
   <select id='filter' name='filter' class='defaultinput-no-margin' style='width: 242px;' onchange='this.form.submit()'>
    <?php echo $optionList; ?>
   </select>
  </form><br />
  <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <br /><button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="{$lang['from-date']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="{$lang['from-date']}" onchange='this.form.submit()' />
		 <br /><button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;

	}
?>
        </form>
 </div>
</div>
<br /><br />
<div id='productoverview'>
 <table>
  <tr>
   <td>&nbsp;&nbsp;<?php echo $lang['closeday-dispensed']; ?>:</td>
   <td class='yellow fat right'><?php echo $realActiveMembersNow; ?></td>
  </tr>
 </table>
</div>
</center>

<br />
	 <table class="default" id="mainTable">
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>C</th>
	    <th class='centered'>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><?php echo $lang['global-credit']; ?></th>
	    <th style='text-align: center;'>*</th>
<?php } ?>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['member-gender']; ?></th>
	    <th><?php echo $lang['age']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['member-group']; ?></th>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
	    <th><?php echo $lang['expiry']; ?></th>
<?php } ?>
	    <th class='centered'><?php echo $lang['signature']; ?></th>
	    <th><?php echo $lang['dni-scan']; ?></th>
	    <!--<th style='color: red;'><?php echo $lang['old-number']; ?></th>-->
	    <th class="noExl"><?php echo $lang['global-comment']; ?></th>
	    <th class="noExl"><?php echo $lang['global-edit']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $result->fetch()) {	
// Calculate Age:
	$day = $user['day'];
	$month = $user['month'];
	$year = $user['year'];
	$paidUntil = $user['paidUntil'];
	$starCat = $user['starCat'];
	$oldNumber = $user['oldNumber'];
	$exento = $user['exento'];
	
	
	
$bdayraw = $day . "." . $month . "." . $year;
$bday = new DateTime($bdayraw);
$today = new DateTime(); // for testing purposes
$diff = $today->diff($bday);
$age = $diff->y;

	// Find out if DNI has been scanned:
	$file = 'images/_' . $_SESSION['domain'] . '/ID/' . $user['user_id'] . '-front.' . $user['dniext1'];
		$object_exist =  object_exist($google_bucket, $google_root_folder.$file);
	if($object_exist){
		$dnicolour = '';
		$dniScan = $lang['global-yes'];
		
	}else{
		$dnicolour = 'negative';
		$dniScan = $lang['global-no'];
	}
/*	if (!file_exists($file)) {
		$dnicolour = 'negative';
		$dniScan = $lang['global-no'];
	} else {
		$dnicolour = '';
		$dniScan = $lang['global-yes'];
	}*/

	// Find out if member has signed:
	$file2 = $google_root_folder.'images/_' . $_SESSION['domain'] . '/sigs/' . $user['user_id'] . '.png';

	$object_exist1 = object_exist($google_bucket, $file2);
	
	if ($object_exist1 === false) {
		$form1colour = 'negative';
		$form1 = $lang['global-no'];
	} else {
		$form1colour = '';
		$form1 = $lang['global-yes'];
	}

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
	} else {
   		$userStar = "<span style='display:none'>0</span>";
	}

	// Does user have comments?
	$getNotes = "SELECT noteid, notetime, userid, note FROM usernotes WHERE userid = {$user['user_id']} ORDER by notetime DESC";
	try
	{
		$resultC = $pdo3->prepare("$getNotes");
		$resultC->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $resultC->fetch();
		$noteid = $row['noteid'];
	if(!$row) {
   		$comment = '';
	} else {
   		//$comment = "<img src='images/note.png' width='16' /><span style='display:none'>1</span>";

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
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $userStar, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.1f {$_SESSION['currencyoperator']}</td>
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

  	   
// OLD NUMBER EXCLUDED:
	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan);


/* OLD NUMBER INCLUDED:
	echo sprintf("
	   <td style='text-align: center;' class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center;' class='clickableRow %s' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td style='text-align: center; color: red;' class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
	  $form1colour, $user['user_id'], $form1, $dnicolour, $user['user_id'], $dniScan, $user['user_id'], $oldNumber);
*/

  	
	echo sprintf("
  	   <td style='text-align: center;' class='clickableRow noExl' href='profile.php?user_id=%d&openComment'><span class='relativeitem'>%s</span></td>
  	   <td style='text-align: center;' class='noExl'><a href='edit-profile.php?user_id=%d'><img src='images/edit.png' height='15' title='Edit user' /></a></td>
	  </tr>",
	  $user['user_id'], $comment, $user['user_id']
	  );
	  
  }
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
