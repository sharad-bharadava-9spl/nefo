<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	
	session_start();
	
	if (isset($_POST['resubmit'])) {
				
		foreach($_POST['comment'] as $sale) {
			$db = $sale['db'];
			$content = str_replace("'","\'",str_replace('%', '&#37;', trim($sale['content'])));
			$orig = $sale['orig'];
			
			if ($content != $orig) {
			
				$selectUsersU = "UPDATE activity SET comment = '$content' WHERE sqldb = '$db'";
				try
				{
					$result = $pdo3->prepare("$selectUsersU")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
			}
		}
		
		$_SESSION['successMessage'] = 'Comments updated succesfully!';

		
	}


	
		
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Socios",
			    filename: "Socios" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$('#mainTable').tablesorter({
				usNumberFormat: false,
				headers: {
					4: {
						sorter: "currency"
					}
				}
			}); 
			
		}); 
		
EOD;


	pageStart("Key club data", NULL, $memberScript, "pmembership", NULL, "Key club data", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<form id="registerForm" action="" method="POST">
	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>Club</th>
	    <th class='centered'>Members</th>
	    <th class='centered'>Active members</th>
	    <th class='centered'>Dispensed <?php echo Date('F', strtotime($currentMonth . " last month")); ?></th>
	    <th class='centered'>Revenue <?php echo Date('F', strtotime($currentMonth . " last month")); ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
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
foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_irena' && $database != 'ccs_masterdb') {
		
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
		$customer = $row['customer'];

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
		
	$selectMembers = "SELECT shortName from customers WHERE number = '$customer'";
	try
	{
		$result = $pdo3->prepare("$selectMembers");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$clubname = $row['shortName'];
		
	$selectMembers = "SELECT COUNT(memberno) from users WHERE userGroup <> 8";
	try
	{
		$result = $pdo4->prepare("$selectMembers");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$currentMembers = $row['COUNT(memberno)'];
		
	$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND (DATE(paidUntil) >= DATE(NOW()) OR exento = 1))";
	try
	{
		$result = $pdo4->prepare("$selectMembers");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$activeMembers = $row['COUNT(memberno)'];
		
		// Real active members last month
		$month_ini = new DateTime("first day of last month");
		$month_end = new DateTime("last day of last month");
		
		$monthBeginLast = $month_ini->format('Y-m-d'); // 2012-02-01
		$monthEndLast = $month_end->format('Y-m-d'); // 2012-02-29
		
		$selectRealActives = "SELECT COUNT( DISTINCT userid ) FROM sales WHERE saletime BETWEEN '$monthBeginLast' AND '$monthEndLast'";
		try
		{
			$result = $pdo4->prepare("$selectRealActives");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$realActiveMembers = $row['COUNT( DISTINCT userid )'];
			
		// Look up dispensed today cash
		$selectSales = "SELECT SUM(amount) from sales WHERE saletime BETWEEN '$monthBeginLast' AND '$monthEndLast' AND direct < 3";
		try
		{
			$result = $pdo4->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayCash = $row['SUM(amount)'];
	
		// Look up bar sales today cash
		$selectSales = "SELECT SUM(amount) from b_sales WHERE saletime BETWEEN '$monthBeginLast' AND '$monthEndLast' AND direct < 3";
		try
		{
			$result = $pdo4->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBarCash = $row['SUM(amount)'];

	// Look up donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo <> 3 AND donationTime BETWEEN '$monthBeginLast' AND '$monthEndLast'";
	try
	{
		$result = $pdo4->prepare("$selectDonations");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$donations = $row['SUM(amount)'];
			
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$monthBeginLast' AND '$monthEndLast'";
	try
	{
		$result = $pdo4->prepare("$selectMembershipFees");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$membershipFees = $row['SUM(amountPaid)'];
		
		$revenue = $donations + $membershipFees + $salesTodayCash + $salesTodayBarCash;

			
			echo sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%s &euro;</td>

 	   </tr>",
	  $clubname, $currentMembers, $activeMembers, $realActiveMembers, $revenue);
	  
	  
	  $i++;

	}
}
}

?>
</table><br />
 <input type='hidden' name='resubmit' value='yes' />
 <button type="submit">OK</button><br />
</form>		

<?php

displayFooter();