<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH))";
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
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH))";
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
		$selectSales = "SELECT SUM(amount) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND direct < 3";
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
		$selectSales = "SELECT SUM(amount) from b_sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND direct < 3";
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
		
		$selectFees = "SELECT SUM(amount) FROM expenses WHERE MONTH(registertime) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(registertime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH))";
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
			
		$timestamp = date("m-Y", strtotime("-$x months", strtotime("first day of this month")));
		
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
	<tr id='loadMore3'><td class='centered' colspan='3'><a href='#' onclick='event.preventDefault(); loadMoreMonths()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;