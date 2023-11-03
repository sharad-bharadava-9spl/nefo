<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
		
	// Query to look up customer
	$userDetails = "SELECT c.id, c.registeredSince, c.Brand, c.number, c.longName, c.shortName, c.cif, c.street, c.streetnumber, c.flat, c.postcode, c.city, c.state, c.country, c.website, c.email, c.facebook, c.twitter, c.instagram, c.googleplus, c.status, c.type, c.lawyer, c.URL, c.source, c.billingType, c.phone, s.statusName, c.membermodule, c.contact, c.language, c.clubtype, c.size, c.opened FROM customers c, customerstatus s WHERE c.status = s.id AND c.id = $user_id";
	
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$id = $row['id'];
		$registeredSince = $row['registeredSince'];
		$Brand = $row['Brand'];
		$number = $row['number'];
		$_SESSION['customer'] = $number;
		$longName = $row['longName'];
		$shortName = $row['shortName'];
		$cif = $row['cif'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$state = $row['state'];
		$country = $row['country'];
		$website = $row['website'];
		$email = $row['email'];
		$facebook = $row['facebook'];
		$twitter = $row['twitter'];
		$instagram = $row['instagram'];
		$googleplus = $row['googleplus'];
		$status = $row['status'];
		$type = $row['type'];
		$lawyer = $row['lawyer'];
		$URL = $row['URL'];
		$source = $row['source'];
		$billingType = $row['billingType'];
		$dbname = $row['dbname'];
		$dbuser = $row['dbuser'];
		$dbpwd = $row['dbpwd'];
		$signedContract = $row['signedContract'];
		$statusName = $row['statusName'];
		$telephone = $row['phone'];
		$membermodule = $row['membermodule'];
		$contact = $row['contact'];
		$language = $row['language'];
		$clubtype = $row['clubtype'];
		$size = $row['size'];
		$opened = date("d-m-Y", strtotime($row['opened']));
		
		// clubtype, size, opened
		
	if ($source == '0' || $source == '') {
		
	} else {
		
		// Check if 'source' contains a hyphen, and if so, explode the string and show text input depending on whether it's a club, lawyer or accountant recommendation. What about 'OTHER'?
		
		if (strpos($source, 'Recommendation') !== false) {
			
			$recommended = 'true'; // To use further down to show text input
			
			$array = explode(" - ",$source);
			
			$recommendedID = $array[1];
			
			// Look up club name
			$query = "SELECT number, shortName FROM customers WHERE id = $recommendedID";
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
		
			$row = $result->fetch();
				$sourceNumber = $row['number'];
				$sourceshortName = $row['shortName'];
			
			$source = "Recommendation: $sourceNumber - $sourceshortName";
			
		} else if (strpos($source, 'Accountant') !== false) {
			
			$accountant = 'true'; // To use further down to show text input
			$array = explode(" - ",$source);
			
			$accountantNo = $array[1];
			
	      	// Query to look up accountant
			$selectGroups = "SELECT id, name FROM accountants WHERE id = '$accountantNo'";
			try
			{
				$result = $pdo3->prepare("$selectGroups");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$accountantName = $row['name'];
				
			$source = "Accountant: $accountantName";
			
		} else if (strpos($source, 'Lawyer') !== false) {
			
			$lawyer = 'true'; // To use further down to show text input
			$array = explode(" - ",$source);
			
			$lawyerNo = $array[1];
			
	      	// Query to look up lawyer
			$selectGroups = "SELECT id, name FROM lawyers WHERE id = '$lawyerNo'";
			try
			{
				$result = $pdo3->prepare("$selectGroups");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$lawyerName = $row['name'];

			$source = "Lawyer: $lawyerName";

		} else if (strpos($source, 'Other') !== false) {
			
			
			$other = 'true';
			
			$array = explode(" - ",$source);
			
			$otherValue = $array[1];
			
			$source = "Other: $source";
			
		}
		
	}		
		
		
		// Look up domain
		$findDomain = "SELECT domain, db_pwd, warning, cutoff FROM db_access WHERE customer = '$number'";
		try
		{
			$result = $pdo->prepare("$findDomain");
			$result->execute();
			$data2 = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		if ($data2) {

			$row = $data2[0];
			$domain = $row['domain'];
			$_SESSION['customerdomain'] = $domain;
			$db_pwd = $row['db_pwd'];
			$warning = $row['warning'];
			$cutoff = date("d-m-Y", strtotime($row['cutoff']));
			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";
			
			/* DEBUG
			echo "domain: $domain<br />";
			echo "db_pwd: $db_pwd<br />";
			echo "db_name: $db_name<br />";
			echo "db_user: $db_user<br />";
			echo "domain: $domain<br />";
			*/
			
			// Look for db name. If it doesn't exist, throw error.

			try	{
		 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
		 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 		$pdo2->exec('SET NAMES "utf8"');
			}
			catch (PDOException $e)	{
		  		// $output = 'Unable to connect to the database server: ' . $e->getMessage();
		
		 		// echo $output;
		 		$_SESSION['errorMessage'] = "Customer does not have a database (meaning they haven't been launched - or they have been sunset!)";
		 		$nodb = 'true';
			}
			
		} else {
			$domain = 'NONE';
		}
		
		try
		{
			$result = $pdo3->prepare("SELECT noteid, notetime, userid, note, worker FROM usernotes WHERE userid = '$user_id' ORDER by notetime DESC");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			



		
	// This week
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW());";
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
		$amountWeek = $row['SUM(amount)'];
		$quantityWeek = $row['SUM(quantity)'];
		$unitsWeek = $row['SUM(unitsTot)'];

	// Last week
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -1 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 WEEK));";
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
		$amountWeekMinusOne = $row['SUM(amount)'];
		$quantityWeekMinusOne = $row['SUM(quantity)'];
		$unitsWeekMinusOne = $row['SUM(unitsTot)'];
		
	// Two weeks ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -2 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 WEEK));";
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
		$amountWeekMinusTwo = $row['SUM(amount)'];
		$quantityWeekMinusTwo = $row['SUM(quantity)'];
		$unitsWeekMinusTwo = $row['SUM(unitsTot)'];

		
	// Consumption this calendar month
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
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
		$amountMonth = $row['SUM(amount)'];
		$quantityMonth = $row['SUM(quantity)'];
		$unitsMonth = $row['SUM(unitsTot)'];
		
		if ($quantityMonth > $mconsumption) {
			$monthClass = 'negative2';
		}
					
	// Past calendar month -also average per weke, average per month etc.
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -1 MONTH))";
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
		$amountMonthMinus1 = $row['SUM(amount)'];
		$quantityMonthMinus1 = $row['SUM(quantity)'];
		$unitsMonthMinus1 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus1 > $mconsumption) {
			$monthMinusOneClass = 'negative2';
		}
		
		
	// Two months ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -2 MONTH))";
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
		$amountMonthMinus2 = $row['SUM(amount)'];
		$quantityMonthMinus2 = $row['SUM(quantity)'];
		$unitsMonthMinus2 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus2 > $mconsumption) {
			$monthMinusTwoClass = 'negative2';
		}
		
	// Three months ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -3 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -3 MONTH))";
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
		$amountMonthMinus3 = $row['SUM(amount)'];
		$quantityMonthMinus3 = $row['SUM(quantity)'];
		$unitsMonthMinus3 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus3 > $mconsumption) {
			$monthMinusThreeClass = 'negative2';
		}
	
		
		
		
		
		
	// Four months ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -4 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -4 MONTH))";
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
		$amountMonthMinus4 = $row['SUM(amount)'];
		$quantityMonthMinus4 = $row['SUM(quantity)'];
		$unitsMonthMinus4 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus4 > $mconsumption) {
			$monthMinusFourClass = 'negative2';
		}
	// Five months ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -5 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -5 MONTH))";
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
		$amountMonthMinus5 = $row['SUM(amount)'];
		$quantityMonthMinus5 = $row['SUM(quantity)'];
		$unitsMonthMinus5 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus5 > $mconsumption) {
			$monthMinusFiveClass = 'negative2';
		}
	// Six months ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -6 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -6 MONTH))";
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
		$amountMonthMinus6 = $row['SUM(amount)'];
		$quantityMonthMinus6 = $row['SUM(quantity)'];
		$unitsMonthMinus6 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus6 > $mconsumption) {
			$monthMinusSixClass = 'negative2';
		}
	// Seven months ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -7 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -7 MONTH))";
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
		$amountMonthMinus7 = $row['SUM(amount)'];
		$quantityMonthMinus7 = $row['SUM(quantity)'];
		$unitsMonthMinus7 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus7 > $mconsumption) {
			$monthMinusSevenClass = 'negative2';
		}
	// Eight months ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -8 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -8 MONTH))";
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
		$amountMonthMinus8 = $row['SUM(amount)'];
		$quantityMonthMinus8 = $row['SUM(quantity)'];
		$unitsMonthMinus8 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus8 > $mconsumption) {
			$monthMinusEightClass = 'negative2';
		}
	// Nine months ago
	$selectSales = "SELECT SUM(unitsTot), SUM(amount) FROM b_sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -9 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -9 MONTH))";
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
		$amountMonthMinus9 = $row['SUM(amount)'];
		$quantityMonthMinus9 = $row['SUM(quantity)'];
		$unitsMonthMinus9 = $row['SUM(unitsTot)'];
		
		if ($quantityMonthMinus9 > $mconsumption) {
			$monthMinusNineClass = 'negative2';
		}
	
		
	// Query to look up individual sales
	$selectSales = "SELECT saleid, saletime, userid, amount, '' AS amountpaid, '' AS quantity, unitsTot AS units, adminComment, creditBefore, creditAfter, 'bar' as Type, '' AS donatedTo FROM b_sales WHERE userid = $user_id ORDER by saletime DESC";
		try
		{
			$resultsS = $pdo3->prepare("$selectSales");
			$resultsS->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
						

	$deleteNoteScript = <<<EOD
function delete_note(noteid, userid) {
	if (confirm("{$lang['confirm-deletenote']}")) {
				window.location = "uTil/delete-note.php?noteid=" + noteid + "&userid=" + userid;
				}
}
EOD;
	pageStart("Client profile", NULL, $deleteNoteScript, "pprofilenew", NULL, "Client profile", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<center>
 <span class="firstbuttons">
 
<?php
	echo "	
  <a href='bar-new-sale-2.php?user_id=$user_id' class='cta'>New HW Sale</a>";

?>

 </span>
  <br />
 <span class="secondbuttons">
  <a href="clients.php" class="cta">&laquo; Customers</a>
  <a href="warnings.php" class="cta">&laquo; Warnings</a>
  <a href="edit-customer.php?user_id=<?php echo $user_id;?>&warning=<?php echo $warning;?>&cutoff=<?php echo $cutoff;?>&domain=<?php echo urlencode($domain);?>" class="cta"><?php echo $lang['global-edit']; ?></a>
  <a href="customer-notes.php?userid=<?php echo $user_id;?>" class="cta"><?php echo $lang['add-note']; ?></a>
  <a href="contacts.php?userid=<?php echo $user_id;?>" class="cta">Contacts</a>
 </span>
</center>

<div class="overview">
 <span class="profilepicholder"><img class="profilepic" src="../images/_<?php echo $domain; ?>/logo.png" /></span>
 <span class="profilefirst"><?php echo $number . " - " . $shortName ?> <a href="customer-contract.php?domain=<?php echo $domain; ?>"><img src="images/contract.png" style='margin-bottom: -3px; margin-left: 5px;'/></a></span>
 <br />
 <span class="profilesecond">
<?php
	if ($language != '') {
		echo "($language)<br />";
	}

	// Warning
	if ($warning == 1) {
		$warningOutput = " - Soft warning: Cutoff $cutoff";
	} else if ($warning == 2) {
		$warningOutput = " - Final warning: Cutoff $cutoff";
	} else if ($warning == 3) {
		$warningOutput = " - Access cut $cutoff";
	}
	
	if ($membermodule == 1) {
		$membermodule = '(member module)';
	} else {
		$membermodule = '';
	}

	echo "$longName<br /><span class='yellow'>$statusName $membermodule</span>$warningOutput";
	
	
	if ($data2) {
	$selectRealActives = "SELECT creditOrDirect FROM systemsettings";
	
		if ($nodb != 'true') {
		try
		{
			$result = $pdo2->prepare("$selectRealActives");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$creditOrDirect = $row['creditOrDirect'];
			

			
		if ($creditOrDirect == 0) {
			$dispensemode = 'Mode: Direct dispensing';
		} else {
			$dispensemode = 'Mode: Credit (donations)';
		}
		echo "<br />$dispensemode";
		
	}
		
	}
		

?>
		
 </span>
 <br />
<div id="memberNotifications"> <span class="profilethird">
<?php 

		
		if ($size == 1) {
			$newInfo = "Small (<100), ";
		} else if ($size == 2) {
			$newInfo = "Medium (100-250), ";
		} else if ($size == 3) {
			$newInfo = "Large (250-500), ";
		} else if ($size == 4) {
			$newInfo = "Full size (>500), ";
		}
		
		if ($clubtype == 1) {
			$newInfo .= "Only medicinal, ";
		} else if ($clubtype == 2) {
			$newInfo .= "Mainly medicinal, ";
		} else if ($clubtype == 3) {
			$newInfo .= "Mixed, ";
		} else if ($clubtype == 4) {
			$newInfo .= "Mainly recreational, ";
		} else if ($clubtype == 5) {
			$newInfo .= "Only recreational, ";
		}
		
			$newInfo .= "Opened since: $opened";
			
			echo "<strong>Segments:</strong> $newInfo";
			
	if ($data) {
		$i = 1;
		
			echo <<<EOD
<span id='adminComment' onClick="javascript:toggleDiv('userNotes');">
 <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' />
 <span class='yellow' id='showComment' style='cursor: pointer;'>{$lang['global-admincomment']}</span>
</span>
EOD;

	if ($_GET['deleted'] == 'yes' || isset($_GET['openComment'])) {
		echo "<div id='userNotes'>";
	} else {
		echo "<div id='userNotes' style='display: none;'>";
	}
	
		echo <<<EOD
	 <table class="profileNew">
  	  <tr>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['pur-date']}</strong></th>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['responsible']}</strong></th>
  	   <th class="smallerfont" colspan='2'><strong>{$lang['global-comment']}</strong></th>
	  </tr>
EOD;
		foreach ($data as $userNote) {
	
			if ($userNote['notetime'] == NULL) {
				$formattedDate = '';
			} else {
				$formattedDate = date("d-m-y H:i", strtotime($userNote['notetime'] . "+$offsetSec seconds"));
			}
			$noteid = $userNote['noteid'];
			$note = $userNote['note'];
			$responsible = $userNote['worker'];
			$worker = getUser($responsible);
			
		if($i == $noteCount) {
	   		echo <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$formattedDate</td>
  <td style='border-bottom: 0;'>$worker</td>
  <td style='border-bottom: 0;'>$note</td>
  <td style='border-bottom: 0;'><a href="javascript:delete_note($noteid,$user_id)"><img src='images/delete.png' width='15' /></a></td>
 </tr>
EOD;
		} else {
			echo <<<EOD
 <tr>
  <td>$formattedDate</td>
  <td>$worker</td>
  <td>$note</td>
  <td><a href="javascript:delete_note($noteid,$user_id)"><img src='images/delete.png' width='15' /></a></td>
 </tr>
EOD;
	}
			echo <<<EOD
EOD;
	$i++;
		}
echo "</table></div>";
	}	// Stats
if ($data2 && $nodb != 'true') {

	$selectMembers = "SELECT COUNT(memberno) from users WHERE userGroup <> 8";
	try
	{
		$result = $pdo2->prepare("$selectMembers");
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
		$result = $pdo2->prepare("$selectMembers");
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
			$result = $pdo2->prepare("$selectRealActives");
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
		
		// Real active member this month
		$month_ini = new DateTime("first day of this month");
		$month_end = new DateTime("last day of this month");
		
		$monthBegin = $month_ini->format('Y-m-d'); // 2012-02-01
		$monthEnd = $month_end->format('Y-m-d'); // 2012-02-29
		
		$selectRealActives = "SELECT COUNT( DISTINCT userid ) FROM sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";
		try
		{
			$result = $pdo2->prepare("$selectRealActives");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$realActiveMembersNow = $row['COUNT( DISTINCT userid )'];
			
	// Revenue: Member payments + donations + direct dispensing
		// Look up dispensed today cash
		$selectSales = "SELECT SUM(amount) from sales WHERE saletime BETWEEN '$monthBeginLast' AND '$monthEndLast' AND direct < 3";
		try
		{
			$result = $pdo2->prepare("$selectSales");
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
			$result = $pdo2->prepare("$selectSales");
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
		$result = $pdo2->prepare("$selectDonations");
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
		$result = $pdo2->prepare("$selectMembershipFees");
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
		
		$revenue = number_format($donations + $membershipFees + $salesTodayCash + $salesTodayBarCash,2);
		
		// Look up dispensed today cash
		$selectSales = "SELECT SUM(amount) from sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd' AND direct < 3";
		try
		{
			$result = $pdo2->prepare("$selectSales");
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
		$selectSales = "SELECT SUM(amount) from b_sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd' AND direct < 3";
		try
		{
			$result = $pdo2->prepare("$selectSales");
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
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo <> 3 AND donationTime BETWEEN '$monthBegin' AND '$monthEnd'";
	try
	{
		$result = $pdo2->prepare("$selectDonations");
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
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$monthBegin' AND '$monthEnd'";
	try
	{
		$result = $pdo2->prepare("$selectMembershipFees");
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
		
		$revenueNow = number_format($donations + $membershipFees + $salesTodayCash + $salesTodayBarCash,2);
		
	}		
				
	$currentMonth = date('F');
	$lastMonth = Date('F', strtotime($currentMonth . " last month"));

	echo <<<EOD
<table style='margin-left: 0; color: white;'>
 <tr>
  <td>Members</td>
  <td class='right'><strong>$currentMembers</strong></td>
 </tr>
 <tr>
  <td>Active members&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td class='right'><strong>$activeMembers</strong></td>
 </tr>
 <tr>
  <td>Dispensed $lastMonth</td>
  <td class='right'><strong>$realActiveMembers</strong></td>
 </tr>
 <tr>
  <td>Dispensed $currentMonth</td>
  <td class='right'><strong>$realActiveMembersNow</strong></td>
 </tr>
 <tr>
  <td>Revenue $lastMonth</td>
  <td class='right'><strong>$revenue &euro;</strong></td>
 </tr>
 <tr>
  <td>Revenue $currentMonth</td>
  <td  class='right'><strong>$revenueNow &euro;</strong></td>
 </tr>
 <tr>
  <td colspan='2'>&nbsp;</td>
 </tr>
 <tr>
  <td>How did they find us?</td>
  <td><strong>$source</strong></td>
 </tr>
 <tr>
  <td>How did they contact us?&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td><strong>$contact</strong></td>
 </tr>
</table>
EOD;

	
	

	echo "</span></div><span class='profilefourth'>";
	
	// Contract
	// Users
	// Lawyer
	if ($lawyer != 0) {
		
		$query = "SELECT name, telephone, email, street, streetnumber, flat, postcode, city, country FROM lawyers WHERE id = $lawyer";
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
	
		$row = $result->fetch();
			$lname = $row['name'];
			$ltelephone = $row['telephone'];
			$lemail = $row['email'];
			$lstreet = $row['street'];
			$lstreetnumber = $row['streetnumber'];
			$lflat = $row['flat'];
			$lpostcode = $row['postcode'];
			$lcity = $row['city'];
			$lcountry = $row['country'];
			
	}
	
		$query = "SELECT name, role, telephone, email, active FROM contacts WHERE customer = '$number' ORDER BY active DESC, name ASC";
		try
		{
			$results = $pdo3->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
			$cname = $row['name'];
			$crole = $row['role'];
			$ctelephone = $row['telephone'];
			$cemail = $row['email'];
			$active = $row['active'];
			
			if ($active == 0) {
				$style = "color: red;";
			} else {
				$style = '';
			}
			
			$contactlist .= <<<EOD
<span style='$style'>
<strong>$cname</strong><br />
<em>$crole</em><br />
<a href="mailto:<?php echo $cemail; ?>">$cemail</a><br />
$ctelephone<br /><br />
</span>
EOD;
			
		}
	


?>
<br />
</span>
 </div> <!-- END OVERVIEW -->
 
  <div class="clearfloat"></div><br />
  
  
<div id="profileWrapper">

<div id="leftprofile">
 <div id="detailedinfo">
  <div id="leftpane">
<h4>Club details</h4><br />
CIF: <?php echo $cif; ?><br /><br />
<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br />
<?php echo $telephone; ?><br /><br />
<?php echo $street . " " . $streetnumber . " " . $flat; ?><br />
      <?php echo $postcode; ?> <?php echo $city; ?><br />
      <?php echo $country; ?><br /><br /><br />
      
<h4>Lawyer</h4><br />
<strong><?php echo $lname; ?></strong><br />
<a href="mailto:<?php echo $lemail; ?>"><?php echo $lemail; ?></a><br />
<?php echo $ltelephone; ?><br /><br />
<?php echo $lstreet . " " . $lstreetnumber . " " . $lflat; ?><br />
      <?php echo $lpostcode; ?> <?php echo $lcity; ?><br />
      <?php echo $lcountry; ?><br /><br />

  </div> <!-- END LEFTPANE -->
  <div id="rightpane">
<h4>Contacts</h4><br />
<?php echo $contactlist; ?>
<!--<h4>System specifics</h4>
User ID: <?php echo $user_id; ?><br />
Signup source: <?php echo $signupsource; ?><br />
Card ID: <?php echo $cardid; ?><br />-->
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  <div id="userPreferences">
  <div id="leftpane">
<h4><?php echo $lang['member-weeklyavgs']; ?></h4>
<?php echo $lang['global-dispenses']; ?>: <?php echo number_format($totalDispensesPerWeek,0); ?><br />
<?php echo $lang['member-spenditure']; ?>: <?php echo number_format($totalAmountPerWeek,0); ?> &euro;<br /><br />

  </div> <!-- END LEFTPANE -->
  <div id="rightpane">


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  </div> <!-- END LEFTPROFILE -->
 
 
 <div id="statistics">
  <h4>Sales History</h4>
  <table class="default memberStats">
   <tr>
    <td class="first"><?php echo $lang['dispensary-thisweek']; ?>:</td>
    <td><?php echo number_format($unitsWeek,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeek,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-lastweek']; ?>:</td>
    <td><?php echo number_format($unitsWeekMinusOne,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusOne,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-twoweeksago']; ?>:</td>
    <td><?php echo number_format($unitsWeekMinusTwo,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusTwo,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date('F'); ?>:</td>
    <td><?php echo number_format($unitsMonth,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonth,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("first day of last month")); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus1,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-1 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus2,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-2 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus3,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   

   <tr>
    <td class="first"><?php echo date("F", strtotime("-3 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus4,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-4 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus5,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-5 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus6,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-6 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus7,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-7 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus8,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus8,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-8 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus9,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus9,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
  </table>

 </div>
 </div> <!-- END PROFILEWRAPPER -->
 <br /><br />
 
 	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th>&euro;</th>
	    <th>Tot. g</th>
	    <th>Tot. u</th>
	    <th>Tot. &euro;</th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
		while ($sale = $resultsS->fetch()) {
	
		$formattedDate = date("d M Y H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$type = $sale['Type'];
		$donatedTo = $sale['donatedTo'];
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
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
		$memberno = $row['memberno'];
		
		
		
			// Make unpaid rows red and donation rows green:
			// Make unpaid rows red:
			if ($type == 'donation') {
				echo "<tr class='green'>";
			} else {
				echo "<tr>";
			}

			
// Separate methodologies and row displays (linkage) for donations vs sales. Change Credit first:
			if ($type == 'donation' && $donatedTo == 3) {
		echo "
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['changed-credit']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} &euro;</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} &euro;</td>

		";
				
// Separate methodologies and row displays (linkage) for donations vs sales. Donations next:
			} else if ($type == 'donation') {
		echo "
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['donation-donation']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} &euro;</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} &euro;</td>

		";
				
			} else if ($type == 'memberpayment') {
		echo "
  	   <td class='clickableRow'>{$formattedDate}</td>
  	   <td class='clickableRow' colspan='6'>{$lang['membership-payments']}</td>
		<td class='clickableRow right'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right'></td>
		<td class='clickableRow right'></td>

		";
				
// Separate methodologies and row displays (linkage) for donations vs sales. Sales next:
			}else if ($type == 'sale') {
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$totResult = $onesaleResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

	   
		echo "
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		foreach ($totResult as $onesale) {	
			if ($onesale['category'] == 1) {
				$category = 'Flower';
			} else if ($onesale['category'] == 2) {
				$category = 'Extract';
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name FROM categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$category = $row['name'];
			}
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		foreach ($totResult as $onesale) {	
			
			$productid = $onesale['productid'];
			
	// Determine product type, and assign query variables accordingly
	if ($onesale['category'] == 1) {
		$purchaseCategory = 'Flower';
		$queryVar = ', breed2';
		$prodSelect = 'flower';
		$prodJoin = 'flowerid';
	} else if ($onesale['category'] == 2) {
		$purchaseCategory = 'Extract';
		$queryVar = '';
		$prodSelect = 'extract';
		$prodJoin = 'extractid';
	} else if ($onesale['category'] > 2) {
		$purchaseCategory = $category;
		$queryVar = '';
		$prodSelect = 'products';
		$prodJoin = "productid";
	}
	
		$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else {
			$name = $row['name'];
		}


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		foreach ($totResult as $onesale) {	
			
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],1) . " u<br />";
			}
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		echo "
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} &euro;</td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} &euro;</td>

		";
		
		
		// And finally, bar
	} else {
		
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$totResult = $onesaleResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	   
		echo "
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			
			// Look up bar category
			$selectBarCat = "SELECT name FROM b_categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$selectBarCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$barRow = $result->fetch();
		   		$category = $barRow['name'];
			
			
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			
			$productid = $onesale['productid'];
			
		$selectProduct = "SELECT name FROM b_products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$name = $row['name'];


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['quantity'],0) . "<br />";
		}
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		echo "
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$credit} &euro;</td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$newcredit} &euro;</td>

		";
		
	}

}
?>

<script>

	$("#adminComment").hover(function () {
	$("#commentText").css("display", "block");
	},function () {
	$("#commentText").css("display", "none");
	});	
	
	$("#minimizeMemberBox").click(function () {
	$("#hiddenSummary").css("display", "block");
	$("#memberbox").css("display", "none");
	});	
	
	$("#minimizeSummaryBox").click(function () {
	$("#memberbox").css("display", "block");
	$("#hiddenSummary").css("display", "none");
	});	
	
function toggleDiv(divId) {
   $("#"+divId).toggle();
}	
</script>
<?php displayFooter(); ?>
