<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		$selectSales = "SELECT COUNT(memberno) from users WHERE DATE(registeredSince) = DATE_ADD(DATE(NOW()), INTERVAL -$x DAY)";
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
			$salesToday = $row['COUNT(memberno)'];
		
		$timestamp = date("d-m-Y", strtotime("-$x days"));
		
		$sales_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td><a href='members.php?period=day&limit=$x' class='yellow'>$salesToday</a></td>
 </tr>
EOD;
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore'><td class='centered' colspan='4'><a href='#' onclick='event.preventDefault(); loadMoreDays()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;