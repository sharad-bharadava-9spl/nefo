<?php 
    
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '5';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	if ($_POST['step1'] == 'complete') {
		
		$email = $_POST['email'];
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION['errorMessage'] = "Invalid e-mail / E-mail incorrecto<br /><br />";
			header("Location: appointment-1.php");
			exit();
		}
			
		
		$_SESSION['name'] = $_POST['name'];
		$_SESSION['telephone'] = $_POST['telephone'];
		$_SESSION['email'] = $_POST['email'];
		
	} else {
		
		pageStart($lang['appointment-module'], NULL, $validationScript, "pindex", "notSelected", $lang['appointment-module'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
		
	}

	
	$validationScript = <<<EOD
    $(document).ready(function() {

	    
		$('.timepicker').timepicker({
		    showPeriodLabels: false,
			altField: '#timeFull',
			minutes: {
	      	  interval: 15
	    	},
		    hourText: 'Hour',
		    minuteText: 'Min',
		    defaultTime: ''
		});
	    
	  $('#registerForm').validate({
		  rules: {
			  services: {
				  required: true
			  },
			  minorder: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
	  
  }); // end ready
EOD;

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


	pageStart($lang['appointment-module'], NULL, $validationScript, "pindex", "notSelected", $lang['appointment-module'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
   <form id="registerForm" action="appointment-3.php" method="POST">
 <div id='mainbox'>
  <div id='mainboxheader'>
#2 - <?php echo $lang['order-settings']; ?>  </div>
  
   <div class='boxcontent'>

<br />
<input type="hidden" name="step2" value="complete" />

 <table class=''>
  <tr>
   <td><?php echo $lang['opening-hours']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td><input type="text" name="openinghourcita" id="openinghour" class='timepicker defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $openinghourcita; ?>" /> - &nbsp;&nbsp;&nbsp;<input type="text" name="closinghourcita" id="closinghour" class='timepicker defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $closinghourcita; ?>" /><br />&nbsp;</td>
  </tr>
  <tr>
   <td><?php echo $lang['days-enabled']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['monday']; ?>
	  <input type="checkbox" name="citaday1" value="1" <?php if ($citaday1 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['tuesday']; ?>
	  <input type="checkbox" name="citaday2" value="1" <?php if ($citaday2 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['wednesday']; ?>
	  <input type="checkbox" name="citaday3" value="1" <?php if ($citaday3 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['thursday']; ?>
	  <input type="checkbox" name="citaday4" value="1" <?php if ($citaday4 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['friday']; ?>
	  <input type="checkbox" name="citaday5" value="1" <?php if ($citaday5 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['saturday']; ?>
	  <input type="checkbox" name="citaday6" value="1" <?php if ($citaday6 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['sunday']; ?>
	  <input type="checkbox" name="citaday7" value="1" <?php if ($citaday7 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div><br />&nbsp;
   </td>
  </tr>
  <tr>
   <td style='position: relative;'><?php echo $lang['members-per-slot']; ?>:</td>
   <td><input type="number" name="citamultiple" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $citamultiple; ?>" /></td>
  </tr>
  <tr>
   <td><?php echo $lang['minutes-between-appointments']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td><input type="number" name="citaminutes" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $citaminutes; ?>" /><br />&nbsp;</td>
  </tr>
  <tr>
   <td><?php echo $lang['allow-same-day-appointments']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-yes']; ?>
	  <input type="radio" name="citasameday" value="1" <?php if ($citasameday == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-no']; ?>
	  <input type="radio" name="citasameday" value="0" <?php if ($citasameday == 0) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div><br />&nbsp;
   </td>
  </tr>
  <tr>
   <td><?php echo $lang['appointments-must-be-placed']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td><input type="number" name="citahours" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $citahours; ?>" /> <?php echo $lang['hours-in-advance']; ?><br />&nbsp;</td>
  </tr>
 </table> 
  </div>
  </div>

 <input type="submit" class='cta1' name='oneClick' type="submit" value="<?php echo $lang['global-save']; ?>">

</form>

