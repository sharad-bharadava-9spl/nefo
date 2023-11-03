<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	$totrows = $_GET['totrows'];
	
	$firstday = $day + 1;
	
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.discount, u.discountBar FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' AND u.userGroup < 7 ORDER by u.memberno ASC LIMIT $day, 1000";
	
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
			
	$checkCatDiscount = "SELECT SUM(discount) from catdiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkCatDiscount");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$catDiscount = $row['SUM(discount)'];
		$catDiscountRaw = $row['SUM(discount)'];
		
	if ($catDiscount > 0 || $catDiscount < 0) {
		$catDiscount = $lang['global-yes'];
	} else {
		$catDiscount = '';
	}

	$checkIndDiscount = "SELECT SUM(discount) from inddiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkIndDiscount");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$indDiscount = $row['SUM(discount)'];
		$indDiscountRaw = $row['SUM(discount)'];
		
	if ($indDiscount > 0 || $indDiscount < 0) {
		$indDiscount = $lang['global-yes'];
	} else {
		$indDiscount = '';
	}

	$checkCatDiscountBar = "SELECT SUM(discount) from b_catdiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkCatDiscountBar");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		$catDiscountBar = $row['SUM(discount)'];
		$catDiscountBarRaw = $row['SUM(discount)'];
		
	if ($catDiscountBar > 0 || $catDiscountBar < 0) {
		$catDiscountBar = $lang['global-yes'];
	} else {
		$catDiscountBar = '';
	}

	$checkIndDiscountBar = "SELECT SUM(discount) from b_inddiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkIndDiscountBar");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		$indDiscountBar = $row['SUM(discount)'];
		$indDiscountBarRaw = $row['SUM(discount)'];
		
	if ($indDiscountBar > 0 || $indDiscountBar < 0) {
		$indDiscountBar = $lang['global-yes'];
	} else {
		$indDiscountBar = '';
	}
	
	if ($user['usageType'] == '1') {
		$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
	} else {
		$usageType = '';
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
	
	$discountSum = $catDiscountRaw + $indDiscountRaw + $catDiscountBarRaw + $indDiscountBarRaw + $user['discount'] + $user['discountBar'];

	if ($discountSum > 0) {
	
	$sales_row .= sprintf("
  	  <tr>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'><a href='mini-profile.php?user_id={$user['user_id']}'>%s</a></td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'><a href='mini-profile.php?user_id={$user['user_id']}'>%s</a></td>
  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'><a href='mini-profile.php?user_id={$user['user_id']}'>%s</a></td>
  	   <td class='clickableRow centered' style='text-align: center;' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%d%%</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%d%%</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow centered' href='mini-profile.php?user_id=%d'>%s</td></tr>
  	   ",
	  $user['user_id'], $userStar, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], $usageType, $user['user_id'], $user['discount'], $user['user_id'], $catDiscount, $user['user_id'], $indDiscount, $user['user_id'], $user['discountBar'], $user['user_id'], $catDiscountBar, $user['user_id'], $indDiscountBar);
	  
  	}
  	
	  
  }
  
  if (($day + 1000) < $totrows) {
	  
	  $sofar = $day + 1000;
	
		$sales_row .= <<<EOD
	<tr id='loadMore' style='border-bottom: 0;'><td class='centered' colspan='11'>($sofar / $totrows {$lang['global-members']} {$lang['loaded']})<br /><a href='#' onclick='event.preventDefault(); loadMoreDays()' style='font-size: 12px;'>{$lang['load-more']}</a></td></tr>
EOD;
} else {
		$sales_row .= <<<EOD
	<tr id='loadMore' style='border-bottom: 0;'><td class='centered' colspan='11'>($totrows / $totrows {$lang['global-members']} {$lang['loaded']})</td></tr>
EOD;
}


	echo $sales_row;