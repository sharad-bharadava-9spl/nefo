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
<td style='padding: 5px;'>10</td>
<td style='padding: 5px;'>10s</td>
<td style='padding: 5px;'>2</td>
<td style='padding: 5px;'>2s</td>
<td style='padding: 5px;'>3</td>
<td style='padding: 5px;'>3s</td>
<td style='padding: 5px;'>4</td>
<td style='padding: 5px;'>4s</td>
<td style='padding: 5px;'>5</td>
<td style='padding: 5px;'>5s</td>
<td style='padding: 5px;'>6</td>
<td style='padding: 5px;'>6s</td>
<td style='padding: 5px;'>7</td>
<td style='padding: 5px;'>7s</td>
</tr>";

foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_masterdb' && $database != 'ccs_terpsarmy'
	
	

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
		echo "NOW DOING: $database<br />";
		$query = "SELECT id from appointments LIMIT 1";
		try
		{
			$result = $pdo4->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		echo "DONE: $database<br /><br />";
		
/*			
		$query = "SELECT SUM(TABLE_ROWS) 
     FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = '{$database}';";
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
			$day5 = $row['SUM(TABLE_ROWS)'];
			
			echo "<tr><td>$database</td><td>$day5</td></tr>";
			
		$selectRows = "SELECT COUNT(id) FROM inddiscounts";
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