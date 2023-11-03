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
			header("Location: pre-order-1.php");
			exit();
		}
			
		
		$_SESSION['name'] = $_POST['name'];
		$_SESSION['telephone'] = $_POST['telephone'];
		$_SESSION['email'] = $_POST['email'];
		
	} else {
		
		pageStart("CCS", NULL, $validationScript, "pindex", "notSelected", $lang['pre-order'], $_SESSION['successMessage'], "ERROR");
		exit();
		
	}

	
	$validationScript = <<<EOD
    $(document).ready(function() {

$('#deliverycharge').bind('keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#deliverychargepct').val('');
  }
});

$('#deliverychargepct').bind('keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#deliverycharge').val('');
  }
});

	if (!$('#services2').is(':checked')) {
		$("#deliveries1").hide();
		$("#deliveries2").hide();				
		$("#deliveries3").hide();				
	}

	    	    
	    $('#services2').change(function(){
		    
			if (!$('#services2').is(':checked')) {
				
				$("#deliveries1").fadeOut('slow');
				$("#deliveries2").fadeOut('slow');	
				$("#deliveries3").fadeOut('slow');	
							
			} else {
				
				$("#deliveries1").fadeIn('slow');			
				$("#deliveries2").fadeIn('slow');			
				$("#deliveries3").fadeIn('slow');			
					
	    	}
	    });

	    
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
	  
  	$("#amb").on({
 		"mouseover" : function() {
		 	$("#ambhelp").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#ambhelp").css("display", "none");
	  	}
	});
	
  	$("#sloticon").on({
 		"mouseover" : function() {
		 	$("#slothelp").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#slothelp").css("display", "none");
	  	}
	});
	
  }); // end ready
EOD;

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
		$email = $row['email'];
		$minorder = $row['minorder'];
		$name = $row['clubname'];
		$email = $row['clubemail'];
		$telephone = $row['clubphone'];
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
		
		if ($deliverychargepct == 1) {
			$deliverychargepct = $deliverycharge;
			$deliverycharge = '';
		} else {
			$deliverychargepct = '';
		}


	pageStart("CCS", NULL, $validationScript, "pindex", "notSelected", $lang['pre-order'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
   <form id="registerForm" action="pre-order-3.php" method="POST">
 <div id='mainbox'>
  <div id='mainboxheader'>
#2 - <?php echo $lang['order-settings']; ?>  </div>
  
   <div class='boxcontent'>

<br />
<input type="hidden" name="step2" value="complete" />

 <table class=''>
  <tr>
   <td><?php echo $lang['services-active']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['collection']; ?>
	  <input type="checkbox" name="services1" id="services1" value="1" <?php if ($services == 1 || $services == 11) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['delivery']; ?>
	  <input type="checkbox" name="services2" id="services2" value="10" <?php if ($services == 10 || $services == 11) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div><br />&nbsp;
   </td>
  </tr>
  <tr id='deliveries1'>
   <td style='position: relative;'><img src='images/questionmark-new.png' height="14" id="sloticon" /> <?php echo $lang['enable-timeslots']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;<div id='slothelp' class='helpBox'><?php echo $lang['timeslots-info']; ?></div></td>
   <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-yes']; ?>
	  <input type="radio" name="timeslots" value="1" <?php if ($timeslots == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-no']; ?>
	  <input type="radio" name="timeslots" value="0" <?php if ($timeslots == 0) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div><br />&nbsp;
   </td>
  </tr>
  <tr id='deliveries2'>
   <td><?php echo $lang['deliveries-per-day']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td><input type="number" placeholder="" name="deliveries" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $deliveries; ?>" />
   </td>
  </tr>
  <tr id='deliveries3'>
   <td><?php echo $lang['delivery-charge']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td><input type="number" placeholder="" name="deliverycharge" id="deliverycharge" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $deliverycharge; ?>" /> <?php echo $_SESSION['currencyoperator']; ?>&nbsp;&nbsp;&nbsp;<?php echo $lang['or']; ?> &nbsp;&nbsp;<input type="number" placeholder="" name="deliverychargepct" id="deliverychargepct" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $deliverychargepct; ?>" /> %
   </td>
  </tr>
  <tr>
   <td><?php echo $lang['min-order']; ?>:</td>
   <td><input type="number" placeholder="" name="minorder" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $minorder; ?>" /> <?php echo $_SESSION['currencyoperator'] ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['opening-hours']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td><input type="text" name="openinghour" id="openinghour" class='timepicker defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $openinghour; ?>" /> - &nbsp;&nbsp;&nbsp;<input type="text" name="closinghour" id="closinghour" class='timepicker defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $closinghour; ?>" /><br />&nbsp;</td>
  </tr>
  <tr>
   <td><?php echo $lang['days-enabled']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['monday']; ?>
	  <input type="checkbox" name="day1" value="1" <?php if ($day1 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['tuesday']; ?>
	  <input type="checkbox" name="day2" value="1" <?php if ($day2 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['wednesday']; ?>
	  <input type="checkbox" name="day3" value="1" <?php if ($day3 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['thursday']; ?>
	  <input type="checkbox" name="day4" value="1" <?php if ($day4 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['friday']; ?>
	  <input type="checkbox" name="day5" value="1" <?php if ($day5 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['saturday']; ?>
	  <input type="checkbox" name="day6" value="1" <?php if ($day6 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['sunday']; ?>
	  <input type="checkbox" name="day7" value="1" <?php if ($day7 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div><br />&nbsp;<br />&nbsp;
   </td>
  </tr>
  <tr>
   <td style='position: relative;'><img src='images/questionmark-new.png' height="14" id="amb" /> <?php echo $lang['allow-multiple-bookings']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;<div id='ambhelp' class='helpBox'><?php echo $lang['amb-help']; ?></div></td>
   <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-yes']; ?>
	  <input type="radio" name="multiple" value="1" <?php if ($multiple == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-no']; ?>
	  <input type="radio" name="multiple" value="0" <?php if ($multiple == 0) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div><br />&nbsp;
   </td>
  </tr>
  <tr>
   <td><?php echo $lang['minutes-between-appointments']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td><input type="number" name="minutes" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $minutes; ?>" /><br />&nbsp;</td>
  </tr>
  <tr>
   <td><?php echo $lang['allow-same-day-orders']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-yes']; ?>
	  <input type="radio" name="sameday" value="1" <?php if ($sameday == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-no']; ?>
	  <input type="radio" name="sameday" value="0" <?php if ($sameday == 0) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
   </td>
  </tr>
  <tr>
   <td><?php echo $lang['orders-must-be-placed']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td><input type="number" name="hours" class='defaultinput twoDigit' style='margin-left: 0;' value="<?php echo $hours; ?>" /> <?php echo $lang['hours-in-advance']; ?><br />&nbsp;</td>
  </tr>
  <tr>
   <td><?php echo $lang['allow-dispense-if-no-stock']; ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-yes']; ?>
	  <input type="radio" name="nostocknodispense" value="0" <?php if ($nostocknodispense == 0) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-no']; ?>
	  <input type="radio" name="nostocknodispense" value="1" <?php if ($nostocknodispense == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
   <br />&nbsp;</td>
  </tr>
 </table> 
  </div>
  </div>

 <input type="submit" class='cta1' name='oneClick' type="submit" value="<?php echo $lang['global-save']; ?>">

</form>

