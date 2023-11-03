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

	pageStart("CCS", NULL, $testinput, "pindex", "notSelected", $lang['pre-order'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$query = "SELECT services, minorder, clubname, clubemail, clubphone, openinghour, closinghour, day1, day2, day3, day4, day5, day6, day7, multiple, minutes, hours, sameday, timeslots, deliveries, deliverycharge, deliverychargepct, nostocknodispense FROM systemsettings";
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
		$servicesInt = $row['services'];
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
		$timeslots = $row['timeslots'];
		$deliveries = $row['deliveries'];
		$deliverycharge = $row['deliverycharge'];
		$deliverychargepct = $row['deliverychargepct'];
		$nostocknodispense = $row['nostocknodispense'];
		
		if ($services == 0) {
			$active = "<strong style='color: red;'>{$lang['inactive']}</strong>";
			$noservice = "NO";
		} else {
			$active = "<strong style='color: #00ce1d;'>{$lang['active']}</strong>";
			$noservice = "YES";
		}
		
		if ($day1 == 1) {
			$days .= substr($lang['monday'], 0, 3);
		}
		if ($day2 == 1) {
			$days .= ", " . substr($lang['tuesday'], 0, 3);
		}
		if ($day3 == 1) {
			$days .= ", " . substr($lang['wednesday'], 0, 3);
		}
		if ($day4 == 1) {
			$days .= ", " . substr($lang['thursday'], 0, 3);
		}
		if ($day5 == 1) {
			$days .= ", " . substr($lang['friday'], 0, 3);
		}
		if ($day6 == 1) {
			$days .= ", " . substr($lang['saturday'], 0, 3);
		}
		if ($day7 == 1) {
			$days .= ", " . substr($lang['sunday'], 0, 3);
		}
		if ($multiple == 0) {
			$multiple = "No";
		} else {
			$multiple = "Yes";
		}
		if ($sameday == 0) {
			$sameday = "No";
		} else {
			$sameday = "Yes";
		}
		if ($timeslots == 0) {
			$timeslots = "No";
		} else {
			$timeslots = "Yes";
		}
		if ($nostocknodispense == 0) {
			$nostocknodispense = "Yes";
		} else {
			$nostocknodispense = "No";
		}
		
		if ($services == 1) {
			$services = $lang['collection'];
		} else if ($services == 10) {
			$services = $lang['delivery'];
		} else if ($services == 11) {
			$services = $lang['collection'] . " + " . $lang['delivery'];
		} else if ($services == 0) {
			$services = "<strong style='color: red;'>{$lang['none']}</strong>";
		}

		if ($deliverychargepct == 1) {
			$deliverycharge = $deliverycharge . "%";
		} else if ($deliverycharge > 0) {
			$deliverycharge = $deliverycharge . "&euro;";
		}

?>

<center>
 <a href="pre-orders.php" class='cta1'><?php echo $lang['orders']; ?></a>
 <!--<a href="mass-invite.php" class='cta1'><?php echo $lang['invite']; ?></a>-->
<?php if ($_SESSION['userGroup'] == 1) { ?>
 <a href="invited.php" class='cta1'><?php if ($_SESSION['lang'] == 'es') { echo "Invitados"; } else { echo "Invited"; } ?></a>
 <a href="pre-order-1.php" class='cta1'><?php echo $lang['configure']; ?></a>
<?php if ($noservice != "NO") { ?>
 <a href="pre-order-deactivate.php" class='cta3' style='background-color: red;'><?php echo $lang['disable']; ?></a>
<?php } ?>
<?php } ?>
<br />
 <div class='actionbox-np2' style='height: 366px;'>
  <div id='mainboxheader'>
General
  </div>
  
   <div class='boxcontent'>
 <table class='default-plain noborder'>
  <tr>
   <td><?php echo $lang['status']; ?>:</td>
   <td><?php echo $active; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['services-active']; ?>:</td>
   <td><?php echo $services; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['club-name']; ?>:</td>
   <td><?php echo $clubname; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['club-email']; ?>:</td>
   <td><?php echo $clubemail; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['club-phone']; ?>:</td>
   <td><?php echo $clubphone; ?></td>
  </tr>
 </table>
  </div>
  </div>
  
  
 <div class='actionbox-np2'>
  <div id='mainboxheader'>
   Limitations
  </div>
  
   <div class='boxcontent'>
 <table class='default-plain noborder'>
  <tr>
   <td><?php echo $lang['min-order']; ?>:</td>
   <td><?php echo $minorder; ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['days-enabled']; ?>:</td>
   <td><?php echo $days; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['opening-hours']; ?>:</td>
   <td><?php echo $openinghour; ?> - <?php echo $closinghour; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['allow-multiple-bookings']; ?>:</td>
   <td><?php echo $multiple; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['minutes-between-appointments']; ?>:</td>
   <td><?php echo $minutes; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['allow-same-day-orders']; ?>:</td>
   <td><?php echo $sameday; ?></td>
  </tr>		
<?php if ($hours > 0) { ?>
  <tr>
   <td colspan="2"><?php echo $lang['orders-must-be-placed']; ?> <strong><?php echo $hours; ?></strong> <?php echo $lang['hours-in-advance']; ?>.</td>
  </tr>
<?php } ?>
<?php if ($servicesInt == 10 || $servicesInt == 11) { ?>
  <tr>
   <td><?php echo $lang['enable-timeslots'] . " ({$lang['delivery']})"; ?>:</td>
   <td><?php echo $timeslots; ?></td>
  </tr>		
  <tr>
   <td><?php echo $lang['deliveries-per-day']; ?>:</td>
   <td><?php echo $deliveries; ?></td>
  </tr>		
  <tr>
   <td><?php echo $lang['allow-dispense-if-no-stock']; ?>:</td>
   <td><?php echo $deliverycharge; ?></td>
  </tr>		
<?php } ?>
  <tr>
   <td><?php echo $lang['allow-dispense-if-no-stock']; ?>:</td>
   <td><?php echo $nostocknodispense; ?></td>
  </tr>		
 </table>
</center>


