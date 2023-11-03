<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -$x DAY)";
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
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE_ADD(DATE(NOW()), INTERVAL -$x DAY)";
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
		$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -$x DAY) AND direct < 3";
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
		$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -$x DAY) AND direct < 3";
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
		
		$selectFees = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE_ADD(DATE(NOW()), INTERVAL -$x DAY)";
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
			
		$timestamp = date("d-m-Y", strtotime("-$x days"));
		
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
	<tr id='loadMore'><td class='centered' colspan='3'><a href='#' onclick='event.preventDefault(); loadMoreDays()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;