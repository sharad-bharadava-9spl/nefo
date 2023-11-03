<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		//$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x WEEK))";
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1)";
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
		
		$timestamp = $lang['dispensary-weeksago-1'] . $x . $lang['dispensary-weeksago-2'];
		
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
	<tr id='loadMore2'><td class='centered' colspan='4'><a href='#' onclick='event.preventDefault(); loadMoreWeeks()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;