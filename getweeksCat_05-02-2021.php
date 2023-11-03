<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	$cat = $_GET['cat'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = '$cat' AND WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$x WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$x WEEK))";
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
			$sales = number_format($row['SUM(d.amount)'],0);
			
		$query = "SELECT type FROM categories WHERE id = '$cat'";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowT = $result->fetch();
			$type = $rowT['type'];
			
		if ($cat < 3 || $type == 1) {
			$gramorunits = 'g';
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
		} else {
			$gramorunits = 'u';
			$quantity = number_format($row['SUM(d.realQuantity)'],0);
		}
		
		$timestamp = $lang['dispensary-weeksago-1'] . $x . $lang['dispensary-weeksago-2'];
		
		$sales_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont"> $gramorunits.</span></td>
  <td>$sales <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
 </tr>
EOD;
	
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore2C$cat'><td class='centered' colspan='4'><a href='#' onclick='event.preventDefault(); loadMoreWeeksCat($cat)' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;