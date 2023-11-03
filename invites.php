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
	
	
	
	// Query to look up users
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.email, u.telephone, SUM(s.amount) FROM users u, sales s WHERE u.user_id = s.userid GROUP BY user_id ORDER by SUM(s.amount) DESC ";
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
	

	pageStart($lang['invite'], NULL, $memberScript, "pmembership", NULL, $lang['invite'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<br />
	<form action='' method='POST' name='registerForm'>
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['make-inactive']; ?></th>
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
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {
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
	$dispQuery = "SELECT saletime FROM sales WHERE userid = {$user['user_id']} ORDER BY saletime DESC LIMIT 1";
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
		}
			
		if (!$data) {
		
		$lastDispense = "<span class='white'>00-00-0000</span>";
		
	} else {
		
		$rowD = $data[0];
			$lastDispense = date("d-m-Y", strtotime($rowD['saletime']));
			
	}
	
	// Decide whether or not to check the checkbox: && regdate - 30
	$untilD = strtotime(date('Y-m-d', strtotime($untilDate)));
	$lastD = strtotime(date('Y-m-d', strtotime($lastDispense)));
	$regD = strtotime(date('Y-m-d', strtotime($user['registeredSince'])));
	$nowMinusThirty = strtotime(date('Y-m-d H:m:s',strtotime('-30 day')));
	
	// Today - 30
	// Compare with regdate
	if (($lastD < $untilD) && ($regD < $nowMinusThirty)) {
		$checkOrNot = 'checked';
	} else {
		$checkOrNot = '';
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
	
	echo sprintf("
  	  <tr>
  	   <td><input type='checkbox' name='giveBaja[%d]' value='%d' style='width: 12px;' $checkOrNot /></td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $user['user_id'], $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	  

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
	 <input type='hidden' name='bajaReady' />
	 <br />
	 <button type='input'><?php echo $lang['make-inactive']; ?></button>
	</form>

<?php  displayFooter(); ?>
