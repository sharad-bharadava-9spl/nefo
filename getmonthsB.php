<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		$selectSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH))";
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
			$b_salesToday = number_format($row['SUM(amount)'],2, '.', '');
			$unitsTotToday = number_format($row['SUM(unitsTot)'],2, '.', '');
					
		$timestamp = date("m-Y", strtotime("-$x months", strtotime("first day of this month")));
		
		$sales_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$unitsTotToday </td>
  <td>$b_salesToday </td>
 </tr>
EOD;
	
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore3'><td class='centered' colspan='4'><a href='#' onclick='event.preventDefault(); loadMoreMonths()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;