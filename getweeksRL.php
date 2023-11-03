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
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND YEARWEEK(donationTime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1)";
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
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE YEARWEEK(paymentdate,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1)";
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
		$selectSales = "SELECT SUM(amount) from sales WHERE YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND direct < 3";
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
		$selectSales = "SELECT SUM(amount) from b_sales WHERE YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND direct < 3";
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
		
		$selectFees = "SELECT SUM(amount) FROM expenses WHERE YEARWEEK(registertime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1)";
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
  <td>{$expr(number_format($donationsToday,2, '.', ''))}</td>
  <td>{$expr(number_format($feesToday,2, '.', ''))}</td>
  <td>{$expr(number_format($totalToday,2, '.', ''))}</td>
  <td>{$expr(number_format($expensesToday,2, '.', ''))}</td>
  <td>{$expr(number_format($plusToday,2, '.', ''))}</td>
 </tr>
EOD;		
	
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore2'><td class='centered' colspan='4'><a href='#' onclick='event.preventDefault(); loadMoreWeeks()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;