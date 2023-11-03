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

	pageStart($lang['appointment-module'], NULL, $testinput, "pindex", "notSelected", $lang['appointment-module'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$query = "SELECT clubname, clubemail, clubphone, openinghourcita, closinghourcita, citaday1, citaday2, citaday3, citaday4, citaday5, citaday6, citaday7, citamultiple, citaminutes, citahours, citasameday FROM systemsettings";
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
		$email = $row['email'];
		$clubname = $row['clubname'];
		$clubemail = $row['clubemail'];
		$clubphone = $row['clubphone'];
		$openinghourcita = $row['openinghourcita'];
		$closinghourcita = $row['closinghourcita'];
		$citaday1 = $row['citaday1'];
		$citaday2 = $row['citaday2'];
		$citaday3 = $row['citaday3'];
		$citaday4 = $row['citaday4'];
		$citaday5 = $row['citaday5'];
		$citaday6 = $row['citaday6'];
		$citaday7 = $row['citaday7'];
		$citamultiple = $row['citamultiple'];
		$citaminutes = $row['citaminutes'];
		$citahours = $row['citahours'];
		$citasameday = $row['citasameday'];
		
		
		if ($citaday1 == 1) {
			$days .= substr($lang['monday'], 0, 3);
		}
		if ($citaday2 == 1) {
			$days .= ", " . substr($lang['tuesday'], 0, 3);
		}
		if ($citaday3 == 1) {
			$days .= ", " . substr($lang['wednesday'], 0, 3);
		}
		if ($citaday4 == 1) {
			$days .= ", " . substr($lang['thursday'], 0, 3);
		}
		if ($citaday5 == 1) {
			$days .= ", " . substr($lang['friday'], 0, 3);
		}
		if ($citaday6 == 1) {
			$days .= ", " . substr($lang['saturday'], 0, 3);
		}
		if ($citaday7 == 1) {
			$days .= ", " . substr($lang['sunday'], 0, 3);
		}
		if ($citasameday == 0) {
			$citasameday = "No";
		} else {
			$citasameday = "Yes";
		}

?>

<center>
 <a href="appointments.php" class='cta1'><?php echo $lang['appointments']; ?></a>
<?php if ($_SESSION['userGroup'] == 1) { ?>
 <a href="appointment-invited.php" class='cta1'><?php if ($_SESSION['lang'] == 'es') { echo "Invitados"; } else { echo "Invited"; } ?></a>
 <a href="appointment-1.php" class='cta1'><?php echo $lang['configure']; ?></a>
<?php if ($noservice != "NO") { ?>
 <a href="appointments-deactivate.php" class='cta3' style='background-color: red;'><?php echo $lang['disable']; ?></a>
<?php } ?>
<?php } ?>
<br />
 <div class='actionbox-np2'>
  <div id='mainboxheader'>
Informaci&oacute;n
  </div>
  
   <div class='boxcontent'>
 <table class='default-plain noborder'>
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
  <tr>
   <td><?php echo $lang['days-enabled']; ?>:</td>
   <td><?php echo $days; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['opening-hours']; ?>:</td>
   <td><?php echo $openinghourcita; ?> - <?php echo $closinghourcita; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['members-per-slot']; ?>:</td>
   <td><?php echo $citamultiple; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['minutes-between-appointments']; ?>:</td>
   <td><?php echo $citaminutes; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['allow-same-day-appointments']; ?>:</td>
   <td><?php echo $citasameday; ?></td>
  </tr>		
<?php if ($hours > 0) { ?>
  <tr>
   <td colspan="2"><?php echo $lang['appointments-must-be-placed']; ?> <strong><?php echo $citahours; ?></strong> <?php echo $lang['hours-in-advance']; ?>.</td>
  </tr>
<?php } ?>
 </table>
</center>


