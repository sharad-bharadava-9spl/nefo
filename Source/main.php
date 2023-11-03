<?php 

	require_once 'cOnfig/connection.php';
	
	if ($_SESSION['puestosOrNot'] == 0) {
		require_once 'cOnfig/view-notitle.php';
	} else {
		require_once 'cOnfig/view.php';
	}
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	if ($_SESSION['iPadReaders'] > 0) {
		
		if ($_SESSION['scanner'] == '' || $_SESSION['scanner'] == 0) {
			header("Location: change-scanner.php");
			exit();
		}
		
		try
		{
			$result = $pdo3->prepare("DELETE FROM newscan WHERE type = {$_SESSION['scanner']}")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
	}
	
	// User comes from logging in @ index.php
	if (isset($_GET['login']) && $_SESSION['workertracking'] == 1) {
	
				$user_id = $_SESSION['user_id'];
				
				// Check if member is scanned in, if so show logout code
				$query = "SELECT user_id FROM logins WHERE DATE(time) = DATE(NOW() - INTERVAL 3 HOUR) AND type = 2 AND user_id = $user_id";
				try
				{
					$result = $pdo3->prepare("$query");
					$result->execute();
					$data = $result->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
				if (!$data) {
					
					// Check if they have an open shift
					$query = "SELECT user_id FROM logins WHERE DATE(time) = DATE(NOW() - INTERVAL 3 HOUR) AND type = 1 AND user_id = $user_id";
					try
					{
						$result2 = $pdo3->prepare("$query");
						$result2->execute();
						$data2 = $result2->fetchAll();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
						
					if ($data2) {
					
						header("Location: main.php");
					
					} else {
						
						// User has no open shifts today. Ask if he wants to start his shift
						pageStart($lang['working-hours'], NULL, $timePicker, "changeuser", NULL, $lang['working-hours'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
						$query = sprintf("SELECT first_name, last_name, user_id, memberno, email, userGroup, workStation, domain, photoExt FROM users WHERE user_id = '%d';",
						$user_id);
						try
						{
							$result = $pdo3->prepare("$query");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user1: ' . $e->getMessage();
								echo $error;
								exit();
						}
							
						$row = $result->fetch();
								$user_id = $row['user_id'];
								$username = $row['email'];
								$memberno = $row['memberno'];
								$userGroup = $row['userGroup'];
								$first_name = $row['first_name'];
								$last_name = $row['last_name'];
								$domain = $row['domain'];
								$photoExt = $row['photoExt'];
								$workStationAccess = $row['workStation'];
							
						echo "<center><div id='profilearea' style='font-size: 18px;'><img src='../images/_$domain/members/$user_id.$photoExt' class='salesPagePic' /><br /><h4>$first_name $last_name</h4><br />{$lang['start-shift-or-not']}?</div></center>";
						
						echo "<br /><center><a href='?signin&user_id=$user_id' class='cta'>{$lang['global-yes']}</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='main.php?user_id=$user_id' class='cta'>{$lang['global-no']}</a></center>";
						
					}
					
				exit();
				}
				
			
			}
		
	// User wants to sign in for the day
	if (isset($_GET['signin'])) {
		
		// User has submitted his schedule, let's sign him in
		if (isset($_POST['timeFull'])) {
			
					$hour = substr($_POST['timeFull'], 0, 2) - 2;
					$minute = substr($_POST['timeFull'], 3, 2);
					$lasthour = $hour . ":" . $minute;
					
					$user_id = $_POST['user_id'];					
					$loginTime = date('Y-m-d H:i:s');
					$workUntil = date('Y-m-d') . ' ' . $lasthour;
					
					// Check if last shift was logged out - if not auto-logout!
					$query = "SELECT id, comment FROM logins WHERE user_id = $user_id AND success = 0 AND type = 1";
					try
					{
						$result = $pdo3->prepare("$query");
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
							$id = $row['id'];
							$scheduled = $row['comment'];

						// $query = "INSERT INTO logins (time, user_id, type, success) VALUES ('$loginTime', '$user_id', 2, 1)";
						$query = "UPDATE logins SET type = 2, email = '$scheduled' WHERE id = $id";
						try
						{
							$result = $pdo3->prepare("$query");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user2: ' . $e->getMessage();
								echo $error;
								exit();
						}
						
					}
	
					$query = "INSERT INTO logins (time, user_id, type, success, comment) VALUES ('$loginTime', '$user_id', 1, 0, '$workUntil')";
					try
					{
						$result = $pdo3->prepare("$query");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user2: ' . $e->getMessage();
							echo $error;
							exit();
					}

						$_SESSION['successMessage'] = $lang['shift-started'] . "!";
						header("Location: index.php");
						exit();

		}
		
		$query = sprintf("SELECT first_name, user_id, memberno, email, userGroup, workStation, domain FROM users WHERE user_id = '%d';",
		$_GET['user_id']);
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$result = $result->fetch();
				$user_id = $result['user_id'];
				$username = $result['email'];
				$memberno = $result['memberno'];
				$userGroup = $result['userGroup'];
				$first_name = $result['first_name'];
				$domain = $result['domain'];
				$workStationAccess = $result['workStation'];
				
		$timePicker = <<<EOD
						
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
						
						pageStart($lang['working-hours'], NULL, $timePicker, "changeuser", NULL, $lang['working-hours'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

						echo "<center><div id='profilearea' style='font-size: 18px;'><img src='images/_$domain/members/$user_id.$photoExt' class='salesPagePic' /><br /><h4>Welcome $first_name!</h4><br />Until what time do you work today?</div></center>";
						
						echo <<<EOD
						
<center><br />
   <form id="timeForm" action="?signin" method="POST">
<input type='hidden' id="timeFull" name="timeFull" />
<input type='hidden' id="user_id" name="user_id" value="$user_id" />
<br />
 <div class="timepicker" style="font-size: 10px; margin-left: 24px;"></div><br />
 <span id="errorBox"></span><br />
      <button name='oneClick' class="visible" type="submit">Confirm</button>
 </form>
</center>
						
EOD;
						exit();

	}
	
	// User wants to sign out for the day
	if (isset($_GET['signout'])) {
		
		$loginTime = date('Y-m-d H:i:s');
		$user_id = $_GET['user_id'];

					// Find last scanin for this user
					$query = "SELECT id FROM logins WHERE user_id = $user_id ORDER BY time DESC";
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
						$id = $row['id'];
	
					// $query = "INSERT INTO logins (time, user_id, type, success) VALUES ('$loginTime', '$user_id', 2, 1)";
					$query = "UPDATE logins SET type = 2, success = 1, email = '$loginTime' WHERE id = $id";
					try
					{
						$result = $pdo3->prepare("$query");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user2: ' . $e->getMessage();
							echo $error;
							exit();
					}		
					
		$_SESSION['successMessage'] = $lang['shift-ended'] . "!";
		header("Location: main.php");
		exit();
		

	}
	
	// Check if a chip was scanned
	if (isset($_POST['cardid'])) {
		
		$cardid = $_POST['cardid'];
		
		if ($cardid == '') {
			
				$_SESSION['errorMessage'] = $lang['scan-error'];
			
		} else {

		
			// Query to look up user
			$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid = '$cardid'")->fetchColumn();
			
			if ($rowCount == 0) {
				// Query to look up user
				$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid2 = '{$cardid}'")->fetchColumn();
				
				if ($rowCount == 0) {
					// Query to look up user
					$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid3 = '{$cardid}'")->fetchColumn();
					
					if ($rowCount == 0) {
				   		handleError($lang['error-keyfob'],"");
					} else {
						$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid3 = '{$cardid}'");
					}
					
				} else {
					$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid2 = '{$cardid}'");
				}
	
				
			} else {
				$result = $pdo3->prepare("SELECT user_id, userGroup FROM users WHERE cardid = '{$cardid}'");
			}
			
					
			$result->execute();
			
			$row = $result->fetch();
				$user_id = $row['user_id'];
				$userGroup = $row['userGroup'];
				
			// Check if chip is registered more than once
			if ($rowCount > 1) {
				
				$_SESSION['errorMessage'] = $lang['chip-registered-more-than-once'];
				header("Location: duplicate-chip.php?cardid=$cardid");
				exit();
			
			}
			
			// Scan in / out code **********************************************************
			// Only run for Workers, not members
			if ($userGroup < 4 && $_SESSION['workertracking'] == 1) {
				// Check if member is scanned in, if so show logout code
				$query = "SELECT user_id FROM logins WHERE DATE(time) = DATE(NOW() - INTERVAL 3 HOUR) AND type = 2 AND user_id = $user_id";
				try
				{
					$result = $pdo3->prepare("$query");
					$result->execute();
					$data = $result->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
				if (!$data) {
					
					// Check if they have an open shift
					$query = "SELECT user_id FROM logins WHERE DATE(time) = DATE(NOW() - INTERVAL 3 HOUR) AND type = 1 AND user_id = $user_id";
					try
					{
						$result2 = $pdo3->prepare("$query");
						$result2->execute();
						$data2 = $result2->fetchAll();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
						
					if ($data2) {
					
						// Do you want to log out?
						pageStart($lang['working-hours'], NULL, $timePicker, "changeuser", NULL, $lang['working-hours'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
						$query = sprintf("SELECT first_name, last_name, user_id, memberno, email, userGroup, workStation, domain, photoExt FROM users WHERE user_id = '%d';",
						$user_id);
						try
						{
							$result = $pdo3->prepare("$query");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user1: ' . $e->getMessage();
								echo $error;
								exit();
						}
							
						$row = $result->fetch();
								$user_id = $row['user_id'];
								$username = $row['email'];
								$memberno = $row['memberno'];
								$userGroup = $row['userGroup'];
								$first_name = $row['first_name'];
								$last_name = $row['last_name'];
								$domain = $row['domain'];
								$photoExt = $row['photoExt'];
								$workStationAccess = $row['workStation'];
							
						echo "<center><div id='profilearea' style='font-size: 18px;'><img src='../images/_$domain/members/$user_id.$photoExt' class='salesPagePic' /><br /><h4>$first_name $last_name</h4><br />{$lang['end-shift-or-not']}?</div></center>";
						
						echo "<br /><center><a href='?signout&user_id=$user_id' class='cta'>{$lang['global-yes']}</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='mini-profile.php?user_id=$user_id' class='cta'>{$lang['global-no']}</a></center>";
					
					} else {
						
						// User has no open shifts today. Ask if he wants to start his shift
						pageStart($lang['working-hours'], NULL, $timePicker, "changeuser", NULL, $lang['working-hours'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
						$query = sprintf("SELECT first_name, last_name, user_id, memberno, email, userGroup, workStation, domain, photoExt FROM users WHERE user_id = '%d';",
						$user_id);
						try
						{
							$result = $pdo3->prepare("$query");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user1: ' . $e->getMessage();
								echo $error;
								exit();
						}
							
						$row = $result->fetch();
								$user_id = $row['user_id'];
								$username = $row['email'];
								$memberno = $row['memberno'];
								$userGroup = $row['userGroup'];
								$first_name = $row['first_name'];
								$last_name = $row['last_name'];
								$domain = $row['domain'];
								$photoExt = $row['photoExt'];
								$workStationAccess = $row['workStation'];
							
						echo "<center><div id='profilearea' style='font-size: 18px;'><img src='../images/_$domain/members/$user_id.$photoExt' class='salesPagePic' /><br /><h4>$first_name $last_name</h4><br />{$lang['start-shift-or-not']}?</div></center>";
						
						echo "<br /><center><a href='?signin&user_id=$user_id' class='cta'>{$lang['global-yes']}</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='mini-profile.php?user_id=$user_id' class='cta'>{$lang['global-no']}</a></center>";
						
					}
					
				}
			
			} // End workers only, signin/out code
			
			// On success: redirect.
			header("Location: mini-profile.php?user_id={$user_id}");
			exit();
		
		}

	}

	
	if ($_SESSION['puestosOrNot'] == 1) {
		
		if (isset($_SESSION['workstation'])) {
			
			$workstation = $_SESSION['workstation'];

			if ($workstation == "reception") {
				header("Location: reception.php");
				exit();
			} else if ($workstation == "bar") {
				header("Location: bar.php");
				exit();
			} else if ($workstation == "dispensary") {
				header("Location: dispensary.php");
				exit();
			} else {
				handleError("No Workstation specified","");
			}
			
		} else {
			
			pageStart($lang['choose-workstation'], NULL, $testinput, "pmain", "notSelected", $lang['choose-workstation'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
			// Look up Workstation access
			try
			{
				$result = $pdo3->prepare("SELECT workStation FROM users WHERE user_id = {$_SESSION['user_id']}");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$wSAccess = $row['workStation'];
				
			$_SESSION['workStationAccess'] = $wSAccess;
			
			echo <<<EOD
			   <center>
				<span class='maincta'>
EOD;
			
			
			// reception
			if ($wSAccess == 1 || $wSAccess == 6 || $wSAccess == 11 || $wSAccess == 16 || $_SESSION['userGroup'] == 1) {
				echo "<a href='reception.php?setsess' id='mainReceptionCTA'>&nbsp; {$lang['reception']}</a><br />";
			}
			// bar
			if ($wSAccess == 5 || $wSAccess == 6 || $wSAccess == 15 || $wSAccess == 16 || $_SESSION['userGroup'] == 1) {
				echo "<a href='bar.php?setsess' id='mainBarCTA'>&nbsp; {$lang['bar']}</a><br />";
			}
			// disp
			if ($wSAccess == 10 || $wSAccess == 11 || $wSAccess == 15 || $wSAccess == 16 || $_SESSION['userGroup'] == 1) {
			 echo "<a href='dispensary.php?setsess' id='mainDispensaryCTA'>&nbsp; {$lang['dispensary']}</a><br />";
			}
			
			
			if ($wSAccess == 0 && $_SESSION['userGroup'] > 1) {
			 echo "<span style='color: red; font-weight: 800;'><br /><br />" . $lang['no-workstation-access'] . "</span>";
			}
			
			echo <<<EOD
				</span>
			   </center>
EOD;

				
		}
		
	} else {
		
	if ($_SESSION['iPadReaders'] > 0) {
			
echo <<<EOD
<script>
setInterval(function()
{ 
    $.ajax({
      type:"post",
      url:"scansearch.php",
      datatype:"text",
      success:function(data)
      {
        if( data == 'false' ) {

	    } else if ( data == 'notregistered' ) {
		    
	        window.location.replace("main.php?notregistered");
		    
	    } else {
		    
	        window.location.replace("profile.php?user_id="+data);
	        
	    }
      }
    });
}, 3000);
</script>
EOD;


	}

	if (isset($_GET['notregistered'])) {
		
		$_SESSION['errorMessage'] = $lang['error-keyfob'];
			
	}
		
		pageStart("CCS", NULL, $testinput, "pindex", "notSelected", NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

		
?>

<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="" autocomplete="off" method="POST">
 <input type="text" name="cardid" id="focus" maxlength="30" autofocus value="" /><br />
<button name='oneClick' type="submit" style='display: none;'><?php echo $lang['form-accept']; ?></button>
</form>


<script>

$(document).ready(function() {
    $("#focus").focus().bind('blur', function() {
        $(this).focus();
    }); 

    $("html").click(function() {
        $("#focus").val($("#focus").val()).focus();
    });

    //disable the tab key
    $(document).keydown(function(objEvent) {
        if (objEvent.keyCode == 9) {  //tab pressed
            objEvent.preventDefault(); // stops its action
       }
    })      
});
</script>

<center>
 <span class="ctalinks">
 <a href="new-dispense.php"><span id="dispenseCTA"></span><br /><?php echo $lang['index-dispense']; ?></a>
 <a href="menu.php"><span id="productsCTA"></span><br /><?php echo $lang['index-menu']; ?></a>
 <a href="bar-new-sale.php"><span id="barCTA"></span><br /><?php echo $lang['barC']; ?></a><br />
 <a href="scan-profile.php"><span id="profileCTA"></span><br /><?php echo $lang['member-profilecaps']; ?></a>
 <?php if ($_SESSION['fingerprint'] == 1) { ?><a href="scan-finger.php"><span id="fingerCTA"></span><br /><?php echo $lang['fingerprint']; ?></a> <?php } ?>
 <a href="members.php"><span id="membersCTA"></span><br /><?php echo $lang['index-membersC']; ?></a>
 <a href="new-member-0.php"><span id="newmemberCTA"></span><br /><?php echo $lang['index-newmember']; ?></a>
 </span>
</center>


<?php } ?>