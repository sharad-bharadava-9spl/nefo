<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

	getSettings();

	if (!isset($_POST['untilDate'])) {
		
		echo "No date selected. Please try again.";
		exit();
		
	}

	$limit = (intval($_POST['limit']) != 0 ) ? $_POST['limit'] : 10;
	$offset = (intval($_POST['offset']) != 0 ) ? $_POST['offset'] : 0;
	
	$untilDate = $_POST['untilDate'];
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.exento, (SELECT s.saletime FROM sales as s WHERE s.userid = u.user_id ORDER BY s.saletime DESC LIMIT 1) as saletime FROM users u ,usergroups ug WHERE u.userGroup = ug.userGroup AND u.userGroup BETWEEN 5 AND 6 ORDER by u.memberno ASC limit $limit OFFSET $offset";
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

die;
?>