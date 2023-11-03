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
	
	$inMenu = $_GET['menu'];
	$saleid = $_GET['saleid'];
	$domain = $_SESSION['domain'];
	
	if ($inMenu == 'yes') {
		
		$changeMenu = "UPDATE sales SET fulfilled = 0 WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$changeMenu")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		header("Location: pre-orders.php");
		exit();
		
	}
	
	if ($_GET['accepted'] == 'true') {
		
		$changeMenu = "UPDATE sales SET fulfilled = 1 WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$changeMenu")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$query = "SELECT clubname, clubemail, clubphone FROM systemsettings";
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
			$clubname = $row['clubname'];
			$clubemail = $row['clubemail'];
			$clubphone = $row['clubphone'];

		$selectSale = "SELECT userid FROM sales WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$selectSale");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$userid = $row['userid'];
		
		$userLookup = "SELECT first_name, email FROM users WHERE user_id = {$userid}";
		
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
			$email = $row['email'];
			
		$query = "SELECT time FROM delivery WHERE saleid = $saleid";
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
			$formattedDate = date("d/m/Y", strtotime($row['time']));
			$formattedTime = date("H:i", strtotime($row['time']));
			
		if ($_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'thebulldog' || $_SESSION['domain'] == 'ccstest') {
			
			$maskWarning = "<br /><br /><strong>Importante:</strong> Cuando vayas a recoger, ven solo y usa mascarilla!";
			
		}

			pageStart("CCS", NULL, $timePicker, "pprofile", NULL, "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	if (isv6()) {
		
		$redirect = "https://ccsnubev2.com/v6/";
		
	} else {
		
		$redirect = "https://ccsnubev2.com/";
		
	}
	
	$body = "Hola $first_name,<br /><br />Esto es para informarte que el $clubname ha aceptado tu cita el $formattedDate a las $formattedTime.$maskWarning<br /><br />¡Hasta pronto!<br /><br />Saludos,<br />$clubname";

?>
<center>
<strong>Please wait - sending e-mail.<br />Por favor espera - mandando e-mail.</strong><br /><br />
<img src="images/spinner.gif" />
</center>
<div style='visibility: hidden;'>
<form id="registerForm" action="https://nefosolutions.com/smtptest.php?accept" method="POST">

<center>
<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
<input type="hidden" name="clubname" value="<?php echo $clubname; ?>" />
<input type="hidden" name="clubemail" value="<?php echo $clubemail; ?>" />
Name: <input type="text" name="name" value="<?php echo $first_name . " " . $last_name; ?>" class='tenDigit defaultinput' placeholder="" /><br />
E-mail: <input type="text" name="email" value="<?php echo $email; ?>" class='tenDigit defaultinput' placeholder="" /><br />
Subject: <input type="text" name="subject" value="Cita confirmada" class='tenDigit defaultinput' placeholder="" /><br />
Body: <textarea name="body" style='height: 300px; width: 500px;'><?php echo $body; ?></textarea><br />


</form>
</div>

<script>
$(document).ready(function(){
     $("#registerForm").submit();
});
</script>
<?php

	exit();
		
	}
	
	if ($_GET['timechange'] == 'true') {
		
	$query = "SELECT services, minorder, clubname, clubemail, clubphone, openinghour, closinghour, day1, day2, day3, day4, day5, day6, day7, multiple, minutes, hours, sameday FROM systemsettings";
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
		$email = $row['email'];
		$minorder = $row['minorder'];
		$clubname = $row['clubname'];
		$clubemail = $row['clubemail'];
		$clubphone = $row['clubphone'];
		$openinghour = $row['openinghour'];
		$closinghour = $row['closinghour'];
		$day1 = $row['day1'];
		$day2 = $row['day2'];
		$day3 = $row['day3'];
		$day4 = $row['day4'];
		$day5 = $row['day5'];
		$day6 = $row['day6'];
		$day7 = $row['day7'];
		$multiple = $row['multiple'];
		$minutes = $row['minutes'];
		$hours = $row['hours'];
		$sameday = $row['sameday'];
		
		// Initialise JS variables
		
		// Don't allow orders today if closinghour has passed - or if min. hours ($hours) has been set!
		$timeNow = date("H:i");
		$hourNow = date("H");
		
		$cutoff = $hourNow + $hours; // 16 + 3 = 19
		
		if ($sameday == 0 || (strtotime($timeNow) >= strtotime($closinghour)) || $cutoff > $closinghour) {
			
			$nextDay = "var nowdate = new Date();
		  				nowdate.setDate(nowdate.getDate() + 1);";
		} else {
			
			$nextDay = "var nowdate = new Date()";
			
		}
		
		if ($day1 == 1) {
			$dayLimit .= "date.getDay() == 1 || ";
		}
		if ($day2 == 1) {
			$dayLimit .= "date.getDay() == 2 || ";
		}
		if ($day3 == 1) {
			$dayLimit .= "date.getDay() == 3 || ";
		}
		if ($day4 == 1) {
			$dayLimit .= "date.getDay() == 4 || ";
		}
		if ($day5 == 1) {
			$dayLimit .= "date.getDay() == 5 || ";
		}
		if ($day6 == 1) {
			$dayLimit .= "date.getDay() == 6 || ";
		}
		if ($day7 == 1) {
			$dayLimit .= "date.getDay() == 0 || ";
		}
		
		$dayLimit = substr($dayLimit, 0, -4);
	


$validationScript = <<<EOD
	
$(document).ready(function (){
	
		$nextDay
		  
		$( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy",
			firstDay: 1,
  			minDate: nowdate,
  			beforeShowDay: function(date) {
        return [$dayLimit] ;
    },
  			onSelect: function () {
            window.location.replace('?timechange2=true&saleid=$saleid&date=' + this.value);
        	}
	    });
});	  
EOD;

	pageStart($lang['choosedate'], NULL, $validationScript, "pprofile", $lang['choosedate'], "", $_SESSION['successMessage'], $_SESSION['errorMessage']);


echo <<<EOD
   <center>
   <div id='sectionText'>
    <p>
     <h1>{$lang['pick-date']}</h1>
    </p>
   </div>
   
   <br /><br />
   
   <div id="datepicker"></div>
</center>
EOD;

exit();
	}
	
	if ($_GET['timechange2'] == 'true') {
		
	$date = $_GET['date'];
	
	$dateDB = date('Y-m-d', strtotime($date));
	
	$_SESSION['order-date'] = $date;
	$_SESSION['order-dateDB'] = $dateDB;
	
	$domain = $_SESSION['domain'];
	
	getSettings();		
	$query = "SELECT services, minorder, clubname, clubemail, clubphone, openinghour, closinghour, day1, day2, day3, day4, day5, day6, day7, multiple, minutes, hours, sameday FROM systemsettings";
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
		$email = $row['email'];
		$minorder = $row['minorder'];
		$clubname = $row['clubname'];
		$clubemail = $row['clubemail'];
		$clubphone = $row['clubphone'];
		$openinghour = $row['openinghour'];
		$closinghour = $row['closinghour'];
		$day1 = $row['day1'];
		$day2 = $row['day2'];
		$day3 = $row['day3'];
		$day4 = $row['day4'];
		$day5 = $row['day5'];
		$day6 = $row['day6'];
		$day7 = $row['day7'];
		$multiple = $row['multiple'];
		$minutes = $row['minutes'];
		$hours = $row['hours'];
		$sameday = $row['sameday'];
		
		$openingminute = substr($openinghour, -2);
		$openinghour = substr($openinghour, 0, 2);
		$closingminute = substr($closinghour, -2);
		$closinghour = substr($closinghour, 0, 2);
		// Remember: Opening hours that are not 'whole', e.g. 12:30 - 20:15
		
		/*
			1. Today: No orders BEFORE current time
			2. X hours before
			3. Opening hours
			4. Multiple citas
			5. Minutes between citas
		*/
		
		// 1. Today: No orders BEFORE current time - nor after closingtime!
		$dateNow = date("d-m-Y");
		$hourNow = date("H");
		$minuteNow = date("i");
		$now = new DateTime($dateNow);
		$selectedDate = new DateTime($date);
		$daysDifference = $selectedDate->diff($now)->format("%a");
		
		if ($daysDifference == 0) {
			
			$blockedSlot = "(hour <= $hourNow && minute < $minuteNow) || (hour == $closinghour && minute > $closingminute) || (hour == ($hourNow + $hours) && minute < $minuteNow)";
			$blockedHours = "(hour < ($hourNow + $hours) || hour > $closinghour)";
			$blockSlots1 = ", onMinuteShow: OnMinuteShowCallback";
			$blockSlots2 = <<<EOD
function OnMinuteShowCallback(hour, minute) {
	if ($blockedSlot) {
		return false; // invalid
	} else {
    	return true;  // valid
	}
}
EOD;
			$today = 'true';
			
		// Not today
		} else {
		
			// 2. Min. X hours ($hours) from now
			if ($hours > 0) {
				
				$hourTomorrow = date("H", strtotime(date() . "+$hours hour"));
				
				if ($daysDifference == 1 && ($hourTomorrow > $openinghour)) {
					
					$openinghour = $hourTomorrow;
					$blockedSlot = "(hour == $hourTomorrow && minute < $minuteNow)";
					$blockSlots2 = <<<EOD
function OnMinuteShowCallback(hour, minute) {
	if ($blockedSlot) {
		return false; // invalid
	} else {
    	return true;  // valid
	}
}
EOD;
					$blockSlots1 = ", onMinuteShow: OnMinuteShowCallback";
					
					$active24 = 'true';
					
				}

			}
			
			// 3. Opening and closing hours
			$blockedHours = "(hour > $closinghour) || (hour < $openinghour)";
			
			if ($active24 != 'true') {
				$blockedSlot = "(hour == $openinghour && minute < $openingminute)";
				$blockSlots1 = ", onMinuteShow: OnMinuteShowCallback";
				$blockSlots2 = <<<EOD
function OnMinuteShowCallback(hour, minute) {
	if ($blockedSlot) {
		return false; // invalid
	} else {
    	return true;  // valid
	}
}
EOD;

			}
				
				$blockedSlot .= " || (hour == $closinghour && minute > $closingminute)";
				$blockSlots2 = <<<EOD
function OnMinuteShowCallback(hour, minute) {
	if ($blockedSlot) {
		return false; // invalid
	} else {
    	return true;  // valid
	}
}
EOD;
				

		}
		
		// 4. Don't allow double-bookings
		if ($multiple == 0) {
			$query = "SELECT HOUR(d.time), MINUTE(d.time) FROM delivery d, sales s WHERE d.saleid = s.saleid AND DATE(d.time) = DATE('$dateDB')";
			try
			{
				$resultBlock = $pdo3->prepare("$query");
				$resultBlock->execute();
				$data = $resultBlock->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			if ($data) {
								
					$blockedSlot = $blockedSlot . " || ";
				
				foreach ($data as $row) {
		
					$blockedHour = $row['HOUR(d.time)'];
					$blockedMinute = $row['MINUTE(d.time)'];
		
					$blockedSlot .= "(hour == $blockedHour && minute == $blockedMinute) || ";
		
				}
				
				// Test tomorrow with and without double bookcing
				// Test Sunday with and without double bookcing
				
				
				$blockedSlot = substr($blockedSlot, 0, -4);
				
				$blockSlots1 = ", onMinuteShow: OnMinuteShowCallback";
				$blockSlots2 = <<<EOD
	function OnMinuteShowCallback(hour, minute) {
		if ($blockedSlot) {
			return false; // invalid
		} else {
	    	return true;  // valid
		}
	}
EOD;

			}

		}
			
		$timePicker = <<<EOD
						
							$(document).ready(function() {
								    
								$('.timepicker').timepicker({
								    showPeriodLabels: false,
									altField: '#timeFull',
									minutes: {
							      	  interval: $minutes
							    	},
								    hourText: 'Hour',
								    minuteText: 'Min',
								    defaultTime: '',
								 	onHourShow: OnHourShowCallback
								 	$blockSlots1
								});
								
function OnHourShowCallback(hour) {
    if (($blockedHours)) {
        return false; // not valid
    }
    return true; // valid
}
$blockSlots2

EOD;
		$timePicker .= <<<EOD
		
		
	  $('#timeForm').validate({
		  ignore: [],
		  rules: {	  
			  timeFull: {
				  required: true
			  }
    	}, // end rules
    	  messages: {
	    	  timeFull: "{$lang['choose-time']}!<br />"
    	},
		  errorPlacement: function(error, element) {
			if (element.is("#timeFull")){
				 error.appendTo("#errorBox");
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate

							});
EOD;

	pageStart("CCS", NULL, $timePicker, "pprofile", NULL, "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

						echo <<<EOD
						
   <center>
   
<br />
   <form id="timeForm" action="?signin&saleid=$saleid&date=$date" method="POST">
<input type='hidden' id="timeFull" name="timeFull" />
<input type='hidden' id="user_id" name="user_id" value="$user_id" />
<br />
 <div class="timepicker" style="font-size: 10px; margin-left: 24px;"></div><br />
 <span id="errorBox"></span><br />
      <button name='oneClick' class="cta1" type="submit">Confirm</button>
 </form>
</center>
						
EOD;
		exit();
		
	}
	
	if (isset($_GET['signin'])) {
		
		$hour = substr($_POST['timeFull'], 0, 2);
		$minute = substr($_POST['timeFull'], 3, 2);
		$date = date("Y-m-d", strtotime($_GET['date']));

		$newTime = "$date $hour:$minute:00";
		
		$query = "SELECT time FROM delivery WHERE saleid = $saleid";
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
			$formattedDate = date("d/m/Y", strtotime($row['time']));
			$formattedTime = date("H:i", strtotime($row['time']));
		
		$query = "UPDATE delivery SET time = '$newTime' WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$changeMenu = "UPDATE sales SET fulfilled = 1 WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$changeMenu")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$query = "SELECT clubname, clubemail, clubphone FROM systemsettings";
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
			$clubname = $row['clubname'];
			$clubemail = $row['clubemail'];
			$clubphone = $row['clubphone'];

		$selectSale = "SELECT userid FROM sales WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$selectSale");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$userid = $row['userid'];
		
		$userLookup = "SELECT first_name, email FROM users WHERE user_id = {$userid}";
		
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
			$email = $row['email'];
			
		$query = "SELECT time FROM delivery WHERE saleid = $saleid";
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
			$formattedDate2 = date("d/m/Y", strtotime($row['time']));
			$formattedTime2 = date("H:i", strtotime($row['time']));
			
		if ($_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'thebulldog' || $_SESSION['domain'] == 'ccstest') {
			
						$maskWarning = "<br /><br /><strong>Importante:</strong> Cuando vayas a recoger, ven solo y usa mascarilla!";
			
		}



	pageStart("CCS", NULL, $timePicker, "pprofile", NULL, "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	if (isv6()) {
		
		$redirect = "https://ccsnubev2.com/v6/";
		
	} else {
		
		$redirect = "https://ccsnubev2.com/";
		
	}
	
	$body = "Hola $first_name,<br /><br />Lamentablemente no estamos disponibles para tu cita el $formattedDate a las $formattedTime.<br />Te facilitamos nueva cita para $formattedDate2 a las $formattedTime2.$maskWarning<br /><br />¡Hasta pronto!<br /><br />Saludos,<br />$clubname";

?>
<center>
<strong>Please wait - sending e-mail.<br />Por favor espera - mandando e-mail.</strong><br /><br />
<img src="images/spinner.gif" />
</center>
<div style='visibility: hidden;'>
<form id="registerForm" action="https://nefosolutions.com/smtptest.php?accept" method="POST">

<center>
<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
<input type="hidden" name="clubname" value="<?php echo $clubname; ?>" />
<input type="hidden" name="clubemail" value="<?php echo $clubemail; ?>" />
Name: <input type="text" name="name" value="<?php echo $first_name . " " . $last_name; ?>" class='tenDigit defaultinput' placeholder="" /><br />
E-mail: <input type="text" name="email" value="<?php echo $email; ?>" class='tenDigit defaultinput' placeholder="" /><br />
Subject: <input type="text" name="subject" value="Cita cambiada" class='tenDigit defaultinput' placeholder="" /><br />
Body: <textarea name="body" style='height: 300px; width: 500px;'><?php echo $body; ?></textarea><br />


</form>
</div>

<script>
$(document).ready(function(){
     $("#registerForm").submit();
});
</script>
<?php

	exit();

		
	}



	$query = "SELECT time FROM delivery WHERE saleid = $saleid";
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
		$formattedDate = date("d M H:i", strtotime($row['time']));
		
	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur, puesto FROM sales WHERE saleid = $saleid";

	try
	{
		$result = $pdo3->prepare("$selectSale");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	
	
	
	pageStart($lang['title-dispense'], NULL, NULL, "psales", "Sale", $lang['global-dispense'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>



	  
<?php

	$sale = $result->fetch();

		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$discount = $sale['discount'];
		$direct = $sale['direct'];
		$discounteur = $sale['discounteur'];
		$puesto = $sale['puesto'];
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno, userGroup, photoExt, email, telephone FROM users WHERE user_id = {$userid}";
		
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
		$photoExt = $row['photoExt'];
		$userGroup = $row['userGroup'];
		$emailUser = $row['email'];
		$phoneUser = $row['telephone'];
				
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
			   
		echo "<center>";
  
  
	$topimg = $google_root."images/_$domain/members/$userid.$photoExt";

	$object_exist = object_exist($google_bucket, $google_root_folder."images/_$domain/members/$userid.$photoExt")
	
	if ($object_exist === false) {
		$topimg = $google_root.'images/silhouette-new.png';
	}
	
		$query = "SELECT groupName FROM usergroups WHERE userGroup = $userGroup";
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
			$userGroupName = $row['groupName'];
			
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$userGroupName</span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$userGroupName</span>";
		
	}
	
		$selectSale = "SELECT street, streetnumber, flat, postcode, city, telephone, time FROM delivery WHERE saleid = $saleid";
		try
		{
			$resultw = $pdo3->prepare("$selectSale");
			$resultw->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$roww = $resultw->fetch();
			$street = $roww['street'];
			$streetnumber = $roww['streetnumber'];
			$flat = $roww['flat'];
			$postcode = $roww['postcode'];
			$city = $roww['city'];
			$telephone = $roww['telephone'];
			$time = date("d-m-Y H:i", strtotime($roww['time']));
			
			
		if ($puesto == '11') {
			if ($_SESSION['lang'] == 'es') {
				$saletype = "Entrega";
			} else {
				$saletype = "Delivery";
			}
 	 	 	 	 	 	
			
  	echo <<<EOD
<center><div class='topaval' style='background-color: white; margin-top: 0;'>
  <center> <span class="profilepicholder" style="float: left; margin-right: 15px;" ><img class="profilepic" src="$topimg" width="143" />$highroller</span>


 <table style="display: inline-block; vertical-align: top; text-align: left;">
  <tr>
   <td class='biggerfont'><a href='profile.php?user_id=$userid'><span class='firsttext'>#$memberno</span><br />
   <span class='nametext'>$first_name $last_name</span></a><br /> $groupName<br /><strong>$formattedDate</strong></td>
  </tr>
  <tr>
   <td><strong></td>
  </tr>
 </table><br /><br /><br />
<strong style='font-size: 18px; color: #09a38b;'>$saletype<br /><br /></strong>
 <strong>{$lang['address']}:</strong><br />$street $streetnumber $flat<br />
$postcode $city<br /><br />
<strong>{$lang['member-contactdetails']}:</strong><br />$telephone<br />
$emailUser<br /><br />

 </center>
</div></center><br />
EOD;
		
		
		} else if ($puesto == '22') {
			if ($_SESSION['lang'] == 'es') {
				$saletype = "Recogida";
			} else {
				$saletype = "Collection";
			}
			
  	echo <<<EOD
<center><div class='topaval' style='background-color: white; margin-top: 0;'>
  <center> <span class="profilepicholder" style="float: left; margin-right: 15px;" ><img class="profilepic" src="$topimg" width="143" />$highroller</span>


 <table style="display: inline-block; vertical-align: top; text-align: left;">
  <tr>
   <td class='biggerfont'><a href='profile.php?user_id=$userid'><span class='firsttext'>#$memberno</span><br />
   <span class='nametext'>$first_name $last_name</span></a><br /> $groupName<br /><strong>$formattedDate</strong></td>
  </tr>
  <tr>
   <td><strong></td>
  </tr>
 </table><br /><br /><br />
<strong style='font-size: 18px; color: #09a38b;'>$saletype<br /><br /></strong>
<strong>{$lang['member-contactdetails']}:</strong><br />$phoneUser<br />
$emailUser<br /><br />
 </center>
</div></center><br />
EOD;

		} else {
			$saletype = "";
		}

		
		echo "
		

	 <table id='detailedsale' class='default'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>{$_SESSION['currencyoperator']}</th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Total {$_SESSION['currencyoperator']}</th>
	   </tr>
	  </thead>
	  <tbody>
<tr><td>";


		while ($onesale = $onesaleResult->fetch()) {
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
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
			echo $category . '<br />';
		}
		echo "</td><td>
";
//while loop goes here
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
	} else {
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
		echo "</td><td class='right'>";
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
			
				$rowC = $result->fetch();
					$category = $rowC['name'];
					$type = $rowC['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='right'>";
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		echo "</td><td class='right'>";
		echo number_format($quantity,2) . " g</td>";
		echo "</td><td class='right'>";
		echo number_format($units,2) . " u</td>";
		echo "<td class='right'>" . number_format($amount,2) . "<span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>";
		echo "</tr></table></span>
		


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 

 ";

		
	echo "<br /><center><a href='pre-orders.php' class='cta1'>&laquo; Pre-pedidos</a>";
	echo "<a href='?accepted=true&saleid=$saleid' class='cta1'>Aceptar</a>";
	echo "<a href='edit-dispense.php?saleid=$saleid&user_id=$userid' class='cta1'>Cambiar pedido</a>";
	echo "<a href='?timechange=true&saleid=$saleid' class='cta1'>Cambiar hora</a></center>";

		
		
	exit();
