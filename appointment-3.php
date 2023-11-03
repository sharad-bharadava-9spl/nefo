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
		
		$query = "UPDATE systemsettings SET clubname = '{$_SESSION['name']}', clubemail = '{$_SESSION['email']}', clubphone = '{$_SESSION['telephone']}', openinghourcita = '{$_SESSION['openinghourcita']}', closinghourcita = '{$_SESSION['closinghourcita']}', citaday1 = '{$_SESSION['citaday1']}', citaday2 = '{$_SESSION['citaday2']}', citaday3 = '{$_SESSION['citaday3']}', citaday4 = '{$_SESSION['citaday4']}', citaday5 = '{$_SESSION['citaday5']}', citaday6 = '{$_SESSION['citaday6']}', citaday7 = '{$_SESSION['citaday7']}', citamultiple = '{$_SESSION['citamultiple']}', citaminutes = '{$_SESSION['citaminutes']}', citahours = '{$_SESSION['citahours']}', citasameday = '{$_SESSION['citasameday']}'";
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
		header("Location: appointment.php");
		exit();
		
	}
	
	if ($_POST['step2'] == 'complete') {
		
		$_SESSION['openinghourcita'] = $_POST['openinghourcita'];
		$_SESSION['closinghourcita'] = $_POST['closinghourcita'];
		
		if ($_POST['citaday1'] == '') {
			$_SESSION['citaday1'] = 0;
		} else {
			$_SESSION['citaday1'] = $_POST['citaday1'];			
		}
		if ($_POST['citaday2'] == '') {
			$_SESSION['citaday2'] = 0;
		} else {
			$_SESSION['citaday2'] = $_POST['citaday2'];			
		}
		if ($_POST['citaday3'] == '') {
			$_SESSION['citaday3'] = 0;
		} else {
			$_SESSION['citaday3'] = $_POST['citaday3'];			
		}
		if ($_POST['citaday4'] == '') {
			$_SESSION['citaday4'] = 0;
		} else {
			$_SESSION['citaday4'] = $_POST['citaday4'];			
		}
		if ($_POST['citaday5'] == '') {
			$_SESSION['citaday5'] = 0;
		} else {
			$_SESSION['citaday5'] = $_POST['citaday5'];			
		}
		if ($_POST['citaday6'] == '') {
			$_SESSION['citaday6'] = 0;
		} else {
			$_SESSION['citaday6'] = $_POST['citaday6'];			
		}
		if ($_POST['citaday7'] == '') {
			$_SESSION['citaday7'] = 0;
		} else {
			$_SESSION['citaday7'] = $_POST['citaday7'];			
		}
		$_SESSION['citamultiple'] = $_POST['citamultiple'];
		$_SESSION['citaminutes'] = $_POST['citaminutes'];
		$_SESSION['citahours'] = $_POST['citahours'];
		$_SESSION['citasameday'] = $_POST['citasameday'];
		
				
	} else {
		
		pageStart($lang['appointment-module'], NULL, $validationScript, "pindex", "notSelected", $lang['appointment-module'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
		
	}
	
	
	pageStart($lang['appointment-module'], NULL, $validationScript, "pindex", "notSelected", $lang['appointment-module'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

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

<p>As an appreciated member of $clubName, we would like to invite you to our new system for booking appointments.

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

<p>Como socio apreciado de $clubName, nos gustaría invitarte a probar nuestro nuevo sistema para pedir citas.

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

