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

	pageStart($lang['index-members'], NULL, $deleteSaleScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
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

echo "<table id='mainTable'><tr>
<td style='padding: 5px;'>DB</td>
<td style='padding: 5px;'>15</td>
<td style='padding: 5px;'>15s</td>
<td style='padding: 5px;'>16</td>
<td style='padding: 5px;'>16s</td>
<td style='padding: 5px;'>10</td>
<td style='padding: 5px;'>10s</td>
<td style='padding: 5px;'>11</td>
<td style='padding: 5px;'>11s</td>
<td style='padding: 5px;'>12</td>
<td style='padding: 5px;'>12s</td>
<td style='padding: 5px;'>13</td>
<td style='padding: 5px;'>13s</td>
<td style='padding: 5px;'>14</td>
<td style='padding: 5px;'>14s</td>
<td style='padding: 5px;'>13</td>
<td style='padding: 5px;'>13s</td>
<td style='padding: 5px;'>14</td>
<td style='padding: 5px;'>14s</td>
</tr>";

foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_masterdb'
	
	

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

		// $query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-04-23') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
		/*$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-15')";
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
			$day1 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-15') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day1s = $row['COUNT(saleid)'];
			
		
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-16')";
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
			$day2 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-16') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day2s = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-17')";
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
			$day3 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-17') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day3s = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-18')";
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
			$day4 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-18') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day4s = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-19')";
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
			$day5 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-19') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day5s = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-20')";
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
			$day6 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-20') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day6s = $row['COUNT(saleid)'];

		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-21')";
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
			$day7 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-21') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day7s = $row['COUNT(saleid)'];
			
			*/
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-23')";
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
			$day8 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-23') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day8s = $row['COUNT(saleid)'];		
				
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-24')";
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
			$day9 = $row['COUNT(saleid)'];
			
		$query = "SELECT COUNT(saleid) FROM sales WHERE DATE(saletime) = DATE('2020-05-24') AND (puesto = 1 OR puesto = 2 OR puesto = 11 OR puesto = 22)";
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
			$day9s = $row['COUNT(saleid)'];
			
		echo "<tr><td>$database</td><td>$day1</td><td>$day1s</td><td>$day2</td><td>$day2s</td><td>$day3</td><td>$day3s</td><td>$day4</td><td>$day4s</td><td>$day5</td><td>$day5s</td><td>$day6</td><td>$day6s</td><td>$day7</td><td>$day7s</td><td>$day8</td><td>$day8s</td><td>$day9</td><td>$day9s</td></tr>";				
		
		/*
				$selectUsersU = "ALTER TABLE `systemsettings` ADD `sameday` INT NOT NULL DEFAULT '1' AFTER `hours`; ";
				try
				{
					$result = $pdo4->prepare("$selectUsersU")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				echo "DONE: $database<br />";

		$query = "SELECT COUNT(saleid) FROM sales WHERE saletime
BETWEEN '2020-02-01'
AND '2020-02-29'";
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
			$day7 = $row['COUNT(saleid)'];
			
			
		echo "<tr><td>$database</td><td>$day7</td><td>$day21</td><td>$day22</td></tr>";				
				
						echo "NOW DOING: $database<br />";
				$selectUsersU = "ALTER TABLE `salesdetails` ADD `discountType` INT NOT NULL DEFAULT '0' AFTER `realQuantity`, ADD `discountPercentage` DECIMAL(15,2) NOT NULL DEFAULT '0' AFTER `discountType`, ADD `happyhourDiscount` DECIMAL(15,2) NOT NULL DEFAULT '0' AFTER `discountPercentage`, ADD `volumeDiscount` DECIMAL(15,2) NOT NULL DEFAULT '0' AFTER `happyhourDiscount`";
				try
				{
					$result = $pdo4->prepare("$selectUsersU")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				echo "UPDATED: $database<br /><br />";

		$query = "SELECT puesto FROM sales WHERE puesto <> ''";
		try
		{
			$result = $pdo4->prepare("$query");
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
				$puesto = $row['puesto'];
				
			echo "$database - $puesto<br/>";
			
		}
		
		$query = "SELECT email FROM closing_mails WHERE email = 'prateektest@mailintor.com' OR email = 'testin@abcd.com'";
		try
		{
			$result = $pdo4->prepare("$query");
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
				$email = $row['email'];
				
			echo "$database - $email<br/>";
			
		}
		

		
		$query = "SELECT fastVisitor FROM systemsettings";
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
			$fastVisitor = $row['fastVisitor'];
			
		echo "$database - $fastVisitor<br />";

		
		// Find all category IDs who are units
		$query = "SELECT id FROM categories WHERE type = 0 AND id > 2";
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
	
		while ($row = $results->fetch()) {
			$catid = $row['id'];
			$idlist .= $catid . ",";
		}
		
		$idlist = substr($idlist, 0, -1);
		
		if ($idlist != '') {
			
			// Find all purchaseid's in said categories
			$query = "SELECT purchaseid FROM purchases WHERE category IN ($idlist)";
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
		
			while ($row = $results->fetch()) {
				$purchaseid = $row['purchaseid'];
				$purchaseidlist .= $purchaseid . ",";
			}
			
			$purchaseidlist = substr($purchaseidlist, 0, -1);
			
			if ($purchaseidlist != '') {
				
				// Look in salesdetails for sales in decimals
				$query = "SELECT COUNT(saleid) FROM `salesdetails` WHERE purchaseid IN ($purchaseidlist) AND quantity <> floor(quantity)";
				echo $query;
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
					$sales = $row['COUNT(saleid)'];
				
				echo "<tr><td>$database</td><td>$sales</td><td></td></tr>";
				
			}
		
		}


		$selectUsersU = "SELECT shakePercentage, COUNT(shakePercentage) FROM `closingdetails` GROUP BY shakePercentage";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$shakePercentage = $row['shakePercentage'];
			$quantity = $row['COUNT(shakePercentage)'];
			
		echo "<tr><td>$database</td><td>$shakePercentage</td><td>$quantity</td></tr>";
		$selectRows = "SELECT COUNT(user_id) FROM users";
		$rowCount = $pdo4->query("$selectRows")->fetchColumn();
		
		$totalCount = $totalCount + $rowCount;
		
		echo "$database - $rowCount ($totalCount)<br />";

/*		$selectRows = "SELECT COUNT(id) FROM inddiscounts";
		$rowCount2 = $pdo4->query("$selectRows")->fetchColumn();
		
		$selectRows = "SELECT COUNT(id) FROM b_catdiscounts";
		$rowCount3 = $pdo4->query("$selectRows")->fetchColumn();
		
		$selectRows = "SELECT COUNT(id) FROM b_inddiscounts";
		$rowCount4 = $pdo4->query("$selectRows")->fetchColumn();
		
		echo "$database - CAT: $rowCount<br />";
		echo "$database - IND: $rowCount2<br />";
		echo "$database - BAR CAT: $rowCount3<br />";
		echo "$database - BAR IND: $rowCount4<br /><br />";
		
		$selectUsersU = "SELECT trialMode FROM systemsettings";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$trialMode = $row['trialMode'];
	
		if ($trialMode == 1) {
					
			// Calculate trial time left
			try
			{
				$result = $pdo->prepare("SELECT time FROM logins WHERE domain = '$domain' ORDER BY time ASC LIMIT 1");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$loginTime = date("Y-m-d", strtotime($row['time']));
				
					
			$now = date("Y-m-d");
			
			$datediff = round((strtotime($now) - strtotime($loginTime)) / (60 * 60 * 24));
			
			$remainingTrial = 30 - $datediff;
			
			echo "$database: $remainingTrial left<br />";
			if ($remainingTrial < 0 && $remainingTrial > -1000 ) {
				
				$selectUsersU = "UPDATE systemsettings SET trialMode = 0";
				try
				{
					$result = $pdo4->prepare("$selectUsersU")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
			echo "$database: Trial timer removed<br /><br />";
			}
			
		}*/

		/*
		echo $database;
		$selectRows = "SELECT COUNT(openingid) FROM shiftopen WHERE DATE(openingtime) = DATE('2019-11-05')";
		$rowCount = $pdo4->query("$selectRows")->fetchColumn();

		echo "DONE: 2019-11-05: $database - $rowCount<br />";	
		
		$selectRows = "SELECT COUNT(openingid) FROM shiftopen WHERE DATE(openingtime) = DATE('2019-11-04')";
		$rowCount = $pdo4->query("$selectRows")->fetchColumn();

		echo "DONE: 2019-11-04: $database - $rowCount<br />";	
		
		$selectRows = "SELECT COUNT(openingid) FROM shiftopen WHERE DATE(openingtime) = DATE('2019-11-03')";
		$rowCount = $pdo4->query("$selectRows")->fetchColumn();

		echo "DONE: 2019-11-03: $database - $rowCount<br />";	
		
		$selectRows = "SELECT COUNT(openingid) FROM shiftopen WHERE DATE(openingtime) = DATE('2019-11-02')";
		$rowCount = $pdo4->query("$selectRows")->fetchColumn();

		echo "DONE: 2019-11-02: $database - $rowCount<br />";	
		
		$selectRows = "SELECT COUNT(openingid) FROM shiftopen WHERE DATE(openingtime) = DATE('2019-11-01')";
		$rowCount = $pdo4->query("$selectRows")->fetchColumn();

		echo "DONE: 2019-11-01: $database - $rowCount<br />";	
		$selectUsersU = "SELECT * FROM users WHERE email LIKE ('%cscgest%')";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];
			$email = $row['email'];
			$userPass = $row['userPass'];
			$userGroup = $row['userGroup'];
			
		if ($first_name != '') {
			
			echo "$database - $first_name $last_name ($email) $userPass - $userGroup<br />";
			
		}
		
		
		
		
			*/
/*	
		
		
		$selectUsersU = "SELECT workertracking FROM systemsettings";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$workertracking = $row['workertracking'];
	
		if ($workertracking == 1) {
			echo "$database<br />";	
					
		}*/
		/*
		
		try
		{
			$result = $pdo4->prepare("INSERT INTO `categories` (`id`, `time`, `name`, `description`, `type`, `sortorder`) VALUES ('1', CURRENT_TIMESTAMP, 'Flowers', '', '0', '9999'), ('2', CURRENT_TIMESTAMP, 'Extract', '', '0', '9999')")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
		$selectUsersU = "SELECT dooropenfor, workertracking FROM systemsettings";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dooropenfor = $row['dooropenfor'];
			$workertracking = $row['workertracking'];

		$selectUsersU = "SELECT chipincomecard, chipincome FROM closing LIMIT 1";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$chipincomecard = $row['chipincomecard'];
			$chipincome = $row['chipincome'];

		$selectUsersU = "SELECT chipincomecard, chipincome FROM shiftclose LIMIT 1";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$chipincomecard2 = $row['chipincomecard'];
			$chipincome2 = $row['chipincome'];
	  
		$selectUsersU = "SELECT value FROM closingdetails LIMIT 1";
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$value = $row['value'];
			
		echo "$database : $dooropenfor - $workertracking - $chipincomecard - $chipincome - $chipincomecard2 - $chipincome2 - $value <br />";
		
		*/
			
//	  $i++;

	}
}
}

echo "</table>";

?>

<?php

displayFooter();