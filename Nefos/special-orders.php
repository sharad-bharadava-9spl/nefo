<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
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
		    
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Retiradas",
	    filename: "Retiradas" //do not include extension

	  });

	});
	
	});
	
EOD;

	pageStart("Orders & Appointments", NULL, $deleteSaleScript, "pmembership", NULL, "Orders & Appointments", $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
	 <table class='default'>
	  
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

echo "
<center><strong>Please note:</strong> Only clubs who have accepted or rejected any of the 3 modules will show in this list!</center><br /><br />
<table id='mainTable' class='default'>
 <tr>
  <td style='padding: 10px;'></td>
  <td style='padding: 10px; color: #5aa242; border-top: 2px solid #5aa242; border-right: 2px solid #5aa242; border-left: 2px solid #5aa242;' colspan='3' class='centered'><strong>ORDERS</strong></td>
  <td style='padding: 10px; color: #5aa242; border-top: 2px solid #5aa242; border-right: 2px solid #5aa242; border-left: 2px solid #5aa242;' colspan='5' class='centered'><strong>PRE-ORDERS</strong></td>
  <td style='padding: 10px; color: #5aa242; border-top: 2px solid #5aa242; border-right: 2px solid #5aa242; border-left: 2px solid #5aa242;' colspan='5' class='centered'><strong>APPOINTMENTS</strong></td>
 </tr>
 <tr>
  <td style='padding: 10px;' class='centered'><strong>DB</strong></td>
  <td style='padding: 10px; border-left: 2px solid #5aa242;' class='centered'><strong>Invitation</strong></td>
  <td style='padding: 10px;' class='centered'><strong>Orders</strong></td>
  <td style='padding: 10px;' class='centered'><strong>Fulfilled</strong></td>
  <td style='padding: 10px; border-left: 2px solid #5aa242;' class='centered'><strong>Invitation</strong></td>
  <td style='padding: 10px;' class='centered'><strong>Services</strong></td>
  <td style='padding: 10px;' class='centered'><strong>Invited</strong></td>
  <td class='centered'><strong>Pre-orders</strong></td>
  <td style='padding: 10px; border-right: 2px solid #5aa242;' class='centered'><strong>Fulfilled</strong></td>
  <td style='padding: 10px; border-left: 2px solid #5aa242;' class='centered'><strong>Invitation</strong></td>
  <td style='padding: 10px;' class='centered'><strong>Invited</strong></td>
  <td class='centered'><strong>Appointments</strong></td>
  <td style='padding: 10px; border-right: 2px solid #5aa242;' class='centered'><strong>Fulfilled</strong></td>
 </tr>";

foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_masterdb' && $database != 'ccs_kjell' && $database != 'ccs_irena' && $database != 'ccs_demo' && $database != 'ccs_ccstest'
	
	

) {
	//if ($database == 'ccs_nectar') {
		
		$purchaseidlist = '';
		$idlist = '';
		
	$domain = substr($database,4);
		
	$query = "SELECT db_pwd FROM db_access WHERE domain = '$domain'";
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
		
		// Orders = setting4
		// Pre-orders = setting3
		
		$query = "SELECT setting3, setting4, services, appointments FROM systemsettings";
		try
		{
			$results = $pdo4->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $results->fetch();
			$services = $row['services'];
			$setting3 = $row['setting3'];
			$setting4 = $row['setting4'];
			$appointments = $row['appointments'];
			
		$operator = $setting3 + $setting4 + $appointments;
			
		if ($setting3 == 0) {
			$setting3 = "";
		} else if ($setting3 == 1) {
			$setting3 = "<span style='color: #00ce1d;'>Accepted</span>";
		} else {
			$setting3 = "<span style='color: red;'>Rejected</span>";
		}
			
		if ($setting4 == 0) {
			$setting4 = "";
		} else if ($setting4 == 1) {
			$setting4 = "<span style='color: #00ce1d;'>Accepted</span>";
		} else {
			$setting4 = "<span style='color: red;'>Rejected</span>";
		}
			
		if ($appointments == 0) {
			$appointmentsSetting = "";
		} else if ($appointments == 1) {
			$appointmentsSetting = "<span style='color: #00ce1d;'>Accepted</span>";
		} else {
			$appointmentsSetting = "<span style='color: red;'>Rejected</span>";
		}
			
		if ($services == 1) {
			$servicesD = "Collection";
		} else if ($services == 10) {
			$servicesD = "Delivery";
		} else if ($services == 11) {
			$servicesD = "Collection + Delivery";
		} else {
			$servicesD = "";
		}
			
		
			
		$query = "SELECT COUNT(user_id) FROM users WHERE invited = 1";
		try
		{
			$results = $pdo4->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $results->fetch();
			$invited = $row['COUNT(user_id)'];
			
		if ($appointments > 0) {
			$query = "SELECT COUNT(user_id) FROM users WHERE citainvited = 1";
			try
			{
				$results = $pdo4->prepare("$query");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $results->fetch();
				$citainvited = $row['COUNT(user_id)'];
				
			$query = "SELECT COUNT(id) FROM appointments";
			try
			{
				$results = $pdo4->prepare("$query");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $results->fetch();
				$appointmentsNo = $row['COUNT(id)'];
				
			$query = "SELECT COUNT(id) FROM appointments WHERE fulfilled = 1";
			try
			{
				$results = $pdo4->prepare("$query");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $results->fetch();
				$appointmentsFulfilled = $row['COUNT(id)'];
				
		} else {
				$citainvited = 0;
				$appointmentsNo = 0;
				$appointmentsFulfilled = 0;
		}
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE puesto = 1 OR puesto = 2";
		try
		{
			$results = $pdo4->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $results->fetch();
			$orders = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE (puesto = 1 OR puesto = 2) AND dispensedFrom = 1";
		try
		{
			$results = $pdo4->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $results->fetch();
			$ordersFulfilled = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE puesto = 11 OR puesto = 22";
		try
		{
			$results = $pdo4->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $results->fetch();
			$preorders = $row['COUNT(saleid)'];

		$query = "SELECT COUNT(saleid) FROM sales WHERE (puesto = 11 OR puesto = 22) AND dispensedFrom = 1";
		try
		{
			$results = $pdo4->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $results->fetch();
			$preordersFulfilled = $row['COUNT(saleid)'];

		if ($operator > 0) {
			echo "
<tr>
 <td>$database</td>
 <td style='border-left: 2px solid #5aa242;'>$setting4</td>
 <td class='right'>$orders</td>
 <td class='right'>$ordersFulfilled</td>
 <td style='border-left: 2px solid #5aa242;'>$setting3</td>
 <td>$servicesD</td>
 <td class='right'>$invited</td>
 <td class='right'>$preorders</td>
 <td style='border-right: 2px solid #5aa242;' class='right'>$preordersFulfilled</td>
 <td style='border-left: 2px solid #5aa242;'>$appointmentsSetting</td>
 <td class='right'>$citainvited</td>
 <td class='right'>$appointmentsNo</td>
 <td style='border-right: 2px solid #5aa242;' class='right'>$appointmentsFulfilled</td>
</tr>";
		}
		

	}
}
}



echo "</table>";

?>

<?php

displayFooter();