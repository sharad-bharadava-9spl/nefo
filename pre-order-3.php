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

	if ($_POST['step3'] == 'complete') {
		
		$query = "UPDATE systemsettings SET clubname = '{$_SESSION['name']}', clubemail = '{$_SESSION['email']}', clubphone = '{$_SESSION['telephone']}', minorder = '{$_SESSION['minorder']}', services = '{$_SESSION['services']}', openinghour = '{$_SESSION['openinghour']}', closinghour = '{$_SESSION['closinghour']}', day1 = '{$_SESSION['day1']}', day2 = '{$_SESSION['day2']}', day3 = '{$_SESSION['day3']}', day4 = '{$_SESSION['day4']}', day5 = '{$_SESSION['day5']}', day6 = '{$_SESSION['day6']}', day7 = '{$_SESSION['day7']}', multiple = '{$_SESSION['multiple']}', minutes = '{$_SESSION['minutes']}', hours = '{$_SESSION['hours']}', sameday = '{$_SESSION['sameday']}', deliveries = '{$_SESSION['deliveries']}', timeslots = '{$_SESSION['timeslots']}', deliverycharge = '{$_SESSION['deliverycharge']}', deliverychargepct = '{$_SESSION['deliverychargepct']}', nostocknodispense = '{$_SESSION['nostocknodispense']}'";
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
		
		$_SESSION['successMessage'] = $lang['preorder-activated'];
		header("Location: pre-order.php");
		exit();
		
	}
	
	if ($_POST['step2'] == 'complete') {
		
		$_SESSION['services'] = $_POST['services1'] + $_POST['services2'];
		$_SESSION['minorder'] = $_POST['minorder'];
		$_SESSION['openinghour'] = $_POST['openinghour'];
		$_SESSION['closinghour'] = $_POST['closinghour'];
/*		echo "minorder: " . $_POST['minorder'] . "<br />";
		echo "openinghour: " . $_POST['openinghour'] . "<br />";
		echo "closinghour: " . $_POST['closinghour'] . "<br />";*/
		if ($_POST['day1'] == '') {
			$_SESSION['day1'] = 0;
		} else {
			$_SESSION['day1'] = $_POST['day1'];			
		}
		if ($_POST['day2'] == '') {
			$_SESSION['day2'] = 0;
		} else {
			$_SESSION['day2'] = $_POST['day2'];			
		}
		if ($_POST['day3'] == '') {
			$_SESSION['day3'] = 0;
		} else {
			$_SESSION['day3'] = $_POST['day3'];			
		}
		if ($_POST['day4'] == '') {
			$_SESSION['day4'] = 0;
		} else {
			$_SESSION['day4'] = $_POST['day4'];			
		}
		if ($_POST['day5'] == '') {
			$_SESSION['day5'] = 0;
		} else {
			$_SESSION['day5'] = $_POST['day5'];			
		}
		if ($_POST['day6'] == '') {
			$_SESSION['day6'] = 0;
		} else {
			$_SESSION['day6'] = $_POST['day6'];			
		}
		if ($_POST['day7'] == '') {
			$_SESSION['day7'] = 0;
		} else {
			$_SESSION['day7'] = $_POST['day7'];			
		}
		$_SESSION['multiple'] = $_POST['multiple'];
		$_SESSION['minutes'] = $_POST['minutes'];
		$_SESSION['hours'] = $_POST['hours'];
		$_SESSION['sameday'] = $_POST['sameday'];
		$_SESSION['deliveries'] = $_POST['deliveries'];
		$_SESSION['timeslots'] = $_POST['timeslots'];
		$_SESSION['nostocknodispense'] = $_POST['nostocknodispense'];
		
		$deliverycharge = $_POST['deliverycharge'];
		$deliverychargepct = $_POST['deliverychargepct'];
		
		if ($deliverychargepct > 0) {
			$deliverycharge = $deliverychargepct;
			$deliverychargepct = 1;
		} else if (($deliverychargepct == '' || $deliverychargepct == 0) && ($deliverycharge == '' || $deliverycharge == 0)) {
			$deliverychargepct = 0;
			$deliverycharge = 0;
		} else {
			$deliverychargepct = 0;
		}
		
		$_SESSION['deliverycharge'] = $deliverycharge;
		$_SESSION['deliverychargepct'] = $deliverychargepct;
				
	} else {
		
		pageStart("CCS", NULL, $validationScript, "pindex", "notSelected", $lang['pre-order'], $_SESSION['successMessage'], "ERROR");
		exit();
		
	}
	
	
	pageStart("CCS", NULL, $validationScript, "pindex", "notSelected", $lang['pre-order'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
   <form id="registerForm" action="" method="POST">

 <div id='mainbox'>
  <div id='mainboxheader'>
#3 - <?php echo $lang['confirm-emails']; ?>  </div>
  
   <div class='boxcontent'>

<br />
<input type="hidden" name="step3" value="complete" />

 <table class=''>
  <tr>
   <td style='padding-right: 10px; width: 45%;'><strong>ENGLISH</strong></td>
   <td style='padding-left: 10px; width: 45%;'><strong>ESPAÑOL</strong></td>
  </tr>
  <tr>
   <td style='padding-right: 10px;'>
Dear $first_name $last_name,

<p>As an appreciated member of $clubName, we would like to invite you to our new pre-ordering system.

<p>Here you can log in and order products from us:<br /><br />
Link: $link<br />
Username: $email<br />
Password: $pwd<br />

<p>Happy Smoking!

<p>All the best,<br />
$clubName

   </td>
   <td style='padding-left: 10px;'>
Estimad@ $first_name $last_name,

<p>Como socio apreciado de $clubName, nos gustaría invitarte a probar nuestro nuevo sistema de pre-pedidos.

<p>Aquí abajo te facilitamos acceso y puedes pedirnos productos:<br /><br />
Enlace: $link<br />
Usuario: $email<br />
Contraseña: $pwd<br />

<p>¡Mantente a salvo y feliz!

<p>Te deseamos todo lo mejor,<br />
$clubName

   </td>
  </tr>
 </table> 
  </div>
  </div>

 <input type="submit" class='cta1' name='oneClick' type="submit" value="<?php echo $lang['activate']; ?>">

</form>

