<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// ini_set('max_execution_time', 0);
	// ignore_user_abort(true);
	
	$deleteSaleScript = <<<EOD
	
	  
	    $(document).ready(function() {
	
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					2: {
						sorter: "dates"
					}
				},
				sortList: [[2,1]]
			}); 
	
	});
	
EOD;

	pageStart("Contract status", NULL, $deleteSaleScript, "pmembership", NULL, "Contract status", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	  
	  <?php
	  
	try	{
		$dbh = new PDO('mysql:host=127.0.0.1:3306;', 'root', 'uqgj5nif5OqjtO3z');
 		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$dbh->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
		
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
 		
	}
	
$sql = $dbh->query('SHOW DATABASES');
$getAllDbs = $sql->fetchALL(PDO::FETCH_ASSOC);

$i = 1;

echo "<table id='mainTable' class='default'><thead><tr>
<th>#</th>
<th>Customer</th>
<th>Date signed</th>
<th>Club</th>
<th>CIF</th>
<th>Address</th>
<th>Name</th>
<th>DNI</th>
<th>Promotions?</th>
<th></th>
</tr></thead><tbody>";


foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_masterdb') {
		
		$purchaseidlist = '';
		$idlist = '';
		
	$domain = substr($database,4);
		
	$query = "SELECT db_pwd, customer FROM db_access WHERE domain = '$domain'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		if ($data) {

			$row = $data[0];
			$db_pwd = $row['db_pwd'];
			$number = $row['customer'];

			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";

		try	{
	 		$pdo4 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo4->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo4->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
	
	 		echo $output;
	 		exit();
		}	   		
		
		
		
		
		/*************** START QUERIES FROM HERE - pdo4! *****************/
		
			$query = "SELECT * FROM contract";
			try
			{
				$result = $pdo4->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$time = date("d-m-Y", strtotime($row['time']));
				$cif = $row['cif'];
				$dni = $row['dni'];
				$name = $row['name'];
				$club = $row['club'];
				$address = $row['address'];
				$box3 = $row['box3'];
				
			if ($box3 == '') {
				$promo = '';
			} else if ($box3 == 1) {
				$promo = 'Yes';
			} else {
				$promo = "<span style='color: red;'>No</span>";
			}
				
			if ($time == '01-01-1970') {
				$time = "<span style='color: white;'>00-00-0000</span>";
				$view = "";
			} else {
				$view = "<a href='client-contract.php?number=$number'><img src='images/eye.png' width='20'></a>";
			}
			
			$domain = substr($database, 4);
			
			$query = "SELECT shortName FROM customers WHERE number = '$number'";
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user2: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$shortName = $row['shortName'];	
							
			echo <<<EOD
<tr>
 <td>$number</td>
 <td>$shortName</td>
 <td>$time</td>
 <td>$club</td>
 <td>$cif</td>
 <td>$address</td>
 <td>$name</td>
 <td>$dni</td>
 <td>$promo</td>
 <td>$view</td>
</tr>

EOD;

				
			
			



	}
}
}

?>
</tbody>
</table>
<?php

displayFooter();