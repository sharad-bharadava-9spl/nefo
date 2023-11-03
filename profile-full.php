<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$domain = $_SESSION['domain'];

	// Get the user ID
	if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
		
	try
	{
		$result = $pdo3->prepare("SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.friend2, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(), u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.dniext1, u.dniext2, u.workStation, u.bajaDate, u.starCat, u.interview, u.exento, u.fptemplate1, u.usergroup2, u.sigext FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$user_id = $row['user_id'];
		$memberno = $row['memberno'];
		$registeredSince = $row['registeredSince'];
		$membertime = date("M y", strtotime($registeredSince));
		$userGroup = $row['userGroup'];
		
		$langoperator = $_SESSION['lang'];
		$query = "SELECT $langoperator FROM usergroups WHERE userGroup = $userGroup";
		try
		{
			$resultU = $pdo->prepare("$query");
			$resultU->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowU = $resultU->fetch();
			$groupName = $rowU[$langoperator];
			
		$groupDesc = $row['groupDesc'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$email = $row['email'];
		$day = $row['day'];
		$month = $row['month'];
		$year = $row['year'];
		$nationality = $row['nationality'];
		$gender = $row['gender'];
		$dni = $row['dni'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$country = $row['country'];
		$telephone = $row['telephone'];
		$mconsumption = $row['mconsumption'];
		$usageType = $row['usageType'];
		$signupsource = $row['signupsource'];
		$cardid = $row['cardid'];
		$photoid = $row['photoid'];
		$docid = $row['docid'];
		$doorAccess = $row['doorAccess'];
		$friend = $row['friend'];
		$friend2 = $row['friend2'];
		$paidUntil = $row['paidUntil'];
		$adminComment = $row['adminComment'];
		$daysMember = $row['daysMember'];
		$form1 = $row['form1'];
		$form2 = $row['form2'];
		$dniscan = $row['dniscan'];
		$paymentWarning = $row['paymentWarning'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
		$userCredit = $row['credit'];
		$banComment = $row['banComment'];
		$creditEligible = $row['creditEligible'];
		$discount = $row['discount'];
		$discountBar = $row['discountBar'];
		$photoext = $row['photoext'];
		$dniext1 = $row['dniext1'];
		$dniext2 = $row['dniext2'];
		$workStation = $row['workStation'];
		$bajaDate = date('d-m-y', strtotime($row['bajaDate']));
		$starCat = $row['starCat'];	
		$interview = $row['interview'];
		$exento = $row['exento'];
		$fptemplate1 = $row['fptemplate1'];
		$usergroup2 = $row['usergroup2'];
		$sigext = $row['sigext'];
		if ($sigext == '') {
			$sigext = 'png';
		}
		
	if ($starCat == 1) {
   		$userStar = "<img src='images/star-yellow.png'/>";
	} else if ($starCat == 2) {
   		$userStar = "<img src='images/star-black.png' />";
	} else if ($starCat == 3) {
   		$userStar = "<img src='images/star-green.png' />";
	} else if ($starCat == 4) {
   		$userStar = "<img src='images/star-red.png' />";
	} else if ($starCat == 5) {
   		$userStar = "<img src='images/star-purple.png' />";
	} else if ($starCat == 6) {
   		$userStar = "<img src='images/star-blue.png' />";
	} else {
   		$userStar = "";
	}
	
	if ($usergroup2 > 0) {
		
	try
	{
		$result = $pdo3->prepare("SELECT name FROM usergroups2 WHERE id = $usergroup2");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$groupName2 = $row['name'];
		
	}

	try
	{
		$results = $pdo3->prepare("SELECT noteid, notetime, userid, note, worker FROM usernotes WHERE userid = $user_id ORDER by notetime DESC");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($results->rowCount()) {
		$userNotes = $results->fetchAll();
	} else {
		$userNotes = '';
	}
	


// Calculate Age - only if Birthday exists

	if ($day != 0) {
		$bdayraw = $day . "." . $month . "." . $year;
		$bday = new DateTime($bdayraw);
		$today = new DateTime(); // for testing purposes
		$diff = $today->diff($bday);
		$age = $diff->y;
		
		$birthday = date("d M Y", strtotime($bdayraw));
	
	} else {
		
		$birthday = '';
		
	}

// Here you start inserting previous sales

	// Query to look up user debt
	try
	{
		$result = $pdo3->prepare("SELECT SUM(amount), SUM(amountpaid) FROM sales WHERE userid = $user_id");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$amtTot = $row['SUM(amount)'];
		$amtPaid = $row['SUM(amountpaid)'];
		$amtOwed = $amtTot - $amtPaid;
			
	// Query to look up total sales and find weekly average
	try
	{
		$result = $pdo3->prepare("SELECT SUM(amount) FROM sales WHERE userid = $user_id");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user3: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		
	try
	{
		$result = $pdo3->prepare("SELECT COUNT(saleid) FROM sales WHERE userid = $user_id");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user4: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$totalDispenses = $row['COUNT(saleid)'];
		$totalDispensesPerDay = $totalDispenses / $daysMember;
		$totalDispensesPerWeek = $totalDispensesPerDay * 7;

	// Select flower purchases
	try
	{
		$result = $pdo3->prepare("SELECT SUM(d.quantity), SUM(d.amount) FROM salesdetails d, sales s WHERE d.category = 1 AND s.userid = $user_id AND s.saleid = d.saleid");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user5: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
			$totalFlowers = $row['SUM(d.quantity)'];

	// Select extract purchases
	try
	{
		$result = $pdo3->prepare("SELECT SUM(d.quantity), SUM(d.amount) FROM salesdetails d, sales s WHERE d.category = 2 AND s.userid = $user_id AND s.saleid = d.saleid");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user6: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$totalExtracts = $row['SUM(d.quantity)'];
			
	$totalPurchases = $totalExtracts + $totalFlowers;
		
		
	if ($totalFlowers > $totalExtracts) {
		$favouriteCategory = 'Flower';
		$percentage = ($totalFlowers / $totalPurchases) * 100;
	} else if ($totalFlowers < $totalExtracts) {
		$favouriteCategory = 'Extract';
		$percentage = ($totalExtracts / $totalPurchases) * 100;
	} else if ($totalFlowers == $totalExtracts && $totalFlowers != 0) {
		$favouriteCategory = 'Both';
		$percentage = 50;
	} else {
		$favouriteCategory = '';
		$percentage = '';
	}

		
	// Select favourite products
	try
	{
		$resultF = $pdo3->prepare("SELECT d.category, d.productid, SUM(d.quantity) FROM salesdetails d, sales s WHERE (d.category = 1 OR d.category = 2) AND s.userid = $user_id AND s.saleid = d.saleid GROUP by d.category, d.productid ORDER by SUM(d.quantity) DESC");
		$resultF->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user7: ' . $e->getMessage();
			echo $error;
			exit();
	}

	// Get the five favourites
	for ($i = 1; $i < 6; $i++) {
	
		$rowF = $resultF->fetch();
			$category = $rowF['category'];
			$productid = $rowF['productid'];
			$quantity = $rowF['SUM(d.quantity)'];
		
		if ($category == 1) {
			
			try
			{
				$result = $pdo3->prepare("SELECT name, breed2 from flower where flowerid = '$productid'");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user8: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$name = $row['name'] . " " . $row['breed2'];
	
		} else if ($category == 2) {
			
			try
			{
				$result = $pdo3->prepare("SELECT name from extract where extractid = '$productid'");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user9: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$name = $row['name'];
				
		} else {
			
			try
			{
				$result = $pdo3->prepare("SELECT name from products where productid = '$productid'");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user10: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$name = $row['name'];
		}
		
		${'favourite' . $i} = $name;
		${'quantity' . $i} = $quantity;
				
	}
	
	// This week
	try
	{
		$result = $pdo3->prepare("SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW());");
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
		$unitsWeek = $row['SUM(units)'];

	// Last week
	try
	{
		$result = $pdo3->prepare("SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -1 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 WEEK));");
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
		$unitsWeekMinusOne = $row['SUM(units)'];
		
	// Two weeks ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -2 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 WEEK));";
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
		$unitsWeekMinusTwo = $row['SUM(units)'];

		
	// Consumption this calendar month
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
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
		$unitsMonth = $row['SUM(units)'];
		
		if ($quantityMonth > $mconsumption) {
			$monthClass = 'negative2';
		}
					
	// Past calendar month -also average per weke, average per month etc.
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -1 MONTH))";
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
		$unitsMonthMinus1 = $row['SUM(units)'];
		
		if ($quantityMonthMinus1 > $mconsumption) {
			$monthMinusOneClass = 'negative2';
		}
		
		
	// Two months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -2 MONTH))";
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
		$unitsMonthMinus2 = $row['SUM(units)'];
		
		if ($quantityMonthMinus2 > $mconsumption) {
			$monthMinusTwoClass = 'negative2';
		}
		
	// Three months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -3 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -3 MONTH))";
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
		$unitsMonthMinus3 = $row['SUM(units)'];
		
		if ($quantityMonthMinus3 > $mconsumption) {
			$monthMinusThreeClass = 'negative2';
		}
	
		
		
		
		
		
	// Four months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -4 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -4 MONTH))";
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
		$unitsMonthMinus4 = $row['SUM(units)'];
		
		if ($quantityMonthMinus4 > $mconsumption) {
			$monthMinusFourClass = 'negative2';
		}
	// Five months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -5 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -5 MONTH))";
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
		$unitsMonthMinus5 = $row['SUM(units)'];
		
		if ($quantityMonthMinus5 > $mconsumption) {
			$monthMinusFiveClass = 'negative2';
		}
	// Six months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -6 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -6 MONTH))";
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
		$unitsMonthMinus6 = $row['SUM(units)'];
		
		if ($quantityMonthMinus6 > $mconsumption) {
			$monthMinusSixClass = 'negative2';
		}
	// Seven months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -7 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -7 MONTH))";
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
		$unitsMonthMinus7 = $row['SUM(units)'];
		
		if ($quantityMonthMinus7 > $mconsumption) {
			$monthMinusSevenClass = 'negative2';
		}
	// Eight months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -8 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -8 MONTH))";
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
		$unitsMonthMinus8 = $row['SUM(units)'];
		
		if ($quantityMonthMinus8 > $mconsumption) {
			$monthMinusEightClass = 'negative2';
		}
	// Nine months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -9 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -9 MONTH))";
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
		$unitsMonthMinus9 = $row['SUM(units)'];
		
		if ($quantityMonthMinus9 > $mconsumption) {
			$monthMinusNineClass = 'negative2';
		}
	// Ten months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -10 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -10 MONTH))";
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
		$amountMonthMinus10 = $row['SUM(amount)'];
		$quantityMonthMinus10 = $row['SUM(quantity)'];
		$unitsMonthMinus10 = $row['SUM(units)'];
		
		if ($quantityMonthMinus10 > $mconsumption) {
			$monthMinusTenClass = 'negative2';
		}
	// Eleven months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -11 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -11 MONTH))";
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
		$amountMonthMinus11 = $row['SUM(amount)'];
		$quantityMonthMinus11 = $row['SUM(quantity)'];
		$unitsMonthMinus11 = $row['SUM(units)'];
		
		if ($quantityMonthMinus11 > $mconsumption) {
			$monthMinusElevenClass = 'negative2';
		}
	// Twelve months ago
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -12 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -12 MONTH))";
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
		$amountMonthMinus12 = $row['SUM(amount)'];
		$quantityMonthMinus12 = $row['SUM(quantity)'];
		$unitsMonthMinus12 = $row['SUM(units)'];
		
		if ($quantityMonthMinus12 > $mconsumption) {
			$monthMinusTwelveClass = 'negative2';
		}
		
	$deleteNoteScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Historial",
	    filename: "Historial" //do not include extension

	  });

	});
});

		
function delete_note(noteid, userid) {
	if (confirm("{$lang['confirm-deletenote']}")) {
				window.location = "uTil/delete-note.php?noteid=" + noteid + "&userid=" + userid;
				}
}
function delete_fingerprint(user_id) {
	
	if (confirm("Estas seguro? No puedes reversar este acción!")) {
		window.location = "uTil/delete-fingerprint.php?user_id=" + user_id;
	}
}

EOD;

	if (isset($_GET['cardadded'])) {
		
		$_SESSION['successMessage'] = $lang['card-chip-added'];
		
	}
	
	if (isset($_GET['newSig'])) {
		
		$oldfile4 = 'images/_' . $_SESSION['domain'] . '/sigs/' . $_SESSION['tempNo'] . '.png';
		$newfile4 = 'images/_' . $_SESSION['domain'] . '/sigs/' . $user_id . '.png';
		rename($oldfile4, $newfile4);
		$_SESSION['successMessage'] = $lang['signature-saved'];
		
	}


	pageStart($lang['title-memberprofile'], NULL, $deleteNoteScript, "pprofilenew", NULL, $lang['member-profilecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<center>
 <span class="firstbuttons">
 
<?php

if ($_SESSION['puestosOrNot'] == 1 && $_SESSION['userGroup'] > 1) {
	
	if ($_SESSION['workstation'] == 'bar') {
		
		echo "<a href='bar-new-sale-2.php?user_id=$user_id' class='cta'>{$lang['bar']}</a>";
		
	} else if ($_SESSION['workstation'] == 'dispensary') {
		
		echo "<a href='new-dispense-2.php?user_id=$user_id' class='cta'>{$lang['global-dispense']}</a>";
		
	}

} else {
	
	echo "	
  <a href='new-dispense-2.php?user_id=$user_id' class='cta'>{$lang['global-dispense']}</a>
  <a href='bar-new-sale-2.php?user_id=$user_id' class='cta'>{$lang['bar']}</a>";
  
}


	$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $user_id . '.' .  $photoext;
	if (!file_exists($memberPhoto)) {
		$memberPhoto = 'images/silhouette.png';
	}

?>

 </span>
  <br />
 <span class="secondbuttons">
  <a href="edit-profile.php?user_id=<?php echo $user_id;?>" class="cta"><?php echo $lang['global-edit']; ?></a>
  <a href="new-card-0.php?user_id=<?php echo $user_id;?>" class="cta"><?php echo $lang['new-chip-card']; ?></a>
  <?php if ($userGroup < 4) { ?> <a href="new-password.php?user_id=<?php echo $user_id;?>" class="cta"><?php echo $lang['password']; ?></a> <?php } ?>
  <a href="notes.php?userid=<?php echo $user_id;?>" class="cta"><?php echo $lang['add-note']; ?></a>
  <a href="member-visits.php?userid=<?php echo $user_id;?>" class="cta"><?php echo $lang['visits']; ?></a>
  <?php if ($_SESSION['userGroup'] < 2) { ?>
  <a href="edit-discounts.php?user_id=<?php echo $user_id; ?>" class="cta"><?php echo $lang['discounts']; ?></a>
  <?php } ?>
  <a href="aval-details.php?user_id=<?php echo $user_id; ?>&chain=true" class="cta">Ver avalados</a>
<!--  <?php if ($_SESSION['userGroup'] < 2) { ?><br /><a href="member-log.php?user_id=<?php echo $user_id;?>" class="cta" style="margin-top: -25px; background-color: red;"><?php echo $lang['member-log']; ?></a> 
  <?php if ($userGroup < 4) { ?> <a href="worker-log.php?operator=<?php echo $user_id;?>" class="cta" style="margin-top: -25px; background-color: red;"><?php echo $lang['worker-log']; ?></a> <?php } } ?>-->
 </span>
</center>

<div class="overview">
 <span class="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>"><img class="profilepic" src="<?php echo $memberPhoto; ?>" /></a></span>
 <span class="profilefirst"><?php echo $userStar . " " . $memberno . " - " . $first_name . " " . $last_name; ?> (<a href="change-registration-date.php?userid=<?php echo $user_id; ?>" style="color: yellow;"><?php echo $membertime; ?></a>) <a href="member-contract.php?user_id=<?php echo $user_id; ?>" target="_blank"><img src="images/contract.png" style='margin-bottom: -3px; margin-left: 5px;'/></a></span>
 <br />
 <span class="profilesecond">
<?php
	if ($_SESSION['showAge'] == 1 && $_SESSION['showGender'] == 1) {
		echo $gender . ", " . $age . " " . $lang['member-yearsold'];
	} else if ($_SESSION['showAge'] == 1 && $_SESSION['showGender'] == 0) {
		echo $age . " " . $lang['member-yearsold'];
	} else if ($_SESSION['showAge'] == 0 && $_SESSION['showGender'] == 1) {
		echo $gender;
	}
?>
		
 </span>
 <br />
<div id="memberNotifications"> <span class="profilethird">
<?php 

	if ($userCredit < 0) {
		$userCreditDisplay = 0;
		$userClass = 'negative';
	} else {
		$userCreditDisplay = $userCredit;
	}
	
	if ($creditEligible == 1) {
		$creditEligibility = "*";
	} else {
		$creditEligibility = "";
	}

	echo "<a href='donation-management.php?userid=" . $user_id . "'><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($userCreditDisplay,2) . " ".$_SESSION['currencyoperator']."$creditEligibility</span></span></a><br />";
	

	// If member is banned
	if ($userGroup == 7) {
		
		// Banned 
		echo "<span class='banDisplay'><span class='banHeader'>*** {$lang['bannedC']} !! ***</span><br /><strong>{$lang['reason']}:</strong><br />" . $banComment . "</span>";
		
	} else {
	
	if ($usergroup2 == 1) {
		
		echo "<div style='
		display: inline-block;
		background-color: black;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		
		'>";
		
	} else if ($usergroup2 == 2) {
		
		echo "<div style='
		display: inline-block;
		background-color: blue;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		
		'>";
		
	} else if ($usergroup2 == 3) {
		
		echo "<div style='
		display: inline-block;
		background-color: yellow;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		color: #333;
		
		'>";
		
	} else if ($usergroup2 == 5) {
		
		echo "<div style='
		display: inline-block;
		background-color: pink;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		color: #333;
		
		'>";
		
	} else if ($usergroup2 == 6) {
		
		echo "<div style='
		display: inline-block;
		background-color: green;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		
		'>";
		
	}else if ($usergroup2 == 7) {
		
		echo "<div style='
		display: inline-block;
		background-color: turquoise;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		color: #333;
		
		'>";
		
	} else if ($usergroup2 == 8) {
		
		echo "<div style='
		display: inline-block;
		background-color: blue;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		
		'>";
		
	} else if ($usergroup2 == 9) {
		
		echo "<div style='
		display: inline-block;
		background-color: black;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		
		'>";
		
	} else {
		
echo "<div style='
		display: inline-block;
		padding: 5px 8px;
		border-radius: 5px;
		margin: 1px 0 3px 0;
		line-height: 18px;
		min-width: 142px;
		
		'>";
		
	}
		
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			echo "<a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 5px;' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a>";
	  	} else if (strtotime($memberExp) > strtotime($timeNow)) {
		  	echo "<a href='pay-membership.php?user_id=$user_id' class='white'>" . $lang['member-memberuntil'] . ": $memberExpReadable</a>";
		} else {
		  	echo "<a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable</span></a>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px;' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: -15px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . ": $paymentWarningDateReadable</span></a>";
		  	}
		  	
		}
		
	} else if ($userGroup == 9) {
		
		echo $groupName . "&nbsp;($bajaDate)";
		
	} else {
		
		echo $groupName . "&nbsp;";
		
		if ($exento == 1) {
			echo "(" . $lang['exempt'] . ")";
		}
		
		if ($_SESSION['puestosOrNot'] == 1) {
		
			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {
				echo "<img src='images/profile-reception.png' />&nbsp;";
			}
			if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/profile-bar.png' />&nbsp;";
			}
			if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/profile-dispensary.png' />&nbsp;";
			}
		}		
	}
	
	if ($groupName2 != '') {
		echo "<br />$groupName2";
	}
		
	echo "</div>";
	

	if ($usageType == '1') {
		echo "<br /><img src='images/medical-22.png' lass='warningIcon' style='margin-bottom: -3px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['medicinal-user']}</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['global-birthday']}</span>";
	}
	
	if ($_SESSION['fingerprint'] == 1) {
	
		if ($fptemplate1 == '') {
	    	echo "<br /><a href='jmu_create_user.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>{$lang['finger-not-registered']}</span></a>";
		} else {
			echo "<br /><a href='javascript:delete_fingerprint($user_id)' style='color: yellow !important;'><img src='images/delete.png' width='20' style='margin-bottom: -4px; margin-left: 7px; margin-right: 3px;' />{$lang['delete-finger']}</a>";
		}
		
	}
	
	$file = 'images/_' . $_SESSION['domain'] . '/ID/' . $user_id . '-front.' . $dniext1;
	$file2 = 'images/_' . $_SESSION['domain'] . '/ID/' . $user_id . '-back.' . $dniext2;
	$file3 = 'images/_' . $_SESSION['domain'] . '/sigs/' . $user_id . ".$sigext";

	if (!file_exists($file)) {
    	echo "<br /><a href='new-id-scan.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>" . $lang['member-dninotscanned'] . "</span></a>";
	}
	
	if (!file_exists($file3)) {
    	echo "<br /><a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>" . $lang['signature-missing'] . "</span></a>";
	}
	
		$highRollerWeekly = $_SESSION['highRollerWeekly'];
		$consumptionPercentage = $_SESSION['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly) {
		echo "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>High roller</span>";
	}
	
	if ($userNotes != '') {
		$i = 1;
		
			echo <<<EOD
<br />
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
		foreach ($userNotes as $userNote) {
	
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
	}
	

	 if ($amtOwed > 0.1 && $userGroup > 2) {
		 echo "<br /><a href='settle-debt-2.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-hasdebt'] . "</span></a>";
	 }

	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	
	if ($quantityMonth >= $mconsumption) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitexc'] . " (+$consumptionDelta g)</span>";
	} else if ($consumptionDeltaPlus < ($mconsumption * $consumptionPercentage)) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitnear'] . " ($consumptionDeltaPlus g " . $lang['global-remaining'] . ")</span>";
	}
	
	
}



	echo "</span></div><span class='profilefourth'>";
	

?>
<br />
<?php 


?>
<br />
</span>
 </div> <!-- END OVERVIEW -->
 
  <div class="clearfloat"></div><br />
  
  
<div id="profileWrapper">


<div id="leftprofile">

 <div id="detailedinfo">
 <center>
 <h4><?php echo $lang['avalista']; ?>(s)</h4>
 <br />

<?php

		/*
		If 0 aval:
		 - Entrevistado? + Add aval
		 
		If 1 aval:
		 - Show aval + Add aval
		 
		If 2 aval:
		 - Show both avales
		 
		What about 'change aval' button?
		
		Also show 'cadena de avalistas'
		
		*/
		
	if ($friend > 0 && $friend2 > 0) {
		
		// 2 avals
		
		// Aval #1
		$friendDetails1 = "SELECT starCat, memberno, first_name, last_name, photoext FROM users WHERE user_id = $friend";
		try
		{
			$result = $pdo3->prepare("$friendDetails1");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$starCat1 = $row['starCat'];
			$memberno1 = $row['memberno'];
			$first_name1 = $row['first_name'];
			$last_name1 = $row['last_name'];
			$photoext1 = $row['photoext'];
		
		if ($starCat1 == 1) {
	   		$userStar1 = "<img src='images/star-yellow.png' width='15' />";
		} else if ($starCat1 == 2) {
	   		$userStar1 = "<img src='images/star-black.png' width='15' />";
		} else if ($starCat1 == 3) {
	   		$userStar1 = "<img src='images/star-green.png' width='15' />";
		} else if ($starCat1 == 4) {
	   		$userStar1 = "<img src='images/star-red.png' width='15' />";
		} else {
	   		$userStar1 = "";
		}
		
		$fileF1 = "images/_$domain/members/" . $friend . '.' . $photoext1;
					
 		echo "<div style='width: 50%; float: left;'><a href='aval-details.php?user_id=$friend&chain=true' class='white'>";
		
		if (file_exists($fileF1)) {
			echo "<img src='$fileF1' height='75' /><br />";
		} else {
			echo "<img src='images/silhouette.png' height='75' /><br />";
		}

		echo "$userStar1 $memberno1 - $first_name1 $last_name1</a><br /><a href='add-aval.php?aval=1&user_id=$user_id'>[{$lang['change']}]</a>";
	
		

	
		echo "</div>";
		
		$friendDetails2 = "SELECT starCat, memberno, first_name, last_name, photoext FROM users WHERE user_id = $friend2";
			
		try
		{
			$result = $pdo3->prepare("$friendDetails2");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$starCat2 = $row['starCat'];
			$memberno2 = $row['memberno'];
			$first_name2 = $row['first_name'];
			$last_name2 = $row['last_name'];
			$photoext2 = $row['photoext'];
		
		if ($starCat2 == 1) {
	   		$userStar2 = "<img src='images/star-yellow.png' width='15' />";
		} else if ($starCat2 == 2) {
	   		$userStar2 = "<img src='images/star-black.png' width='15' />";
		} else if ($starCat2 == 3) {
	   		$userStar2 = "<img src='images/star-green.png' width='15' />";
		} else if ($starCat2 == 4) {
	   		$userStar2 = "<img src='images/star-red.png' width='15' />";
		} else {
	   		$userStar2 = "";
		}
		
		$fileF2 = "images/_$domain/members/" . $friend2 . '.' . $photoext2;
					
 		echo "<div style='width: 50%; float: left;'><a href='aval-details.php?user_id=$friend2&chain=true' class='white'>";
		
		if (file_exists($fileF2)) {
			echo "<img src='$fileF2' height='75' /><br />";
		} else {
			echo "<img src='images/silhouette.png' height='75' /><br />";
		}

		echo "$userStar2 $memberno2 - $first_name2 $last_name2</a><br /><a href='add-aval.php?aval=1&user_id=$user_id&twoavals'>[{$lang['change']}]</a>";
	
		

	
		echo "</div>";
		
		echo "</center>";
		
	} else if ($friend > 0) {
		
		// 1 aval
		
		// Aval #1
		$friendDetails1 = "SELECT starCat, memberno, first_name, last_name, photoext FROM users WHERE user_id = $friend";
			
		try
		{
			$result = $pdo3->prepare("$friendDetails1");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$starCat1 = $row['starCat'];
			$memberno1 = $row['memberno'];
			$first_name1 = $row['first_name'];
			$last_name1 = $row['last_name'];
			$photoext1 = $row['photoext'];
		
		if ($starCat1 == 1) {
	   		$userStar1 = "<img src='images/star-yellow.png' width='15' />";
		} else if ($starCat1 == 2) {
	   		$userStar1 = "<img src='images/star-black.png' width='15' />";
		} else if ($starCat1 == 3) {
	   		$userStar1 = "<img src='images/star-green.png' width='15' />";
		} else if ($starCat1 == 4) {
	   		$userStar1 = "<img src='images/star-red.png' width='15' />";
		} else {
	   		$userStar1 = "";
		}
		
		$fileF1 = "images/_$domain/members/" . $friend . '.' . $photoext1;
					
 		echo "<div style='width: 50%; float: left;'><a href='aval-details.php?user_id=$friend&chain=true' class='white'>";
		
		if (file_exists($fileF1)) {
			echo "<img src='$fileF1' height='75' /><br />";
		} else {
			echo "<img src='images/silhouette.png' height='75' /><br />";
		}

		echo "$userStar1 $memberno1 - $first_name1 $last_name1</a><br /><a href='add-aval.php?aval=1&user_id=$user_id'>[{$lang['change']}]</a>";
	
		

	
		echo "</div>";
		
 		echo "<div style='width: 50%; float: left;'>";
			echo "<img src='images/silhouette.png' height='75' /><br />";

		echo "{$lang['no-avalista']} #2<br /><a href='add-aval.php?aval=2&user_id=$user_id&twoavals'>[{$lang['global-add']}]</a>";
	
		echo "</div>";
		
		
	} else {
		
		// 0 aval
 		echo "<div style='width: 50%; float: left;'>";
			echo "<img src='images/silhouette.png' height='75' /><br />";

		echo "{$lang['no-avalista']} #1<br /><a href='add-aval.php?aval=1&user_id=$user_id'>[{$lang['global-add']}]</a>";
	
		echo "</div>";
 		echo "<div style='width: 50%; float: left;'>";
			echo "<img src='images/silhouette.png' height='75' /><br />";

		echo "{$lang['no-avalista']} #2<br />";
	
		echo "</div>";
		
		if ($interview == 0) {
			$interviewed = "<span class='negative'><strong>{$lang['global-no']}</strong></span>";
		} else {
			$interviewed = "<strong style='color: #005c0b;'>{$lang['global-yes']}</strong>";
		}
		
		echo "<br />&nbsp;<br />{$lang['interviewed-member']}: $interviewed";
		
	}
		echo "</center>";
		echo "</div>";
		
?>
 

 <div id="detailedinfo">
 

  <div id="leftpane">
<h4><?php echo $lang['member-personal']; ?></h4>
<?php echo $nationality; ?><br />
<?php echo $lang['global-birthday'] . ": " . $birthday; ?><br />
<?php
		echo "<span style='display: block; margin-bottom: 3px;'>{$lang['dni-or-passport']}: " . $dni . "</span>";
?>


<?php	
	if (file_exists($file)) {
		echo "<a href='images/_$domain/ID/" . $user_id . "-front." . $dniext1 . "'><img src='images/dni-iconbig.png' style='margin-top: 10px;'/></a> <a href='new-id-scan-front.php?user_id=$user_id'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 5px; margin-bottom: 2px;' /></a> <br />";
	} else {
		echo "<img src='images/dni-icon-nabig.png' style='margin-top: 10px;' /> <a href='new-id-scan-front.php?user_id=$user_id'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 8px; margin-bottom: 2px;' /></a> <br />";
	}
			
	if (file_exists($file2)) {
		echo "<a href='images/_$domain/ID/" . $user_id . "-back." . $dniext2 . "'><img src='images/dni-icon-2big.png' style='margin-top: 10px;' /></a> <a href='new-id-scan-back.php?user_id=$user_id'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 8px; margin-bottom: 2px;' /></a> <br />";
	} else {
		echo "<img src='images/dni-icon-na-2big.png' style='margin-top: 10px;' /> <a href='new-id-scan-back.php?user_id=$user_id'> <img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 7px; margin-bottom: 2px;' /></a> <br />";
	}
		
	if (file_exists($file3)) {
		echo "<a href='images/_$domain/sigs/" . $user_id . ".$sigext'><img src='images/sig-iconbig.png' style='margin-top: 10px;' /></a> <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 5px; margin-bottom: 2px;' /></a> <br />";
	} else {
		echo "<img src='images/sig-icon-nabig.png' style='margin-top: 10px;'/> <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 8px; margin-bottom: 2px;' /></a> <br />";
	}
?>


	<br />

<h4><?php echo $lang['member-usage']; ?></h4>
<?php echo $lang['global-type']; ?>: 
<?php
	if ($usageType == 1) {
		echo $lang['member-medicinal'];
	} else {
		echo $lang['member-recreational'];
	}
?><br />
<?php echo $lang['member-monthcons']; ?>: <?php echo $mconsumption; ?> g.<br /><br />
  </div> <!-- END LEFTPANE -->
  <div id="rightpane">

<h4><?php echo $lang['member-contactdetails']; ?></h4>
<?php echo $telephone; ?><br />
<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br /><br />
<?php echo $street . " " . $streetnumber . " " . $flat; ?><br />
      <?php echo $postcode; ?> <?php echo $city; ?><br />
      <?php echo $country; ?><br /><br />
<!--<h4>System specifics</h4>
User ID: <?php echo $user_id; ?><br />
Signup source: <?php echo $signupsource; ?><br />
Card ID: <?php echo $cardid; ?><br />-->

<h4><?php echo $lang['discounts']; ?></h4>
<?php echo $lang['member-discountD']; ?>: <?php echo $discount; ?>%<br />
<?php echo $lang['member-discountBar']; ?>: <?php echo $discountBar; ?>%<br />
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  <div id="userPreferences">
  <div id="leftpane">
<h4><?php echo $lang['member-preferences']; ?></h4>
<?php if ($favouriteCategory != '') { echo $lang['global-category']; ?>: <?php echo " Flor (" . number_format($percentage,0) . "%)"; } ?><br />
<?php if ($favourite1 != '') { ?> #1: <?php echo $favourite1 . " (" . number_format($quantity1,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite2 != '') { ?> #2: <?php echo $favourite2 . " (" . number_format($quantity2,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite3 != '') { ?>#3: <?php echo $favourite3 . " (" . number_format($quantity3,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite4 != '') { ?>#4: <?php echo $favourite4 . " (" . number_format($quantity4,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite5 != '') { ?>#5: <?php echo $favourite5 . " (" . number_format($quantity5,0) . " g)"; ?> <?php } ?>
	<br /><br />


  </div> <!-- END LEFTPANE -->
  <div id="rightpane">

<h4><?php echo $lang['member-weeklyavgs']; ?></h4>
<?php echo $lang['global-dispenses']; ?>: <?php echo number_format($totalDispensesPerWeek,0); ?><br />
<?php echo $lang['member-spenditure']; ?>: <?php echo number_format($totalAmountPerWeek,0); ?> <?php echo $_SESSION['currencyoperator'] ?><br /><br />

  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  </div> <!-- END LEFTPROFILE -->
 
 
 <div id="statistics">
  <h4><?php echo $lang['member-dispensehistory']; ?></h4>
  <table class="default memberStats">
   <tr>
    <td class="first"><?php echo $lang['dispensary-thisweek']; ?>:</td>
    <td><?php echo number_format($quantityWeek,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeek,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeek,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-lastweek']; ?>:</td>
    <td><?php echo number_format($quantityWeekMinusOne,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeekMinusOne,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusOne,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-twoweeksago']; ?>:</td>
    <td><?php echo number_format($quantityWeekMinusTwo,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeekMinusTwo,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusTwo,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date('F'); ?>:</td>
    <td class="<?php echo $monthClass;?>"><?php echo number_format($quantityMonth,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonth,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonth,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("first day of last month")); ?>:</td>
    <td class="<?php echo $monthMinusOneClass;?>"><?php echo number_format($quantityMonthMinus1,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus1,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus1,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-1 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusTwoClass;?>"><?php echo number_format($quantityMonthMinus2,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus2,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus2,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-2 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusThreeClass;?>"><?php echo number_format($quantityMonthMinus3,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus3,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus3,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   

   <tr>
    <td class="first"><?php echo date("F", strtotime("-3 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusFourClass;?>"><?php echo number_format($quantityMonthMinus4,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus4,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus4,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-4 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusFiveClass;?>"><?php echo number_format($quantityMonthMinus5,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus5,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus5,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-5 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusSixClass;?>"><?php echo number_format($quantityMonthMinus6,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus6,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus6,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-6 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusSevenClass;?>"><?php echo number_format($quantityMonthMinus7,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus7,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus7,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-7 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusEightClass;?>"><?php echo number_format($quantityMonthMinus8,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus8,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus8,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-8 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusNineClass;?>"><?php echo number_format($quantityMonthMinus9,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus9,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus9,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-9 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusTenClass;?>"><?php echo number_format($quantityMonthMinus10,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus10,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus10,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-10 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusElevenClass;?>"><?php echo number_format($quantityMonthMinus11,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus11,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus11,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-11 months", strtotime("first day of last month") )); ?>:</td>
    <td class="<?php echo $monthMinusTwelveClass;?>"><?php echo number_format($quantityMonthMinus12,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus12,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus12,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
  </table>

 </div>
 </div> <!-- END PROFILEWRAPPER -->
 <br /><br />
 <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a></center><br />
 	 <table class="default" id='mainTable'>
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $_SESSION['currencyoperator'] ?></th>
	    <th>Tot. g</th>
	    <th>Tot. u</th>
	    <th>Tot. <?php echo $_SESSION['currencyoperator'] ?></th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	  
	// Pagination
	if (isset($_GET['pageno'])) {
    	$pageno = $_GET['pageno'];
    } else {
    	$pageno = 1;
    }
    	$no_of_records_per_page = 20;
	
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_pages_sql = "SELECT COUNT(saleid) FROM sales WHERE userid = $user_id";
	$rowCount1 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(saleid) FROM b_sales WHERE userid = $user_id";
	$rowCount2 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(donationid) FROM donations WHERE userid = $user_id";
	$rowCount3 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(paymentid) FROM memberpayments WHERE userid = $user_id";
	$rowCount4 = $pdo3->query("$total_pages_sql")->fetchColumn();
	
	$rowCount = $rowCount1 + $rowCount2 + $rowCount3 + $rowCount4;
    
    $total_pages = ceil($rowCount / $no_of_records_per_page);

	// Query to look up individual sales
	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, 'sale' as Type, '' AS donatedTo FROM sales WHERE userid = $user_id UNION ALL SELECT saleid, saletime, userid, amount, '' AS amountpaid, '' AS quantity, unitsTot AS units, adminComment, creditBefore, creditAfter, 'bar' as Type, '' AS donatedTo FROM b_sales WHERE userid = $user_id UNION ALL SELECT donationid, donationTime as saletime, userid, amount, '' AS amountpaid, '' AS quantity, '' AS units, '' AS adminComment, creditBefore, creditAfter, 'donation' as Type, donatedTo AS donatedTo FROM donations WHERE userid = $user_id UNION ALL SELECT paymentid, paymentdate as saletime, userid, amountPaid AS amount, '' AS amountpaid, '' AS quantity, '' AS units, '' AS adminComment, creditBefore, creditAfter, 'memberpayment' as Type, paidTo AS donatedTo FROM memberpayments WHERE userid = $user_id ORDER by saletime DESC LIMIT $offset, $no_of_records_per_page";
	
	try
	{
		$results = $pdo3->prepare("$selectSales");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($sale = $results->fetch()) {
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
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
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>

		";
				
// Separate methodologies and row displays (linkage) for donations vs sales. Donations next:
		} else if ($type == 'donation') {
			echo "
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['donation-donation']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>

		";
				
		} else if ($type == 'memberpayment') {
				
			if ($donatedTo == '2') {
				$paidTo = $lang['card'];
			} else if ($donatedTo == '3') {
				$paidTo = $lang['global-credit'];
			} else if ($donatedTo == '4') {
				$paidTo = "CashDro";
			} else if ($donatedTo == '5') {
				$paidTo = $lang['changed-expiry'];
			} else {
				$paidTo = $lang['cash'];
			}
		
			echo "
  	   <td class='clickableRow'>{$formattedDate}</td>
  	   <td class='clickableRow' colspan='6'>{$lang['membership-payments']}: $paidTo</td>
		<td class='clickableRow right'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right'>$credit</td>
		<td class='clickableRow right'>$newcredit</td>";
				
// Separate methodologies and row displays (linkage) for donations vs sales. Sales next:
			} else if ($type == 'sale') {
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$onesaleResult2 = $pdo3->prepare("$selectoneSale");
			$onesaleResult2->execute();
			$onesaleResult3 = $pdo3->prepare("$selectoneSale");
			$onesaleResult3->execute();
			$onesaleResult4 = $pdo3->prepare("$selectoneSale");
			$onesaleResult4->execute();
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
		while ($onesale = $onesaleResult->fetch()) {
			if ($onesale['category'] == 1) {
				$category = 'Flower';
			} else if ($onesale['category'] == 2) {
				$category = 'Extract';
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				
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
					$catType = $row['type'];
			}
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = $onesaleResult2->fetch()) {
			
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
		while ($onesale = $onesaleResult3->fetch()) {
			
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				try
				{
					$result = $pdo3->prepare("$categoryDetailsC");
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
					$type = $row['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		echo "
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>

		";
		
		
		// And finally, bar
	} else {
		
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult6 = $pdo3->prepare("$selectoneSale");
			$onesaleResult6->execute();
			$onesaleResult7 = $pdo3->prepare("$selectoneSale");
			$onesaleResult7->execute();
			$onesaleResult8 = $pdo3->prepare("$selectoneSale");
			$onesaleResult8->execute();
			$onesaleResult9 = $pdo3->prepare("$selectoneSale");
			$onesaleResult9->execute();
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
		while ($onesale = $onesaleResult6->fetch()) {
			
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
		while ($onesale = $onesaleResult7->fetch()) {
			
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
		while ($onesale = $onesaleResult8->fetch()) {
			echo number_format($onesale['quantity'],0) . "<br />";
		}
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		while ($onesale = $onesaleResult9->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		echo "
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$credit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>

		";
		
	}
	
}

echo "</table>";
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

<!-- Pagination code BEGIN -->
<style>
a.pagination {
	display: inline-block;
	background-color: #eee;
	border: 1px solid #ccc;
	width: 50px;
	height: 50px;
	line-height: 50px;
	margin: 5px;
	color: #333;
}
a.pagination.disabled {
	background-color: #ccc;
	border: 1px solid #aaa;
}
</style>
<center>
<br />
<a href="?pageno=1<?php echo $sortparam . '&user_id=' . $user_id; ?>" class='pagination <?php if ($pageno == 1 || (!isset($_GET['pageno']))) { echo 'disabled'; } ?>'>&laquo;</a>
<a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } echo $sortparam . '&user_id=' . $user_id; ?>" class='pagination <?php if($pageno <= 1){ echo 'disabled'; } ?>'>Prev</a>
<a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } echo $sortparam . '&user_id=' . $user_id; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>Next</a>
<a href="?pageno=<?php echo $total_pages; echo $sortparam . '&user_id=' . $user_id; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>&raquo;</a>
</center>
<!-- Pagination code END -->

<?php  displayFooter(); ?>
	
