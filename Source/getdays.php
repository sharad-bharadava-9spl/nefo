<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -$x DAY)";
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
			$sales = number_format($row['SUM(amount)'],0);
			$units = number_format($row['SUM(units)'],0);
			$quantity = number_format($row['SUM(realQuantity)'],0);
		
		$timestamp = date("d-m-Y", strtotime("-$x days"));
		
		$sales_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$units <span class="smallerfont">u.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore'><td class='centered' colspan='3'><a href='#' onclick='event.preventDefault(); loadMoreDays()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;