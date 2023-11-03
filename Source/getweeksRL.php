<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationsToday = $row['SUM(amount)'];
			
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$x WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$feesToday = $row['SUM(amountPaid)'];
			
		// Look up direct dispensed today
		$selectSales = "SELECT SUM(amount) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x WEEK)) AND direct < 3";
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
			$salesTodayCash = $row['SUM(amount)'];
			
		// Look up direct bar sales today
		$selectSales = "SELECT SUM(amount) from b_sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x WEEK)) AND direct < 3";
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
			$salesTodayBarCash = $row['SUM(amount)'];
		
		$selectFees = "SELECT SUM(amount) FROM expenses WHERE WEEK(registertime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND YEAR(registertime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expensesToday = $row['SUM(amount)'];
			
			
		$totalToday = $donationsToday + $feesToday + $salesTodayCash + $salesTodayBarCash;
		
		$plusToday = $totalToday - $expensesToday;
			
		$timestamp = $lang['dispensary-weeksago-1'] . $x . $lang['dispensary-weeksago-2'];
		
		$sales_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$donationsToday <span class="smallerfont">&euro;</span></td>
  <td>$feesToday <span class="smallerfont">&euro;</span></td>
  <td>$totalToday <span class="smallerfont">&euro;</span></td>
  <td>$expensesToday <span class="smallerfont">&euro;</span></td>
  <td>$plusToday <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;		
	
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore2'><td class='centered' colspan='3'><a href='#' onclick='event.preventDefault(); loadMoreWeeks()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;