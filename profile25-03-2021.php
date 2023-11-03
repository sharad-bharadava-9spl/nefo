<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
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
		
	if ($_GET['success'] == 'true') {
		$_SESSION['successMessage'] = "Thank you. The member has been invited!<br />Gracias. ?El socio ha sido invitado!";
	}
	// Dabulance customization
	if ($_SESSION['domain'] == 'dabulance') {
			
		try
		{
			$result = $pdo3->prepare("SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.friend2, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(), u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.dniext1, u.dniext2, u.workStation, u.bajaDate, u.starCat, u.interview, u.exento, u.fptemplate1, u.usergroup2, u.sigext, u.contributor, u.map, u.calendar, u.menu FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	} else {
		
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
		
	}

	$row = $result->fetch();
		$user_id = $row['user_id'];
		$memberno = $row['memberno'];
		$registeredSince = $row['registeredSince'];
		$membertime = date("M y", strtotime($registeredSince));
		$userGroup = $row['userGroup'];
		$groupName = $row['groupName'];
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
		$contributor = $row['contributor'];
		$map = $row['map'];
		$calendar = $row['calendar'];
		$menu = $row['menu'];
		
		if ($sigext == '') {
			$sigext = 'png';
		}
	if ($usageType == 1) {
		$medicalicon = "<tr>
     <td><img src='images/medical-new.png' style='margin-bottom: -1px;' /></td>
     <td colspan='3'>{$lang['medical-user']}</td>
    </tr>";
	} else {
		$medicalicon = "";
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
	if ($_SESSION['showAge'] == 1) {
		$age = $age . " " . $lang['member-yearsold'];
	} else {
		$age = '';
	}

	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		$bdayicon = "<tr>
     <td><img src='images/birthday-cake.png' style='margin-bottom: -2px;' /></td>
     <td colspan='3'><span style='color: #f6ae4a;'><strong>{$lang['global-birthday']}!</strong> ($age)</span> </td>
    </tr>";
	} else {
		$bdayicon = "<tr>
     <td><img src='images/birthday.png' style='margin-bottom: -2px;' /></td>
     <td colspan='3'>$birthday, $age</td>
    </tr>";
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

	$('#mainTable').tablesorter({
		usNumberFormat: true,
	}); 	
	$('.memberStats').tablesorter({
		usNumberFormat: true
	}); 
});
	
function delete_note(noteid, userid) {
	if (confirm("{$lang['confirm-deletenote']}")) {
				window.location = "uTil/delete-note.php?noteid=" + noteid + "&userid=" + userid;
				}
}
function delete_fingerprint(user_id) {
	
	if (confirm("Estas seguro? No puedes reversar este acci?n!")) {
		window.location = "uTil/delete-fingerprint.php?user_id=" + user_id;
	}
}

function invite_user(user_id) {
	if (confirm("{$lang['confirm-invite']}")) {
				window.location = "invite.php?user_id=" + user_id;
				}
}

EOD;

	if (isset($_GET['cardadded'])) {
		
		$_SESSION['successMessage'] = $lang['card-chip-added'];
		
	}
	
	if (isset($_GET['newSig'])) {
		
		$oldfile4 = $google_root_folder.'images/_' . $_SESSION['domain'] . '/sigs/' . $_SESSION['tempNo'] . '.png';
		$newfile4 = $google_root_folder.'images/_' . $_SESSION['domain'] . '/sigs/' . $user_id . '.png';
		rename_object($google_bucket, $oldfile4, $google_bucket, $newfile4);
		$_SESSION['successMessage'] = $lang['signature-saved'];
		
	}


	pageStart($lang['title-memberprofile'], NULL, $deleteNoteScript, "pprofilenew", NULL, $lang['member-profilecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<script>
	// disable back script
	(function (global) {

	  if(typeof (global) === "undefined")
	  {
	    throw new Error("window is undefined");
	  }

	    var _hash = "!";
	    var noBackPlease = function () {
	        global.location.href += "#";

	    // making sure we have the fruit available for juice....
	    // 50 milliseconds for just once do not cost much (^__^)
	        global.setTimeout(function () {
	            global.location.href += "!";
	        }, 50);
	    };
	  
	  // Earlier we had setInerval here....
	    global.onhashchange = function () {
	        if (global.location.hash !== _hash) {
	            global.location.hash = _hash;
	        }
	    };

	    global.onload = function () {
	        
	    noBackPlease();

	    // disables backspace on page except on input fields and textarea..
	    document.body.onkeydown = function (e) {
	            var elm = e.target.nodeName.toLowerCase();
	            if (e.which === 8 && (elm !== 'input' && elm  !== 'textarea')) {
	                e.preventDefault();
	            }
	            // stopping event bubbling up the DOM tree..
	            e.stopPropagation();
	        };
	    
	    };

	})(window);		
</script>

<center>
 <div id="profilebuttons">
 
<?php

if ($_SESSION['puestosOrNot'] == 1 && $_SESSION['userGroup'] > 1) {
	
	if ($_SESSION['workstation'] == 'bar') {
		
		echo "<a href='bar-new-sale-2.php?user_id=$user_id' class='cta'><img src='images/main-baricon.png' height='26' style='margin-right: 3px; margin-bottom: -5px;' /> {$lang['bar']}</a>";
		
	} else if ($_SESSION['workstation'] == 'dispensary') {
		
		echo "<a href='new-dispense-2.php?user_id=$user_id' class='cta'><img src='images/main-dispense.png' height='26' style='margin-right: 3px; margin-bottom: -5px;' /> {$lang['global-dispense']}</a>";
		
	}

} else {
	
	echo "	
  <a href='new-dispense-2.php?user_id=$user_id' class='cta'><img src='images/main-dispense.png' height='26' style='margin-right: 3px; margin-bottom: -5px;' /> {$lang['global-dispense']}</a>
  <a href='bar-new-sale-2.php?user_id=$user_id' class='cta'><img src='images/main-baricon.png' height='26' style='margin-right: 3px; margin-bottom: -5px;' /> {$lang['bar']}</a>";
  
}



	$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $user_id . '.' .  $photoext;

	$memberPhoto_exist = object_exist($google_bucket, $google_root_folder.$memberPhoto);
	
	if (!$memberPhoto_exist) {
		$memberPhoto = "<img class='profilepic' src='{$google_root}images/silhouette-new-big.png' />";
		$deletePhoto = '';
	} else {
		$memberPhoto = "<img class='profilepic' src='{$google_root}$memberPhoto' width='237' />";
		$deletePhoto = "<div style='position: absolute; width: 15px; height: 15px; padding: 5px; background-color: #fff; top: 5px; left: 5px; z-index: 99999; opacity: 0.8;'><a href='uTil/delete-photo.php?user_id=$user_id'><img src='images/delete.png' width='15' /></a></div>";	
	}
	
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$groupName</span>";	
		 $banTextDiv = "<div id='bantext' style='display:none;'><img src='images/delete.png' id='closeban' width='15'><p>".$banComment."</p></div>";	
	} else if ($userGroup == 5 && $exento == '1') {
		$groupName = "<span class='usergrouptext'>{$lang['exempt']}</span>";		
	} else if ($userGroup == 5) {
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$groupName = "<span class='usergrouptext'><a href='pay-membership.php?user_id=$user_id'>{$lang['member-memberuntil']} $memberExpReadable</a></span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$groupName</span>";
	}
	if ($groupName2 != '') {
		$groupName2 = "<span class='usergrouptext2'>$groupName2</span><br />";
	} else {
		$groupName2 = "";
	}

		$highRollerWeekly = $_SESSION['highRollerWeekly'];
		$consumptionPercentage = $_SESSION['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly) {
		$highroller = "<br /><div class='highrollerholder'><img src='images/trophy.png' style='margin-bottom: -2px;'/> High roller</div>";
		$highroller2 = "<tr><td><img src='images/trophy-g.png' style='margin-bottom: -1px;' /></td><td>High roller</td></tr>";

	} else {
		$highroller = ""; 
	}

?>
  <a href="edit-profile.php?user_id=<?php echo $user_id;?>" class="cta ctahover" style="background-color: #fff; color: #00a48c;"><?php echo $lang['global-edit']; ?></a><br />
	<span id='rowtwo'>
  <a href="new-card-0.php?user_id=<?php echo $user_id;?>" class="cta2"><?php echo $lang['new-chip-card']; ?></a>
  <?php if ($userGroup < 4) { ?> <a href="new-password.php?user_id=<?php echo $user_id;?>" class="cta2"><?php echo $lang['password']; ?></a> <?php } ?>
  <a href="notes.php?userid=<?php echo $user_id;?>" class="cta2"><?php echo $lang['add-note']; ?></a>
  <a href="member-visits.php?userid=<?php echo $user_id;?>" class="cta2"><?php echo $lang['visits']; ?></a>
  <?php if ($_SESSION['domain'] == '1up' || $_SESSION['domain'] == 'damsquare' || $_SESSION['domain'] == 'koala' || $_SESSION['domain'] == 'nectar' || $_SESSION['domain'] == 'gasstation') { ?>
  <a href="edit-discounts.php?user_id=<?php echo $user_id; ?>" class="cta2"><?php echo $lang['discounts']; ?></a>
<?php } else if ($_SESSION['userGroup'] < 2) { ?>
  <a href="edit-discounts.php?user_id=<?php echo $user_id; ?>" class="cta2"><?php echo $lang['discounts']; ?></a>
  <?php } ?>
  <a href="aval-details.php?user_id=<?php echo $user_id; ?>&chain=true" class="cta2"><?php echo $lang['guardianship']; ?></a>
  <a href="member-log.php?user_id=<?php echo $user_id; ?>&chain=true" class="cta2">Log</a>
<!--  <?php if ($_SESSION['userGroup'] < 2) { ?><br /><a href="member-log.php?user_id=<?php echo $user_id;?>" class="cta2" style="margin-top: -25px; background-color: red;"><?php echo $lang['member-log']; ?></a> 
  <?php if ($userGroup < 4) { ?> <a href="worker-log.php?operator=<?php echo $user_id;?>" class="cta2" style="margin-top: -25px; background-color: red;"><?php echo $lang['worker-log']; ?></a> <?php } } ?>-->
<?php 

	$query = "SELECT services, appointments FROM systemsettings";
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
		$services = $row['services'];
		$appointments = $row['appointments'];

	if ($_SESSION['domain'] != 'dabulance') {
		
		if (($services > 0 && $services != '') || $appointments == 1) {		
		
			echo "<a href='invite.php?user_id=$user_id' class='cta2' style='background-color: red; color: yellow;'>{$lang['invite']}</a>";
		}
  
	}
	
		if ($_SESSION['puestosOrNot'] == 1 && $userGroup < 4) {
		
			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16 || $workStation == 101 || $workStation == 106 || $workStation == 111 || $workStation == 116) {
				$wsdisplay .= "<img src='images/puesto-reception.png' height='22' style='margin-bottom: -6px; margin-right: 8px;' />&nbsp;";
			}
			if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16 || $workStation == 105 || $workStation == 106 || $workStation == 115 || $workStation == 116) {
				$wsdisplay .= "<img src='images/puesto-bar.png' height='22' style='margin-bottom: -6px; margin-right: 8px;' />&nbsp;";
			}
			if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16 || $workStation == 110 || $workStation == 111 || $workStation == 115 || $workStation == 116) {
				$wsdisplay .= "<img src='images/puesto-dispensary.png' height='22' style='margin-bottom: -6px; margin-right: 8px;' />&nbsp;";
			}
			if ($workStation > 99 && $workStation < 1000) {
				$wsdisplay .= "<img src='images/puesto-stockroom.png' height='22' style='margin-bottom: -6px;' />&nbsp;";
			}
			
			$wsdisplay .=  "<br />";
			
		}
		
		// Dabulance customization
		if ($_SESSION['domain'] == 'dabulance') {
			
			if (strpos($contributor, 'v') !== false) {
				$contributortype .= "&nbsp;<a href='dab-invite-vendor.php'><span class='usergrouptext3'>Vendor</span></a>";
			}
			if (strpos($contributor, 's') !== false) {
				$contributortype .= "&nbsp;&nbsp;<span class='usergrouptext3'>Services</span>";
			}
			if (strpos($contributor, 'a') !== false) {
				$contributortype .= "&nbsp;&nbsp;<span class='usergrouptext3'>Artist</span>";
			}
			
			if ($map == 1) {
				$dabaccess .= "<img src='images/access-map.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
			if ($calendar == 1) {
				$dabaccess .= "<img src='images/access-calendar.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
			if ($menu == 1) {
				$dabaccess .= "<img src='images/access-menu.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
			
		}
		
?>
  </span>
 </div>
</center>
<div id="mainbox">
 <div id="mainleft">
  <span id="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>"><?php echo $memberPhoto; ?></a><?php echo $deletePhoto; ?><?php echo $highroller; ?></span>
<?php

	echo <<<EOD
   <span class='firsttext'>#$memberno&nbsp;<a href="change-registration-date.php?userid=$user_id" style='font-size: 16px; color: #f6ae4a;'>($membertime)</a></span> <a href="member-contract.php?user_id=$user_id" target="_blank"><img src="images/contract-new.png" style='margin-bottom: -3px; margin-left: 5px;'/></a><br /><span class='nametext'>$userStar $first_name $last_name</span><br />
   $groupName $contributortype<br />$wsdisplay<br />$banTextDiv
EOD;

		
	echo "$groupName2"; 
		
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

	echo "<br /><a href='donation-management.php?userid=" . $user_id . "'><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($userCreditDisplay,2) . " ".$_SESSION['currencyoperator']."$creditEligibility</span></span></a>$dabaccess";

    if ($_SESSION['saldoGift'] == 1) {
	    echo "&nbsp;<a href='gift-credit.php?user_id=$user_id' ><img src='images/gift-noframe.png' style='display: inline-block; margin-top: -3px; margin-left: 10px;'/></a>";
    }
    
    echo "<br /><br />";
    
	
	
	if ($_SESSION['showGender'] == 1) {
		if ($gender == 'Male') {
			$gender = $lang['member-male'];
			$gendericon = "<img src='images/gender.png' style='margin-bottom: -3px;' />";
		} else if ($gender == 'Female') {
			$gender = $lang['member-female'];
			$gendericon = "<img src='images/gender-female.png' style='margin-bottom: -3px;' />";
		} else {
			$gender = '';
		}
	} else {
		$gender = '';
	}
	
	if ($_SESSION['showAge'] == 1) {
		$age = $age . " " . $lang['member-yearsold'];
	} else {
		$age = '';
	}


	echo <<<EOD
    <table class='smallinfo'>
$bdayicon $medicalicon
    <tr>
     <td><img src="images/new-flag.png" style="margin-bottom: -2px;" /></td>
     <td>$nationality</td>
     <td>$gendericon</td>
     <td>$gender</td>
    </tr>
   </table>
EOD;


?>
  
 </div>
 
<?php

	// Check for all warnings, if any warning found set warningflag = 1
	// if flag = 1, show the box, if not, do nothing
	if ($_SESSION['fingerprint'] == 1) {
	
		if ($fptemplate1 == '') {
		$warningbox .= <<<EOD
  <a href='jmu_create_user.php?user_id=$user_id' class='smallwarning finger'>
   {$lang['finger-not-registered']}
  </a>
EOD;

	    	$warningflag = 1;
		} else {
		$warningbox .= <<<EOD
  <a href='javascript:delete_fingerprint($user_id)' class='smallwarning finger'>
   {$lang['delete-finger']}
  </a>
EOD;
	    $warningflag = 1;
		}
		
	}
	

	
	$file = $google_root_folder.'images/_' . $_SESSION['domain'] . '/ID/' . $user_id . '-front.' . $dniext1;
	$file2 = $google_root_folder.'images/_' . $_SESSION['domain'] . '/ID/' . $user_id . '-back.' . $dniext2;
	$file3 = $google_root_folder.'images/_' . $_SESSION['domain'] . '/sigs/' . $user_id . ".$sigext";

	$file1_exist = object_exist($google_bucket, $file);
	$file2_exist = object_exist($google_bucket, $file2);
	$file3_exist = object_exist($google_bucket, $file3);

	if ($file1_exist === false) {
		$warningbox .= <<<EOD
  <a href='new-id-scan-front.php?user_id=$user_id' class='smallwarning dni'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['member-dninotscanned']}
  </a>
EOD;
	    $warningflag = 1;
	}
	
	if ($file3_exist === false) {
		$warningbox .= <<<EOD
  <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType' class='smallwarning signature'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['signature-missing']}
  </a>
EOD;

	    $warningflag = 1;
	}
	
	
	if ($userNotes != '') {
		$i = 1;
	    $warningflag = 1;
		
		$warningbox .= <<<EOD
  <a class='smallwarning comment' href="#" id='adminComment' onClick="javascript:toggleDiv('userNotes'); return false;">
  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['comments']}
EOD;

	if ($_GET['deleted'] == 'yes' || isset($_GET['openComment'])) {
		$warningbox .= "<div id='userNotes'>";
	} else {
		$warningbox .= "<div id='userNotes' style='display: none;'>";
	}
	
		$warningbox .= <<<EOD
	 <table class="default">
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
	   		$warningbox .= <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$formattedDate</td>
  <td style='border-bottom: 0;'>$worker</td>
  <td style='border-bottom: 0;'>$note</td>
  <td style='border-bottom: 0;'></td>
 </tr>
EOD;
		} else {
			$warningbox .= <<<EOD
 <tr>
  <td>$formattedDate</td>
  <td>$worker</td>
  <td>$note</td>
  <td><a href="javascript:void(0);" onclick= "delete_note($noteid,$user_id);" style='z-index: 22000;'><img src='images/delete.png' width='15' /></a></td>
 </tr>
EOD;
	}
			$warningbox .= <<<EOD
EOD;
	$i++;
		}
$warningbox .= "</table></div></a>";
	}
	
	if ($_SESSION['domain'] == 'abuelitamaria') {
	// Check if socio has signed in:
		$selectRows = "SELECT COUNT(visitNo) FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
		$rowCount1 = $pdo3->query("$selectRows")->fetchColumn();

	$lastVisit = "SELECT visitNo, completed, scanin, warning FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
	try
	{
		$result = $pdo3->prepare("$lastVisit");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$completed = $row['completed'];
		$visitNo = $row['visitNo'];
		$scanin = $row['scanin'];
		$scaninCompare = date('d-m-Y', strtotime($scanin));
		$scaninReadable = date('d-m-Y H:i', strtotime($scanin));
		
		$visitTime = date('Y-m-d H:i:s');
		$visitTimeCompare = date('d-m-Y', strtotime($visitTime));
		
		tzo();
		$visitTimeReadable = date('H:i');
		
		if ($rowCount1 == 0) {
			
				$warningbox .= <<<EOD
	<span class='smallwarning'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' />Entrada no registrado
	</span>   
EOD;
	    		$warningflag = 1;
			
		} else {

			if ($completed == 1) {
				
				$warningbox .= <<<EOD
	<span class='smallwarning'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' />Entrada no registrado
	</span>   
EOD;
	    		$warningflag = 1;
				
			} else if ($completed == 0 && (strtotime($scaninCompare) < strtotime($visitTimeCompare))) {
				
				$warningbox .= <<<EOD
	<span class='bigwarning dni'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' />Última visita: $scaninReadable - <strong>no ha salida</strong>
	</span>   
EOD;
	    		$warningflag = 1;
				
			}
			
		}

		
		
		
		// Look up visit warnings
		$selectRows = "SELECT COUNT(visitNo) FROM newvisits WHERE userid = $user_id AND warning > 0 ORDER BY scanin DESC";
		$rowCount = $pdo3->query("$selectRows")->fetchColumn();
		$visitWarnings = "SELECT visitNo, completed, scanin, scanout, warning FROM newvisits WHERE userid = $user_id AND warning > 0 ORDER BY scanin DESC";
		try
		{
			$results = $pdo3->prepare("$visitWarnings");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		
		if ($rowCount > 0) {

		$k = 1;
	    $warningflag = 1;
		
		$warningbox .= <<<EOD
  <a class='smallwarning comment' href="#" id='adminComment' onClick="javascript:toggleDiv('visitNotes'); return false;">
  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> Problemas con visitas
EOD;

		if ($_GET['vWdeleted'] == 'yes') {
		$warningbox .= "<div id='visitNotes'>";
	} else {
		$warningbox .= "<div id='visitNotes' style='display: none;'>";
	}
	
		$warningbox .= <<<EOD
	 <table class="default" style="width: 700px; margin-left: -700px; margin-top: 10px; color: #666; border: 2px solid #bbb;">
  	  <tr>
  	   <th class="smallerfont" style='width: 120px;'><strong>Entrada</strong></th>
  	   <th class="smallerfont" style='width: 120px;'><strong>Salida</strong></th>
  	   <th class="smallerfont" colspan='2'><strong>{$lang['global-comment']}</strong></th>
	  </tr>
EOD;
		foreach ($results as $visitWarning) {
	
				$scanInDate = date("d-m-y H:i", strtotime($visitWarning['scanin'] . "+$offsetSec seconds"));
				
				if ($visitWarning['scanout'] == NULL) {
					$scanOutDate = '';
				} else {
					$scanOutDate = date("d-m-y H:i", strtotime($visitWarning['scanout'] . "+$offsetSec seconds"));
				}
				
				$visitNo = $visitWarning['visitNo'];
				$warning = $visitWarning['warning'];
				
				if ($warning == 1) {
					
					$warningMessage = "Duración de visita menos de 15 minutos";
					
				} else if ($warning == 2) {
					
					$warningMessage = "Ha olvidado escanear cuando salido el club";
					
				} else {
					
					$warningMessage = "";
					
				}
			
		if($k == $noteCount) {
	   		$warningbox .= <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$scanInDate</td>
  <td style='border-bottom: 0;'>$scanOutDate</td>
  <td style='border-bottom: 0;'>$warningMessage</td>
  <td style='border-bottom: 0;'><a href="javascript:void(0);" onclick="delete_warning($visitNo, $user_id)" style='z-index: 22000;'><img src='images/delete.png' width='15' /></a></td>
 </tr>
EOD;
		} else {
			$warningbox .= <<<EOD
 <tr>
  <td>$scanInDate</td>
  <td>$scanOutDate</td>
  <td>$warningMessage</td>
  <td><a href="javascript:void(0);" onclick="delete_warning($visitNo, $user_id)" style='z-index: 22000;'><img src='images/delete.png' width='15' /></a></td>
 </tr>
EOD;
	}
			$warningbox .= <<<EOD
EOD;
	$k++;
		}
$warningbox .= "</table></div></a>";
	}
		
		
	// Check for short visits
	} else if ($_SESSION['entrysysstay'] > 0) {
		
		$minStay = $_SESSION['entrysysstay'];

		$selectRows = "SELECT duration FROM newvisits WHERE userid = $user_id AND completed = 1 ORDER BY scanin DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$selectRows");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$duration = $row['duration'];
			
		
		// 
		if ($duration < $minStay) {
		

		$visitWarnings = "SELECT visitNo, scanin, scanout, duration FROM newvisits WHERE userid = $user_id AND completed = 1 AND duration < $minStay ORDER BY scanin DESC LIMIT 1";

		try
		{
			$results = $pdo3->prepare("$visitWarnings");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		$i = 1;
	    $warningflag = 1;
		
		$warningbox .= <<<EOD
  <a class='smallwarning comment' href="#" onClick="javascript:toggleDiv('userWarnings'); return false;">
  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['short-visits']}
EOD;

	if (isset($_GET['openComment2'])) {
		$warningbox .= "<div id='userWarnings'>";
	} else {
		$warningbox .= "<div id='userWarnings' style='display: none;'>";
	}
	
		$warningbox .= <<<EOD
	 <table class="profileNew">
  	  <tr>
  	   <th class="smallerfont left" style='width: 120px;'><strong>{$lang['pur-date']}</strong></th>
  	   <th class="smallerfont left" style='width: 120px;'><strong>{$lang['entry']}</strong></th>
  	   <th class="smallerfont left" style='width: 120px;'><strong>{$lang['exit']}</strong></th>
  	   <th class="smallerfont left" colspan='2'><strong>{$lang['duration']}</strong></th>
	  </tr>
EOD;

		while ($userNote = $results->fetch()) {
	
			$visitdate = date("d-m-y H:i", strtotime($userNote['scanin'] . "+$offsetSec seconds"));
			$entry = date("H:i", strtotime($userNote['scanin'] . "+$offsetSec seconds"));
			$exit = date("H:i", strtotime($userNote['scanout'] . "+$offsetSec seconds"));
			$duration = $userNote['duration'];
			
		if($i == $rowCount) {
			
	   		$warningbox .= <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$visitdate</td>
  <td style='border-bottom: 0;'>$entry</td>
  <td style='border-bottom: 0;'>$exit</td>
  <td style='border-bottom: 0;'>$duration min.</td>
 </tr>
EOD;
		} else {
			
			$warningbox .= <<<EOD
 <tr>
  <td>$visitdate</td>
  <td>$entry</td>
  <td>$exit</td>
  <td>$duration min.</td>
 </tr>
EOD;

		}
	$i++;
		}
$warningbox .= "</table></div></a>";
	}
	
}

	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
		$warningbox .= <<<EOD
  <a href='pay-membership.php?user_id=$user_id' class='bigwarning cuota'>
  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' />
   {$lang['member-expirestoday']}
  </a>
EOD;
	    	$warningflag = 1;
	    	
		  	if ($paymentWarning == '1') {
		$warningbox .= <<<EOD
   <a href='pay-membership.php?user_id=$user_id' class='bigwarning cuota'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: -14px; margin-right: 5px;' />
   {$lang['member-receivedwarning']}: $paymentWarningDateReadable
  </a>
EOD;
			}

		} else if (strtotime($memberExp) < strtotime($timeNow)) {
			
		$warningbox .= <<<EOD
   <a href='pay-membership.php?user_id=$user_id' class='bigwarning cuota'>
  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> 
   {$lang['member-expiredon']}: $memberExpReadable
  </a>
EOD;

	    	$warningflag = 1;
	    	
		  	if ($paymentWarning == '1') {
		$warningbox .= <<<EOD
   <a href='pay-membership.php?user_id=$user_id' class='bigwarning cuota'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: -14px; margin-right: 5px;' />
   {$lang['member-receivedwarning']}: $paymentWarningDateReadable
  </a>
EOD;
	    	$warningflag = 1;
	    	
		  	}
		  	
		}
		
	}

	
	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	
	if ($quantityMonth >= $mconsumption) {
		$warningbox .= <<<EOD
  <div class='bigwarning'>
  <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['member-conslimitexc']} (+$consumptionDelta g)</a>
  </div>
EOD;
	    	$warningflag = 1;
	} else if ($consumptionDeltaPlus < ($mconsumption * $consumptionPercentage)) {
		$warningbox .= <<<EOD
  <div class='bigwarning'>
  <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['member-conslimitnear']} ($consumptionDeltaPlus g {$lang['global-remaining']})</a>
  </div>
EOD;
	    	$warningflag = 1;
	}
	
	if ($warningflag == 1) {
		
		echo <<<EOD
 <div id="mainright">
$warningbox
 </div>
EOD;

	}
?>
 </div>
 </div>
 <div class="mainwrapper">
  <div class="mainbox3">
   <div class='mainboxheader'>
    <img src="images/calendar.png" style='margin-bottom: -5px; margin-right: 5px;' /> <?php echo $lang['member-dispensehistory']; ?>
   </div>
   <div class='mainboxcontent'>
  <table class="memberStats">
   <tr>
    <th><?php echo $lang['pur-date']; ?></th>
    <th><?php echo $lang['grams']; ?></th>
    <th><?php echo $lang['units']; ?></th>
    <th><?php echo $lang['global-amount']; ?></th>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-thisweek']; ?></td>
    <td><?php echo number_format($quantityWeek,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeek,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeek,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-lastweek']; ?></td>
    <td><?php echo number_format($quantityWeekMinusOne,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeekMinusOne,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusOne,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-twoweeksago']; ?></td>
    <td><?php echo number_format($quantityWeekMinusTwo,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsWeekMinusTwo,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusTwo,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date('F'); ?></td>
    <td class="<?php echo $monthClass;?>"><?php echo number_format($quantityMonth,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonth,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonth,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("first day of last month")); ?></td>
    <td class="<?php echo $monthMinusOneClass;?>"><?php echo number_format($quantityMonthMinus1,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus1,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus1,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-1 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusTwoClass;?>"><?php echo number_format($quantityMonthMinus2,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus2,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus2,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-2 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusThreeClass;?>"><?php echo number_format($quantityMonthMinus3,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus3,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus3,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   

   <tr>
    <td class="first"><?php echo date("F", strtotime("-3 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusFourClass;?>"><?php echo number_format($quantityMonthMinus4,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus4,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus4,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-4 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusFiveClass;?>"><?php echo number_format($quantityMonthMinus5,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus5,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus5,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-5 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusSixClass;?>"><?php echo number_format($quantityMonthMinus6,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus6,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus6,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-6 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusSevenClass;?>"><?php echo number_format($quantityMonthMinus7,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus7,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus7,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-7 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusEightClass;?>"><?php echo number_format($quantityMonthMinus8,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus8,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus8,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-8 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusNineClass;?>"><?php echo number_format($quantityMonthMinus9,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus9,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus9,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-9 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusTenClass;?>"><?php echo number_format($quantityMonthMinus10,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus10,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus10,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-10 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusElevenClass;?>"><?php echo number_format($quantityMonthMinus11,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus11,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus11,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-11 months", strtotime("first day of last month") )); ?></td>
    <td class="<?php echo $monthMinusTwelveClass;?>"><?php echo number_format($quantityMonthMinus12,0); ?> <span class="smallerfont">g.</span></td>
    <td><?php echo number_format($unitsMonthMinus12,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus12,0); ?> <span class="smallerfont"><?php echo $_SESSION['currencyoperator'] ?></span></td>
   </tr>
  </table>
   </div>
  </div>
  <div class="mainbox2">
   <div class='mainboxheader'>
    <img src="images/shield.png" style='margin-bottom: -10px; margin-right: 5px;' /> <?php echo $lang['avalista']; ?>(s)
   </div>
   <div class='mainboxcontent'>
   
<?php

	// Check if aval 1 exists
	if ($friend > 0) {
		
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

		$fileF1_exist = object_exist($google_bucket, $google_root_folder.$fileF1);
					
 		echo "<a href='aval-details.php?user_id=$friend&chain=true' class='white'><div class='aval'>";
		
		if ($fileF1_exist) {
			echo "<img src='{$google_root}$fileF1' height='157' /><br />";
		} else {
			echo "<img src='{$google_root}images/silhouette-new.png' /><br />";
		}

		echo <<<EOD
     <span class='firsttext'>#$memberno1</span><br />
     <p class='secondtext'>$userStar1 $first_name1 $last_name1</p></a>
     <a href='add-aval.php?aval=1&user_id=$user_id' class='cta4'>{$lang['change']}</a> <a href='uTil/delete-aval.php?a=1&user_id=$user_id' style='margin-left: -16px;'><img src='images/delete.png' width='15' /></a>
</div>
EOD;
	
	} else {
		

		echo <<<EOD
<div class='aval'>

<img src='images/silhouette-new.png' /><br />
     <span class='firsttext'>&nbsp;</span><br />
<p class='secondtext'>{$lang['no-avalista']} 1</p>
 <a href='add-aval.php?aval=1&user_id=$user_id' class='cta4'>{$lang['global-add']}</a>
</div>
EOD;
	}
	
	if ($friend2 > 0) {
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

		$fileF2_exist = object_exist($google_bucket, $google_root_folder.$fileF2);
					
 		echo "<a href='aval-details.php?user_id=$friend2&chain=true' class='white'><div class='aval'>";
		
		if ($fileF2_exist) {
			echo "<img src='{$google_root}$fileF2' height='157' /><br />";
		} else {
			echo "<img src='{$google_root}images/silhouette-new.png' /><br />";
		}
		
		echo <<<EOD
     <span class='firsttext'>#$memberno2</span><br />
     <p class='secondtext'>$userStar2 $first_name2 $last_name2</p></a>
     <a href='add-aval.php?aval=1&user_id=$user_id&twoavals' class='cta4'>{$lang['change']}</a> <a href='uTil/delete-aval.php?a=2&user_id=$user_id' style='margin-left: -16px;'><img src='images/delete.png' width='15' /></a>
</div>
EOD;

} else {
		

		echo <<<EOD
<div class='aval'>

<img src='images/silhouette-new.png' /><br />
     <span class='firsttext'>&nbsp;</span><br />
<p class='secondtext'>{$lang['no-avalista']} 2</p>
 <a href='add-aval.php?aval=1&user_id=$user_id&twoavals' class='cta4'>{$lang['global-add']}</a>
</div>
EOD;
	}

?>
   </div>  
  </div>
  <div class="mainbox2">
   <div class='mainboxheader'>
    <img src="images/personal.png" style='margin-bottom: -10px; margin-right: 5px;' /> <?php echo $lang['member-personal']; ?>
   </div>
   <div class='mainboxcontent'>
    <div class='infobox'>
     <h4><?php echo $lang['member-usage']; ?></h4>
     <table>
      
<?php
	if ($usageType == 1) {
		echo "<tr><td><img src='images/medical-new.png' /></td><td>{$lang['member-medicinal']}</td></tr>";
	} else {
		echo "<tr><td></td><td>{$lang['member-recreational']}</td></tr>";
	}
	
	// If high roller
		echo $highroller2;
	// g per month
		echo "<tr><td><img src='images/stats.png' /></td><td>{$lang['member-monthcons']}:<br />$mconsumption g. / {$lang['monthLC']}</td></tr>";
	
?>
	</table>

    </div>
    <div class='infobox'>
     <h4><?php echo $lang['member-contactdetails']; ?></h4>
     <table>
      <tr>
       <td><img src="images/pin-new.png" /></td>
       <td><?php echo $street . " " . $streetnumber . " " . $flat; ?><br />
      <?php echo $postcode; ?> <?php echo $city; ?><br />
      <?php echo $country; ?>
</td>
      </tr>
      <tr>
       <td><img src="images/telephone.png" /></td>
       <td><?php echo $telephone; ?></td>
      </tr>
      <?php
      		$charLimit = 21;
      		$emailLength = strlen($email);
      		if($emailLength > $charLimit){
      			$emailString = substr($email, 0, $charLimit)."...";
      		}else{
      			$emailString = $email;
      		}

      ?>      <tr>
       <td><img src="images/email.png" /></td>
       <td style="position: relative;"><span id='emailbox_pop' style="cursor: pointer;"><?php echo $emailString; ?></span>
       		<div id='emailbox' class="helpBox" style="display: none; width: auto !important;"><?php echo $email; ?></div>
       </td>
      </tr>
    <?php if($emailLength > $charLimit){  ?>
			<script>
          	$('#emailbox_pop').on({
		 		'mouseover' : function() {
				 	$('#emailbox').css('display', 'block');
		  		},
		  		'mouseout' : function() {
				 	$('#emailbox').css('display', 'none');
			  	}
		  	});
		</script>
	<?php } ?>	
     </table>
       
    </div>
    <br />
<?php if ($_SESSION['domain'] == 'dabulance') {

	$query = "SELECT id, name, contract, description FROM contracts";
	try
	{
		$resultshop = $pdo3->prepare("$query");
		$resultshop->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	while ($shop = $resultshop->fetch()) {

		$id = $shop['id'];
		$contract = $shop['contract'];
		$name = $shop['name'];
		$description = $shop['description'];
		
		// Only show if contract has been sent/signed!
		$query = "SELECT status, sent FROM sentContracts WHERE user_id = '$user_id' AND contract = '$id'";
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
			$status = $row['status'];
			$sent = date("Y-m-d", strtotime($row['sent']));
			
		if ($status == 1) {

			$myContracts .= "
			<tr>
			<td class='left'>$name</td>
			<td class='centered'>$sent</td>
			</tr>";
			
		} else if ($status == 2) {

			$myContracts .= "
			<tr>
			<td class='left'><a href='see-contract.php?contractid=6&user_id=$user_id'>$name</a></td>
			<td class='centered'><img src='images/check.png' width='12' /></td>
			</tr>";
			
		}

	}
	
	
	
?>
    <div class='infobox'>
     <h4>CONTRACTS</h4>
     

     <table>
      <tr>
       <td colspan='2'><a href='sent-contracts.php?user_id=<?php echo $user_id; ?>' style='color: #00a48c;'>Send contract</a></td>
      </tr>
	  <?php echo $myContracts ?>
     </table>
       
    </div>
<br />
<?php } ?>
<br /><center>
    <?php	
	if ($file1_exist) {
		
		// echo "<a href='images/_$domain/ID/" . $user_id . "-front." . $dniext1 . "' class='oklink'><img src='images/dni-iconbig.png' style='margin-top: 10px;'/></a> <a href='new-id-scan-front.php?user_id=$user_id'><img src='images/edit-dnibig.png' style='display: inline-block; margin-left: 5px; margin-bottom: 2px;' /></a> <br />";
		
		echo "<div style='display: inline-block;'><a href='{$google_root}images/_$domain/ID/" . $user_id . "-front." . $dniext1 . "' class='oklink'><img src='images/check.png' style='margin-bottom: -5px;' /> &nbsp;<img src='images/dni-front-ok.png' height='22' style='margin-bottom: -5px;' /> &nbsp; {$lang['front']}</a><br />
		<a href='new-id-scan-front.php?user_id=$user_id'><img src='images/new-edit-dni.png' style='display: inline-block; margin-left: -15px; margin-top: 8px;' /></a>
		<a href='uTil/delete-dni-front.php?user_id=$user_id'><img src='images/delete.png' width='18' style='display: inline-block; margin-left: 10px;' /></a></div>";
		
	} else {
		
		echo "<div style='display: inline-block; vertical-align: top;'><a href='new-id-scan-front.php?user_id=$user_id' class='errorlink'><img src='images/warning-new.png' style='margin-bottom: -5px;' /> &nbsp;<img src='images/dni-front.png' height='22' style='margin-bottom: -5px;' /> &nbsp; {$lang['front']}</a></div>";
		
	}
			
	if ($file2_exist) {
		echo "<div style='display: inline-block;'><a href='{$google_root}images/_$domain/ID/" . $user_id . "-back." . $dniext2 . "' class='oklink'><img src='images/check.png' style='margin-bottom: -5px;' /> &nbsp;<img src='images/dni-back-ok.png' height='22' style='margin-bottom: -5px;' /> &nbsp; {$lang['back']}</a><br />
		<a href='new-id-scan-back.php?user_id=$user_id'><img src='images/new-edit-dni.png' style='display: inline-block; margin-left: -15px; margin-top: 8px;' /></a>
		<a href='uTil/delete-dni-back.php?user_id=$user_id'><img src='images/delete.png' width='18' style='display: inline-block; margin-left: 10px;' /></a></div>";
	} else {
		
		echo "<div style='display: inline-block; vertical-align: top;'><a href='new-id-scan-back.php?user_id=$user_id' class='errorlink'><img src='images/warning-new.png' style='margin-bottom: -5px;' /> &nbsp;<img src='images/dni-back.png' height='22' style='margin-bottom: -5px;' /> &nbsp; {$lang['back']}</a></div>";
		
	}
		
	if ($file3_exist) {
		echo "<div style='display: inline-block;'><a href='{$google_root}images/_$domain/sigs/" . $user_id . ".$sigext' class='oklink'><img src='images/check.png' style='margin-bottom: -5px;' /> &nbsp;<img src='images/sig-ok.png' height='22' style='margin-bottom: -5px;' /> &nbsp; {$lang['signature']}</a><br />
		<a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/new-edit-dni.png' style='display: inline-block; margin-left: -15px; margin-top: 8px;' /></a>
		<a href='uTil/delete-sig.php?user_id=$user_id'><img src='images/delete.png' width='18' style='display: inline-block; margin-left: 10px;' /></a></div>";
	} else {
		echo "<div style='display: inline-block; vertical-align: top;'><a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType' class='errorlink'><img src='images/warning-new.png' style='margin-bottom: -5px;' /> &nbsp;<img src='images/sig.png' height='22' style='margin-bottom: -5px;' /> &nbsp; {$lang['signature']}</a><br /></div>";
	}
?>
</center>
   </div>
  </div>
 </div>
 <br /><br />
 <div class='clearFloat'></div>
   <div id="mainbox">
   <div class='mainboxheader'>
    <img src="images/consumo.png" style='margin-bottom: -8px; margin-right: 5px;' /> <?php echo $lang['consumption-info']; ?>
   </div>
   <div class='mainboxcontent'>
    <div class='infobox'>
     <h4><?php echo $lang['member-preferences']; ?></h4>
     <table class='memberStats v2'>
      <tr>
       <td class='left'><?php if ($favouriteCategory != '') { echo $lang['global-category']; ?>: <?php echo " Flor (" . number_format($percentage,0) . "%)"; } ?></td></tr>
       <tr><td class='left'><?php if ($favourite1 != '') { ?> #1: <?php echo $favourite1 . " (" . number_format($quantity1,0) . " g)"; ?></td></tr> <?php } ?>
       <tr><td class='left'><?php if ($favourite2 != '') { ?> #2: <?php echo $favourite2 . " (" . number_format($quantity2,0) . " g)"; ?></td></tr> <?php } ?>
       <tr><td class='left'><?php if ($favourite3 != '') { ?>#3: <?php echo $favourite3 . " (" . number_format($quantity3,0) . " g)"; ?></td></tr> <?php } ?>
       <tr><td class='left'><?php if ($favourite4 != '') { ?>#4: <?php echo $favourite4 . " (" . number_format($quantity4,0) . " g)"; ?></td></tr> <?php } ?>
       <tr><td class='left'><?php if ($favourite5 != '') { ?>#5: <?php echo $favourite5 . " (" . number_format($quantity5,0) . " g)"; ?></td></tr> <?php } ?>
</table>
       
    </div>
    <div class='infobox'>
     <h4><?php echo $lang['member-weeklyavgs']; ?></h4>
     <table style='width: 100%'>
      <tr>
       <td><span class='maintext'><?php echo $lang['global-dispenses']; ?>:</span> <?php echo number_format($totalDispensesPerWeek,0); ?></td>
       <td style='text-align: right;'><span class='maintext'><?php echo $lang['member-spenditure']; ?>:</span> <?php echo number_format($totalAmountPerWeek,0); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
      </tr>
     </table>
     <br /><br />
     <h4><?php echo $lang['profile-discount']; ?></h4>
     <table style='width: 100%'>
      <tr>
       <td><span class='maintext'><?php echo $lang['member-discountD']; ?>:</span> <?php echo $discount; ?>%</td>
       <td style='text-align: right;'><span class='maintext'><?php echo $lang['member-discountBar']; ?>:</span> <?php echo $discountBar; ?>%</td>
      </tr>
     </table>
       
    </div>
   </div>
   </div>


 <br /><br />
 <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a><br /><br />
 	 <table class="default" id='mainTable' style='width: 1144px;'>
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
	    <th></th>
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
	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, 'sale' as Type, '' AS donatedTo FROM sales WHERE userid = $user_id UNION ALL SELECT saleid, saletime, userid, amount, '' AS amountpaid, '' AS quantity, unitsTot AS units, adminComment, creditBefore, creditAfter, 'bar' as Type, '' AS donatedTo FROM b_sales WHERE userid = $user_id UNION ALL SELECT donationid, donationTime as saletime, userid, amount, '' AS amountpaid, '' AS quantity, '' AS units, comment AS adminComment, creditBefore, creditAfter, 'donation' as Type, donatedTo AS donatedTo FROM donations WHERE userid = $user_id UNION ALL SELECT paymentid, paymentdate as saletime, userid, amountPaid AS amount, '' AS amountpaid, '' AS quantity, '' AS units, comment AS adminComment, creditBefore, creditAfter, 'memberpayment' as Type, paidTo AS donatedTo FROM memberpayments WHERE userid = $user_id ORDER by saletime DESC LIMIT $offset, $no_of_records_per_page";
	
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
		$adminComment = $sale['adminComment'];
		
	if ($adminComment != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>$adminComment</div>
		                <script>
		                  	$('#comment$saleid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$saleid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$saleid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
		
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
  	   <td class='clickableRow green' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow green' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['changed-credit']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'>{$credit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'><span class='relativeitem'>{$commentRead}</span></td>
		";

// Separate methodologies and row displays (linkage) for donations vs sales. Gift credit first
		} else if ($type == 'donation' && $donatedTo == 5) {
			echo "
  	   <td class='clickableRow green' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow green' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['gift-credit']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'>{$credit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'><span class='relativeitem'>{$commentRead}</span></td>

		";
				
				
// Separate methodologies and row displays (linkage) for donations vs sales. Donations next:
		} else if ($type == 'donation') {
			echo "
  	   <td class='clickableRow green' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow green' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['donation-donation']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'>{$credit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>
		<td class='clickableRow right green' href='donation-management.php?userid={$userid}'><span class='relativeitem'>{$commentRead}</span></td>
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
  	   <td class='clickableRow' href='pay-membership.php?user_id=$user_id'>{$formattedDate}</td>
  	   <td class='clickableRow' colspan='6' href='pay-membership.php?user_id=$user_id'>{$lang['membership-payments']}: $paidTo</td>
		<td class='clickableRow right' href='pay-membership.php?user_id=$user_id'><strong>{$amount} <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></strong></td>
		<td class='clickableRow right' href='pay-membership.php?user_id=$user_id'>$credit</td>
		<td class='clickableRow right' href='pay-membership.php?user_id=$user_id'>$newcredit</td>
		<td class='clickableRow right' href='pay-membership.php?user_id=$user_id'><span class='relativeitem'>{$commentRead}</span></td>";
				
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
		<td class='clickableRow right' href='dispense.php?userid={$saleid}'><span class='relativeitem'>{$commentRead}</span></td>
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
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><span class='relativeitem'>{$commentRead}</span></td>
		";
		
	}
	
}

echo "</table></center>";
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
<script type="text/javascript">
	// bantext
	$(".usergrouptextbanned").hover(function(){
		$("#bantext").show();
	});	
	$(".usergrouptextbanned").click(function(){
		$("#bantext").toggle();
	});
	$("#closeban").click(function(){
		$("#bantext").hide();
	});
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
	
