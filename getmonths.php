<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH))";
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
			$sales = number_format($row['SUM(amount)'],2, '.', '');
			$units = number_format($row['SUM(units)'],2, '.', '');
			$quantity = number_format($row['SUM(realQuantity)'],2, '.', '');
					
		$timestamp = date("m-Y", strtotime("-$x months", strtotime("first day of this month")));
		
		$sales_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity </td>
  <td>$units </td>
  <td>$sales </td>
 </tr>
EOD;
	
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore3'><td class='centered' colspan='4'><a href='#' onclick='event.preventDefault(); loadMoreMonths()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;