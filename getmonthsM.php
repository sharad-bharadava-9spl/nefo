<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$day = $_GET['day'];
	
	$newday = $day + 7;
	
	for ($x = $day; $x <= $newday; $x++) {
		
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -$x MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -$x MONTH))";
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
					
		$timestamp = date("m-Y", strtotime("-$x months", strtotime("first day of this month")));
		
		$sales_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td><a href='members.php?period=month&limit=$x' class='yellow'>$salesToday</a></td>
 </tr>
EOD;
	
	
	}
	
		$sales_row .= <<<EOD
	<tr id='loadMore3'><td class='centered' colspan='4'><a href='#' onclick='event.preventDefault(); loadMoreMonths()' class='yellow' style='font-size: 12px;'>[Cargar mas]</a></td></tr>
EOD;


	echo $sales_row;