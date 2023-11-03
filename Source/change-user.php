<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
		if ($_GET['setTime'] == 'yes') {
			
			$query = sprintf("SELECT first_name, user_id, memberno, email, userGroup, workStation, domain FROM users WHERE user_id = '%d';",
			$_POST['user_id']);
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
					
					
					$hour = substr($_POST['timeFull'], 0, 2) - 2;
					$minute = substr($_POST['timeFull'], 3, 2);
					$lasthour = $hour . ":" . $minute;
					
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
					
						$_SESSION['user_id'] = $user_id;
						$_SESSION['username'] = $username;
						$_SESSION['memberno'] = $memberno;
						$_SESSION['userGroup'] = $userGroup;
						$_SESSION['first_name'] = $first_name;
						$_SESSION['domain'] = $domain;
						$_SESSION['workStationAccess'] = $workStationAccess;
						unset($_SESSION['workstation']);
						$_SESSION['successMessage'] = 'Operador cambiado con exito!';
						header("Location: index.php");
						exit();
		}
	
		// Did the user submit a form with username for login?
		if ($_POST['action'] == 'submit') {
					
			// Try to log the user in
			$email = trim($_POST['email']);
			$password = trim($_POST['password']);
			
			// Look up the provided credentials
			$query = sprintf("SELECT first_name, user_id, memberno, email, userGroup, workStation, domain FROM users WHERE email = '%s' AND userPass = '%s';",
			$email, crypt($password, $email));
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user1: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			// Check if worker tracking is enabled in system settings
			if ($_SESSION['workertracking'] == 0) {
				
				if ($data) {
					$result = $data[0];
					$_SESSION['user_id'] = $result['user_id'];
					$_SESSION['username'] = $result['email'];
					$_SESSION['memberno'] = $result['memberno'];
					$_SESSION['userGroup'] = $result['userGroup'];
					$_SESSION['first_name'] = $result['first_name'];
					$_SESSION['domain'] = $result['domain'];
					$_SESSION['workStationAccess'] = $result['workStation'];
					unset($_SESSION['workstation']);
					$_SESSION['successMessage'] = 'Operador cambiado con exito!';
					header("Location: index.php");
					exit();
				} else {
					$_SESSION['errorMessage'] = 'Contrase&ntilde;a incorrecto';
				}
				
			} else {
				
				if (!$data) {
					
					$_SESSION['errorMessage'] = 'Contrase&ntilde;a incorrecto';
					
				} else {
				
					$result = $data[0];
						$user_id = $result['user_id'];
						$username = $result['email'];
						$memberno = $result['memberno'];
						$userGroup = $result['userGroup'];
						$first_name = $result['first_name'];
						$domain = $result['domain'];
						$workStationAccess = $result['workStation'];
						
					// Check if logged in today already - to determine 'today', take timestamp now, minus 3 hours
					$query = "SELECT user_id FROM logins WHERE DATE(time) = DATE(NOW() - INTERVAL 3 HOUR) AND user_id = $user_id";
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
						
						// Show welcome message and timepicker
						$photoExt = $_POST['photoExt'];
						
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
   <form id="timeForm" action="?setTime=yes" method="POST">
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
						
					} else {
						
						// Change user as normally
						$_SESSION['user_id'] = $user_id;
						$_SESSION['username'] = $username;
						$_SESSION['memberno'] = $memberno;
						$_SESSION['userGroup'] = $userGroup;
						$_SESSION['first_name'] = $first_name;
						$_SESSION['domain'] = $domain;
						$_SESSION['workStationAccess'] = $workStationAccess;
						unset($_SESSION['workstation']);
						$_SESSION['successMessage'] = 'Operador cambiado con exito!';
						header("Location: index.php");
						exit();
						
					}

						
				}
				
			}
			
		}


		if (isset($_GET['loggedinuser'])) {
			$newUser = $_GET['loggedinuser'];
			// Look up the provided credentials
			$query = sprintf("SELECT first_name, last_name, memberno, email, photoExt, userGroup FROM users WHERE user_id = '%d';",
			$newUser);
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
					$first_name = $row['first_name'];
					$last_name = $row['last_name'];
					$memberno = $row['memberno'];
					$email = $row['email'];
					$photoExt = $row['photoExt'];
					$userGroup = $row['userGroup'];

			
			pageStart($lang['change-operator'], NULL, $validationScript, "changeuser", NULL, $lang['change-operator'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$topimg = "images/_$domain/members/$newUser.$photoExt";
	if (!file_exists($topimg)) {
		$topimg = 'images/silhouette-new.png';
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


	echo <<<EOD
	
<center><div class='userbox'>
<span class="profilepicholder" style="float: left; margin-right: 15px;" ><img class="profilepic" src="$topimg" width="143" />$highroller</span>


 <table style="display: inline-block; vertical-align: top; text-align: left;">
  <tr>
   <td class='biggerfont'><span class='firsttext'>#$memberno</span>&nbsp;&nbsp;<span class='secondtext'></span><br />
   <span class='nametext'>$first_name $last_name</span><br /> $groupName<br /></td>
  </tr>
  <tr>
   <td>   <form id="registerForm" action="" method="POST">
     <input type="hidden" name='action' value='submit'>
     <input type="hidden" name='user_id' value='<?php echo $user_id; ?>'>
     <input type="hidden" name='email' value='<?php echo $email; ?>'>
     <input type="hidden" name='photoExt' value='<?php echo $photoExt; ?>'>
     <label for="password" style='text-transform: uppercase;'>{$lang['password']}</label><br />
     <input type="password" name="password" class='defaultinput' style='margin-left: 0; margin-top: 2px;' /><br /><br />
</td>
  </tr>
 </table>

EOD;		

			
		} else {
			handleError("No user specified","");
		}
		
?>
</div>
<center>
      <button name='oneClick' class="cta5" type="submit"><?php echo $lang['change-operator']; ?></button>
   </form>
 </center>
