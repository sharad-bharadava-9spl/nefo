<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		$selectSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x WEEK))";
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
			$b_salesToday = $row['SUM(amount)'];
			$unitsTotToday = $row['SUM(unitsTot)'];
		
		$timestamp = $lang['dispensary-weeksago-1'] . $x . $lang['dispensary-weeksago-2'];
		
		$sales_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$unitsTotToday <span class="smallerfont">u.</span></td>
  <td>$b_salesToday <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;
	
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore2'><td class='centered' colspan='3'><a href='#' onclick='event.preventDefault(); loadMoreWeeks()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;