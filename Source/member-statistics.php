<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	pageStart($lang['member-statistics'], NULL, NULL, "memberstats", "product admin", $lang['member-statistics'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


		// Look up current members
		$selectMembers = "SELECT COUNT(memberno) from users WHERE userGroup <> 8 AND userGroup <> 7";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembers = $row['COUNT(memberno)'];
			
		// Look up current members consumption limit
		$selectMembers = "SELECT SUM(mconsumption) from users";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembersConsumptionLimit = $row['SUM(mconsumption)'];

		// Consumption this calendar month
		$selectSales = "SELECT SUM(s.quantity) FROM sales s, users u WHERE u.memberno <> '0' AND s.userid = u.user_id AND MONTH(s.saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$quantityMonth = $row['SUM(s.quantity)'];
			
		// Look up current active members status 5
		$selectMembers = "SELECT COUNT(memberno) from users WHERE usergroup = 5 AND (paidUntil >= DATE(NOW()) OR exento = 1)";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembersNormal = $row['COUNT(memberno)'];
			
		// Look up current professionals
		$selectMembers = "SELECT COUNT(memberno) from users WHERE usergroup = 4";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentPros = $row['COUNT(memberno)'];
			
		// Look up current volunteers
		$selectMembers = "SELECT COUNT(memberno) from users WHERE usergroup = 3";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentVolunteers = $row['COUNT(memberno)'];
			
		$selectMembers = "SELECT COUNT(memberno) from users WHERE usergroup = 2";
		// Look up current staff
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentStaff = $row['COUNT(memberno)'];
			
		// Look up current admins
		$selectMembers = "SELECT COUNT(memberno) from users WHERE usergroup = 1";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentAdmins = $row['COUNT(memberno)'];
			
		// Look up current active members
		$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND (DATE(paidUntil) >= DATE(NOW()) OR exento = 1))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentActiveMembers = $row['COUNT(memberno)'];
			
		// Look up current active members
		$selectMembers = "SELECT SUM(mconsumption) from users WHERE memberno <> '0' AND ((paidUntil >= DATE(NOW())) OR (userGroup < 5) OR exento = 1)";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentActiveMembersConsumptionLimit = $row['SUM(mconsumption)'];
			
		$consumptionPercentage = (($quantityMonth / $currentActiveMembersConsumptionLimit) * 100);
		
		// Look up new members this month
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(NOW()) AND YEAR(registeredSince) = YEAR(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembersMonth = $row['COUNT(memberno)'];
			
		// Look up expired members this month
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(paidUntil) = MONTH(NOW()) AND YEAR(paidUntil) = YEAR(NOW()) AND paidUntil < DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expiredMembersMonth = $row['COUNT(memberno)'];
			
		// Look up renewed members this month
		$backDate = date('y-m', strtotime('-0 month'));
		$backDate = $backDate . '-01';
		
		$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND MONTH(m.paymentdate) = MONTH(NOW()) AND YEAR(m.paymentdate) = YEAR(NOW()) AND u.registeredSince < DATE_FORMAT(NOW(),'%Y-%m-01')";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$renewedMembersMonth = $row['COUNT(m.paymentid)'];
	
			
			
		// Look up new members last month
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembersMonthMinus1 = $row['COUNT(memberno)'];
			
		// Look up expired members last month
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(paidUntil) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(paidUntil) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expiredMembersMonthMinus1 = $row['COUNT(memberno)'];
			
		// Look up renewed members last month
		$backDate = date('y-m', strtotime('-1 month')) . '-01';
		$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND MONTH(m.paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(m.paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND u.registeredSince < DATE_FORMAT(NOW(),'$backDate')";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$renewedMembersMonthMinus1 = $row['COUNT(m.paymentid)'];
	
			
					
		// Look up new members 2 months back
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembersMonthMinus2 = $row['COUNT(memberno)'];
			
		// Look up expired members 2 months back
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(paidUntil) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(paidUntil) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expiredMembersMonthMinus2 = $row['COUNT(memberno)'];
			
		// Look up renewed members 2 months back
		$backDate = date('y-m', strtotime('-2 month')) . '-01';
		$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND MONTH(m.paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(m.paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND u.registeredSince < DATE_FORMAT(NOW(),'$backDate')";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$renewedMembersMonthMinus2 = $row['COUNT(m.paymentid)'];
	
			
			
		// Look up new members 3 months back
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembersMonthMinus3 = $row['COUNT(memberno)'];
			
		// Look up expired members 3 months back
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(paidUntil) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(paidUntil) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expiredMembersMonthMinus3 = $row['COUNT(memberno)'];
			
		// Look up renewed members 3 months back
		$backDate = date('y-m', strtotime('-3 month')) . '-01';
		$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND MONTH(m.paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(m.paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND u.registeredSince < DATE_FORMAT(NOW(),'$backDate')";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$renewedMembersMonthMinus3 = $row['COUNT(m.paymentid)'];
	
		// Look up new members 4 months back
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembersMonthMinus4 = $row['COUNT(memberno)'];
			
		// Look up expired members 4 months back
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(paidUntil) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(paidUntil) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expiredMembersMonthMinus4 = $row['COUNT(memberno)'];
			
		// Look up renewed members 4 months back
		$backDate = date('y-m', strtotime('-4 month')) . '-01';
		$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND MONTH(m.paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(m.paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND u.registeredSince < DATE_FORMAT(NOW(),'$backDate')";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$renewedMembersMonthMinus4 = $row['COUNT(m.paymentid)'];
			
		// Look up new members 5 months back
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembersMonthMinus5 = $row['COUNT(memberno)'];
			
		// Look up expired members 5 months back
		$selectMembers = "SELECT COUNT(memberno) from users WHERE MONTH(paidUntil) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(paidUntil) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expiredMembersMonthMinus5 = $row['COUNT(memberno)'];
			
		// Look up renewed members 5 months back
		$backDate = date('y-m', strtotime('-5 month')) . '-01';
		$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND MONTH(m.paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(m.paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND u.registeredSince < DATE_FORMAT(NOW(),'$backDate')";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$renewedMembersMonthMinus5 = $row['COUNT(m.paymentid)'];
			
		// Real active members last month
		$month_ini = new DateTime("first day of last month");
		$month_end = new DateTime("last day of last month");
		
		$monthBegin = $month_ini->format('Y-m-d'); // 2012-02-01
		$monthEnd = $month_end->format('Y-m-d'); // 2012-02-29
		
		$selectRealActives = "SELECT COUNT( DISTINCT userid ) FROM sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";
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
	
		$row = $result->fetch();
			$realActiveMembers = $row['COUNT( DISTINCT userid )'];
		
		// Real active member this month
		$month_ini = new DateTime("first day of this month");
		$month_end = new DateTime("last day of this month");
		
		$monthBegin = $month_ini->format('Y-m-d'); // 2012-02-01
		$monthEnd = $month_end->format('Y-m-d'); // 2012-02-29
		
		$selectRealActives = "SELECT COUNT( DISTINCT userid ) FROM sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";
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
	
		$row = $result->fetch();
			$realActiveMembersNow = $row['COUNT( DISTINCT userid )'];

?>

<center>
<div id='productoverview'>
 <table>
  <tr>
   <td><?php echo $lang['total-members']; ?>:</td>
   <td class='yellow fat right'><a href='members.php?filter=all'><?php echo $currentMembers; ?></a></td>
  </tr>
  <tr>
   <td><?php echo $lang['active-members']; ?>:</td>
   <td class='yellow fat right'><a href='members.php?filter=active'><?php echo $currentActiveMembers; ?></a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;<?php echo $lang['ow-members']; ?>:</td>
   <td class='yellow fat right'><a href='members.php?filter=5'><?php echo $currentMembersNormal; ?></a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;<?php echo $lang['ow-professionals']; ?>:</td>
   <td class='yellow fat right'><a href='members.php?filter=4'><?php echo $currentPros; ?></a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;<?php echo $lang['ow-volunteers']; ?>:</td>
   <td class='yellow fat right'><a href='members.php?filter=3'><?php echo $currentVolunteers; ?></a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;<?php echo $lang['ow-staff']; ?>:</td>
   <td class='yellow fat right'><a href='members.php?filter=2'><?php echo $currentStaff; ?></a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;<?php echo $lang['ow-admins']; ?>:</td>
   <td class='yellow fat right'><a href='members.php?filter=1'><?php echo $currentAdmins; ?></a></td>
  </tr>
  <tr>
   <td>&nbsp;&nbsp;<?php echo $lang['dispensed-last-month']; ?>:</td>
   <td class='yellow fat right'><a href='members-dispensed.php'><?php echo $realActiveMembers; ?></a></td>
  </tr>
  <tr>
   <td>&nbsp;&nbsp;<?php echo $lang['dispensed-this-month2']; ?>:</td>
   <td class='yellow fat right'><a href='members-dispensed.php?filter=this'><?php echo $realActiveMembersNow; ?></a></td>
  </tr>
 </table>
</div>
<br /><br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['global-details']; ?>
 </div>
 <div class='boxcontent'>
<table class="default">
 <tr>
  <th></th>
  <th><h3><?php echo date("M", strtotime("-4 months", strtotime("first day of last month") )); ?></h3></h3></th>
  <th><h3><?php echo date("M", strtotime("-3 months", strtotime("first day of last month") )); ?></h3></h3></th>
  <th><h3><?php echo date("M", strtotime("-2 months", strtotime("first day of last month") )); ?></h3></h3></th>
  <th><h3><?php echo date("M", strtotime("-1 months", strtotime("first day of last month") )); ?></h3></h3></th>
  <th><h3><?php echo date("M", strtotime("first day of last month")); ?></h3></th>
  <th><h3><?php echo date("M", strtotime("first day of this month")); ?></h3></th>
 </tr>
 <tr>
  <td class="left"><?php echo $lang['closeday-newmembers']; ?>:</td>
  <td><?php echo number_format($currentMembersMonthMinus5,0); ?> </td>
  <td><?php echo number_format($currentMembersMonthMinus4,0); ?> </td>
  <td><?php echo number_format($currentMembersMonthMinus3,0); ?> </td>
  <td><?php echo number_format($currentMembersMonthMinus2,0); ?> </td>
  <td><?php echo number_format($currentMembersMonthMinus1,0); ?> </td>
  <td><?php echo number_format($currentMembersMonth,0); ?> </td>
 </tr>
 <tr>
  <td class="left"><?php echo $lang['expired-members']; ?>:</td>
  <td><?php echo number_format($expiredMembersMonthMinus5,0); ?> </td>
  <td><?php echo number_format($expiredMembersMonthMinus4,0); ?> </td>
  <td><?php echo number_format($expiredMembersMonthMinus3,0); ?> </td>
  <td><?php echo number_format($expiredMembersMonthMinus2,0); ?> </td>
  <td><?php echo number_format($expiredMembersMonthMinus1,0); ?> </td>
  <td><?php echo number_format($expiredMembersMonth,0); ?> </td>
 </tr>
 <tr style="border-bottom: 1px solid white;">
  <td class="left"><?php echo $lang['closeday-renewedmembers']; ?>:</td>
  <td><?php echo number_format($renewedMembersMonthMinus5,0); ?> </td>
  <td><?php echo number_format($renewedMembersMonthMinus4,0); ?> </td>
  <td><?php echo number_format($renewedMembersMonthMinus3,0); ?> </td>
  <td><?php echo number_format($renewedMembersMonthMinus2,0); ?> </td>
  <td><?php echo number_format($renewedMembersMonthMinus1,0); ?> </td>
  <td><?php echo number_format($renewedMembersMonth,0); ?> </td>
 </tr>
 <tr>
  <td class="left"><?php echo $lang['expired-vs-renewed']; ?>:</td>
  <td><?php echo $renewedMembersMonthMinus5 - $expiredMembersMonthMinus5; ?> </td>
  <td><?php echo $renewedMembersMonthMinus4 - $expiredMembersMonthMinus4; ?> </td>
  <td><?php echo $renewedMembersMonthMinus3 - $expiredMembersMonthMinus3; ?> </td>
  <td><?php echo $renewedMembersMonthMinus2 - $expiredMembersMonthMinus2; ?> </td>
  <td><?php echo $renewedMembersMonthMinus1 - $expiredMembersMonthMinus1; ?> </td>
  <td><?php echo $renewedMembersMonth - $expiredMembersMonth; ?> </td>
 </tr>
 
 <tr>
  <td class="left"><?php echo $lang['active-growth']; ?>:</td>
  <td><?php echo $currentMembersMonthMinus5 + $renewedMembersMonthMinus5 - $expiredMembersMonthMinus5; ?> </td>
  <td><?php echo $currentMembersMonthMinus4 + $renewedMembersMonthMinus4 - $expiredMembersMonthMinus4; ?> </td>
  <td><?php echo $currentMembersMonthMinus3 + $renewedMembersMonthMinus3 - $expiredMembersMonthMinus3; ?> </td>
  <td><?php echo $currentMembersMonthMinus2 + $renewedMembersMonthMinus2 - $expiredMembersMonthMinus2; ?> </td>
  <td><?php echo $currentMembersMonthMinus1 + $renewedMembersMonthMinus1 - $expiredMembersMonthMinus1; ?> </td>
  <td><?php echo $currentMembersMonth + $renewedMembersMonth - $expiredMembersMonth; ?> </td>
 </tr>
</table>
</div>
</div>
<br /><br />

<div id='productoverview'>
 <table>
  <tr>
   <td><?php echo $lang['conslimit-all']; ?>:</td>
   <td class='yellow fat right'><?php echo number_format($currentMembersConsumptionLimit,0); ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['conslimit-active']; ?>:</td>
   <td class='yellow fat right'><?php echo number_format($currentActiveMembersConsumptionLimit,0); ?></td>
  </tr>
  <tr>
   <td>&raquo;&nbsp;<?php echo $lang['dispensed-this-month']; ?>:</td>
   <td class='yellow fat right'><?php echo number_format($quantityMonth,0); ?></td>
  </tr>
  <tr>
   <td>&raquo;&nbsp;<?php echo $lang['consumed']; ?> %:</td>
   <td class='yellow fat right'><?php echo number_format($consumptionPercentage,0); ?>%</td>
  </tr>
 </table>
</div>
<br /><br />


<?php displayFooter(); ?>
