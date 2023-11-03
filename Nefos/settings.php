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

	pageStart("System settings", NULL, $deleteSaleScript, "pmembership", NULL, "System settings", $_SESSION['successMessage'], $_SESSION['errorMessage']);
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

echo <<<EOD
<table id='mainTable' class='default'>
 <tr>
<td>DB</td>
<td>highRollerWeekly</td>
<td>minAge</td>
<td>closingMail</td>
<td>dispensaryGift</td>
<td>barGift</td>
<td>menuType</td>
<td>medicalDiscount</td>
<td>logouttime</td>
<td>logoutredir</td>
<td>dispDonate</td>
<td>dispExpired</td>
<td>dispenseLimit</td>
<td>showAge</td>
<td>showGender</td>
<td>keepNumber</td>
<td>membershipFees</td>
<td>medicalDiscountPercentage</td>
<td>bankPayments</td>
<td>creditOrDirect</td>
<td>visitRegistration</td>
<td>cropOrNot</td>
<td>puestosOrNot</td>
<td>openAndClose</td>
<td>barMenuType</td>
<td>flowerLimit</td>
<td>extractLimit</td>
<td>realWeight</td>
<td>showStock</td>
<td>showOrigPrice</td>
<td>checkoutDiscount</td>
<td>consumptionMin</td>
<td>consumptionMax</td>
<td>showStockBar</td>
<td>showOrigPriceBar</td>
<td>barTouchscreen</td>
<td>iPadReaders</td>
<td>cashdro</td>
<td>creditchange</td>
<td>expirychange</td>
<td>exentoset</td>
<td>menusortdisp</td>
<td>menusortbar</td>
<td>dispsig</td>
<td>barsig</td>
<td>openmenu</td>
<td>keypads</td>
<td>moneycount</td>
<td>customws</td>
<td>negcredit</td>
<td>language</td>
<td>nobar</td>
<td>sigtablet</td>
<td>entrysys</td>
<td>entrysysstay</td>
<td>entrysyssecs</td>
<td>dooropener</td>
<td>cuotaincrement</td>
<td>checkoutDiscountBar</td>
<td>chipcost</td>
<td>fingerprint</td>
<td>pagination</td>
<td>dooropenfor</td>
<td>workertracking</td>
<td>fullmenu</td>
<td>presignup</td>
<td>signupcode</td>
<td>allowvisitors</td>
<td>flowerLimitPercentage</td>
<td>extractLimitPercentage</td>
<td>fastVisitor</td>
<td>saldoGift</td>
 </tr>
EOD;

foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_masterdb') {
	//if ($database == 'ccs_irena') {
		
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
		
	$selectSettings = "SELECT highRollerWeekly, minAge, closingMail, dispensaryGift, barGift, menuType, medicalDiscount, logouttime, logoutredir, dispDonate, dispExpired, dispenseLimit, showAge, showGender, keepNumber, membershipFees, medicalDiscountPercentage, bankPayments, creditOrDirect, visitRegistration, cropOrNot, puestosOrNot, openAndClose, barMenuType, flowerLimit, extractLimit, realWeight, showStock, showOrigPrice, checkoutDiscount, consumptionMin, consumptionMax, showStockBar, showOrigPriceBar, barTouchscreen, iPadReaders, cashdro, creditchange, expirychange, exentoset, menusortdisp, menusortbar, dispsig, barsig, openmenu, keypads, moneycount, customws, negcredit, language, nobar, sigtablet, entrysys, entrysysstay, entrysyssecs, dooropener, cuotaincrement, checkoutDiscountBar, chipcost, fingerprint, pagination, dooropenfor, workertracking, fullmenu, signupcode, presignup, allowvisitors, flowerLimitPercentage, extractLimitPercentage, fastVisitor, saldoGift FROM systemsettings";
	try
	{
		$result = $pdo4->prepare("$selectSettings");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
  	    $highRollerWeekly = $row['highRollerWeekly'];
  	    $minAge = $row['minAge'];
  	    $closingMail = $row['closingMail'];
  	    $dispensaryGift = $row['dispensaryGift'];
  	    $barGift = $row['barGift'];
  	    $menuType = $row['menuType'];
  	    $medicalDiscount = $row['medicalDiscount'];
		$logouttime = $row['logouttime'];
		$logoutredir = $row['logoutredir'];
		$dispDonate = $row['dispDonate'];
		$dispExpired = $row['dispExpired'];
		$dispenseLimit = $row['dispenseLimit'];
		$showAge = $row['showAge'];
		$showGender = $row['showGender'];
		$keepNumber = $row['keepNumber'];
		$membershipFees = $row['membershipFees'];
		$medicalDiscountPercentage = $row['medicalDiscountPercentage'];
		$bankPayments = $row['bankPayments'];
		$creditOrDirect = $row['creditOrDirect'];
		$visitRegistration  = $row['visitRegistration'];
		$cropOrNot  = $row['cropOrNot'];
		$puestosOrNot  = $row['puestosOrNot'];
		$openAndClose  = $row['openAndClose'];
		$barMenuType  = $row['barMenuType'];
		$flowerLimit  = $row['flowerLimit'];
		$extractLimit  = $row['extractLimit'];
		$realWeight  = $row['realWeight'];
		$showStock  = $row['showStock'];
		$showOrigPrice  = $row['showOrigPrice'];
		$checkoutDiscount  = $row['checkoutDiscount'];
		$consumptionMin  = $row['consumptionMin'];
		$consumptionMax  = $row['consumptionMax'];
		$showStockBar  = $row['showStockBar'];
		$showOrigPriceBar  = $row['showOrigPriceBar'];
		$barTouchscreen  = $row['barTouchscreen'];
		$iPadReaders  = $row['iPadReaders'];
		$cashdro  = $row['cashdro'];
		$creditchange  = $row['creditchange'];
		$expirychange  = $row['expirychange'];
		$exentoset  = $row['exentoset'];
		$menusortdisp  = $row['menusortdisp'];
		$menusortbar  = $row['menusortbar'];
		$dispsig  = $row['dispsig'];
		$barsig  = $row['barsig'];
		$openmenu  = $row['openmenu'];
		$keypads  = $row['keypads'];
		$moneycount  = $row['moneycount'];
		$customws  = $row['customws'];
		$negcredit  = $row['negcredit'];
		$language  = $row['language'];
		$nobar  = $row['nobar'];
		$sigtablet  = $row['sigtablet'];
		$entrysys  = $row['entrysys'];
		$entrysysstay  = $row['entrysysstay'];
		$entrysyssecs  = $row['entrysyssecs'];
		$dooropener  = $row['dooropener'];
		$cuotaincrement  = $row['cuotaincrement'];
		$checkoutDiscountBar  = $row['checkoutDiscountBar'];
		$chipcost  = $row['chipcost'];
		$fingerprint  = $row['fingerprint'];
		$pagination  = $row['pagination'];
		$dooropenfor  = $row['dooropenfor'];
		$workertracking  = $row['workertracking'];
		$fullmenu  = $row['fullmenu'];
		$presignup  = $row['presignup'];
		$signupcode  = $row['signupcode'];
		$allowvisitors  = $row['allowvisitors'];
		$flowerLimitPercentage  = $row['flowerLimitPercentage'];
		$extractLimitPercentage  = $row['extractLimitPercentage'];
		$fastVisitor  = $row['fastVisitor'];
		$saldoGift  = $row['saldoGift'];
		
echo "<tr>";
echo "<td>$database</td>";
echo "<td>$highRollerWeekly</td>";
echo "<td>$minAge</td>";
echo "<td>$closingMail</td>";
echo "<td>$dispensaryGift</td>";
echo "<td>$barGift</td>";
echo "<td>$menuType</td>";
echo "<td>$medicalDiscount</td>";
echo "<td>$logouttime</td>";
echo "<td>$logoutredir</td>";
echo "<td>$dispDonate</td>";
echo "<td>$dispExpired</td>";
echo "<td>$dispenseLimit</td>";
echo "<td>$showAge</td>";
echo "<td>$showGender</td>";
echo "<td>$keepNumber</td>";
echo "<td>$membershipFees</td>";
echo "<td>$medicalDiscountPercentage</td>";
echo "<td>$bankPayments</td>";
echo "<td>$creditOrDirect</td>";
echo "<td>$visitRegistration</td>";
echo "<td>$cropOrNot</td>";
echo "<td>$puestosOrNot</td>";
echo "<td>$openAndClose</td>";
echo "<td>$barMenuType</td>";
echo "<td>$flowerLimit</td>";
echo "<td>$extractLimit</td>";
echo "<td>$realWeight</td>";
echo "<td>$showStock</td>";
echo "<td>$showOrigPrice</td>";
echo "<td>$checkoutDiscount</td>";
echo "<td>$consumptionMin</td>";
echo "<td>$consumptionMax</td>";
echo "<td>$showStockBar</td>";
echo "<td>$showOrigPriceBar</td>";
echo "<td>$barTouchscreen</td>";
echo "<td>$iPadReaders</td>";
echo "<td>$cashdro</td>";
echo "<td>$creditchange</td>";
echo "<td>$expirychange</td>";
echo "<td>$exentoset</td>";
echo "<td>$menusortdisp</td>";
echo "<td>$menusortbar</td>";
echo "<td>$dispsig</td>";
echo "<td>$barsig</td>";
echo "<td>$openmenu</td>";
echo "<td>$keypads</td>";
echo "<td>$moneycount</td>";
echo "<td>$customws</td>";
echo "<td>$negcredit</td>";
echo "<td>$language</td>";
echo "<td>$nobar</td>";
echo "<td>$sigtablet</td>";
echo "<td>$entrysys</td>";
echo "<td>$entrysysstay</td>";
echo "<td>$entrysyssecs</td>";
echo "<td>$dooropener</td>";
echo "<td>$cuotaincrement</td>";
echo "<td>$checkoutDiscountBar</td>";
echo "<td>$chipcost</td>";
echo "<td>$fingerprint</td>";
echo "<td>$pagination</td>";
echo "<td>$dooropenfor</td>";
echo "<td>$workertracking</td>";
echo "<td>$fullmenu</td>";
echo "<td>$presignup</td>";
echo "<td>$signupcode</td>";
echo "<td>$allowvisitors</td>";
echo "<td>$flowerLimitPercentage</td>";
echo "<td>$extractLimitPercentage</td>";
echo "<td>$fastVisitor</td>";
echo "<td>$saldoGift</td>";
echo "</tr>";

		
/*		$selectUsersU = "SELECT userid, COUNT(saleid) FROM sales WHERE saletime BETWEEN '2019-11-01' AND '2019-11-30' GROUP BY userid";
		try
		{
			$results = $pdo4->prepare("$selectUsersU");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
			
			$userid = $row['userid'];
			$sales = $row['COUNT(saleid)'];
			
			if ($sales > 30) {
			
				echo "<tr><td style='padding: 5px;'>$database</td><td style='padding: 5px; text-align: right;'>$userid</td><td style='padding: 5px; text-align: right;'>$sales</td></tr>";
				
			}
			
		}

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

		$selectUsersU = "SELECT movementid, purchaseid, category FROM productmovements";
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