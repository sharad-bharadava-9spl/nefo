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
	
	ini_set('max_execution_time', 0);
	ignore_user_abort(true);
	
	

	pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_masterdb') {
	//if ($database == 'ccs_cloud') {
		
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

		$selectUsersU = "SELECT COUNT(movementid) FROM productmovements WHERE category = 0";
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
			$visitRegistration = $row['COUNT(movementid)'];
			
		$count = $count + $visitRegistration;
		
		echo "$database - $visitRegistration ($count)<br />";

		
/*		$selectUsersU = "SELECT movementid, purchaseid, category FROM productmovements";
		try
		{
			$results = $pdo4->prepare("$selectUsersU");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rows = $results->fetch()) {
			
			$movementid = $rows['movementid'];
			$purchaseid = $rows['purchaseid'];
			$category = $rows['category'];
			
			if ($category == 0) {
				
				$query = "SELECT category FROM purchases WHERE purchaseid = '$purchaseid'";
				try
				{
					$result = $pdo4->prepare("$query");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user2: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$row = $result->fetch();
					$category = $row['category'];
					
				if ($category == '') {
					$category = 0;
				}
				
				if ($category > 0) {
					
					$query = "UPDATE productmovements SET category = '$category' WHERE movementid = '$movementid'";
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
					
				}

				
			}
			
			
			echo "$database - YES<br />";
			
		}
		*/
		
		/*
		$query = "ALTER TABLE `productmovements` ADD `category` INT NOT NULL DEFAULT '0' AFTER `user_id`;";
		try
		{
			$result = $pdo4->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user $database: ' . $e->getMessage();
				echo $error;
				exit();
		}
		echo "Done: $database<br />";

		$selectRows = "SELECT COUNT(user_id) FROM users WHERE userGroup = 6 AND DATE(registeredSince) > DATE('2019-09-30')";
		$rowCount = $pdo4->query("$selectRows")->fetchColumn();
		
		if ($rowCount > 5) {
			echo "$database - $rowCount<br />";
			$i++;
		}*/
/*

		$selectUsersU = "SELECT visitRegistration FROM systemsettings";
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
			$visitRegistration = $row['visitRegistration'];
			
		if ($visitRegistration == 1) {
			echo "$database - YES<br />";
		}
	*/
/*
		$selectUsersU = "SELECT email FROM closing_mails WHERE email = 'fabresitoh@icloud.com'";
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
			$email = $row['email'];
			
		echo "$database - $email<br />";
	*/
		/*
		$selectUsersU = "SELECT groupName FROM usergroups WHERE userGroup = 10";
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
			$groupName1 = $row['groupName'];

		$selectUsersU = "SELECT groupName FROM usergroups WHERE userGroup = 11";
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
			$groupName2 = $row['groupName'];
			
		echo $database . " - " . $groupName1 . " - " . $groupName2 . "<br />";
*/
/*
		$selectUsersU = "SELECT userPass, userGroup, domain FROM users WHERE email = 'eli@cscgest.com'";
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
			$userPass = $row['userPass'];
			$userGroup = $row['userGroup'];
			$domain = $row['domain'];
			
			if ($userPass != '') {
			
		echo "$database - $userPass - $userGroup - $domain<br />";
		
	}

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
			if ($remainingTrial < 0) {
				
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
			
		}
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
		*/
		/*
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