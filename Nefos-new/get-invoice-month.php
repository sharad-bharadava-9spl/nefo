<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['month'];
	$number = $_GET['number'];
	
	$newday = $day + 10;
	
	for ($x = $day; $x <= $newday; $x++) {

		$dateOperator = "MONTH(invdate) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(invdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH))";
		$timestamp = date("M Y", strtotime("-$x months", strtotime("first day of this month") ));

		// Look up invoice amounts
		$query = "SELECT SUM(amount) FROM invoices WHERE customer = '$number' AND brand = 'SW' AND $dateOperator";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$amountSW = $row['SUM(amount)'];
			
		// Look up invoice amounts
		$query = "SELECT SUM(amount) FROM invoices WHERE customer = '$number' AND brand = 'HW' AND $dateOperator";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$amountHW = $row['SUM(amount)'];
			
		// Look up invoice amounts
		$query = "SELECT SUM(amount) FROM invoices WHERE customer = '$number' AND brand <> 'SW' AND brand <> 'HW' AND $dateOperator";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$amountOther = $row['SUM(amount)'];
			
		$totalAmount = $amountSW + $amountHW + $amountOther;
			
			
		$timestamp = date("m-Y", strtotime("-$x months", strtotime("first day of this month")));
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp</td>
  <td>{$expr(number_format($amountSW,2))} <span class="smallerfont">&euro;</span></td>
  <td>{$expr(number_format($amountHW,2))} <span class="smallerfont">&euro;</span></td>
  <td>{$expr(number_format($amountOther,2))} <span class="smallerfont">&euro;</span></td>
  <td><strong>{$expr(number_format($totalAmount,2))} <span class="smallerfont">&euro;</span></strong></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 <tr id="loadMore3">
  <td class="centered" colspan="5"><a href="#" onclick="event.preventDefault(); loadMoreMonths()" class='yellow' style='font-size: 12px;'>[Load more]</a></td>
 </tr>
EOD;

	echo $month_row;