<?php

	

	require_once 'cOnfig/connection.php';

	require_once 'cOnfig/view.php';

	require_once 'cOnfig/authenticate.php';

	require_once 'cOnfig/languages/common.php';

	

	session_start();

	$accessLevel = '1';

	

	// Authenticate & authorize

	authorizeUser($accessLevel);
	
	if (isset($_GET['dobackup'])) {
		
	$host = DATABASE_HOST;	
	$domain = $_SESSION['domain'];
	$db_name = $_SESSION['db_name'];
	$db_user = $_SESSION['db_user'];
	$db_pwd = $_SESSION['db_pwd'];
	
require_once "BackupMySQL.php";
$backup = new BackupMySQL([
	'host'=> "$host",
	'database'=> "$db_name",
	'user'=> "$db_user",
	'password'=> "$db_pwd",
]);
$backup->download();
	
	} else if (isset($_POST['userPass'])) {
	
		$userPass = $_POST['userPass'];

		$userId = $_SESSION['user_id'];

		

		// Look up email for password crypt

		$query = "SELECT email FROM users WHERE user_id = $userId";

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

		

		// Check pwd first

		$query = sprintf("SELECT memberno FROM users WHERE email = '$email' AND userPass = '%s';",

			crypt($userPass, $email));

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
			

			$_SESSION['errorMessage'] = "Contrase&ntilde;a incorrecta / Wrong password!";

				

		} else {
			
			$deleted = '';
			
		

			$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));

			

			foreach($_POST['toDelete'] as $toDelete) {

	    		$delOrNot .=  $toDelete;

	  		}

	  		

	  		if (strpos($delOrNot, 'B1') !== false) {
		  		
		  		$deleted .= 'Bar categories<br />';
		  		

	    		$query = "DELETE FROM b_categories";

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
	

			}

			

	  		if (strpos($delOrNot, 'B2') !== false) {

		  		

		  		$deleted .= 'Bar products<br />';
	    		$query = "DELETE FROM b_products";

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
	

			}

			

	  		if (strpos($delOrNot, 'B3') !== false) {

		  		

		  		$deleted .= 'Bar purchases<br />';
	    		$query = "DELETE FROM b_purchases";

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
	

			}

			

	  		if (strpos($delOrNot, 'B4') !== false) {

		  		

		  		$deleted .= 'Bar movements<br />';
	    		$query = "DELETE FROM b_productmovements";

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
	

			}

			

	  		if (strpos($delOrNot, 'B5') !== false) {

		  		

		  		$deleted .= 'Bar providers<br />';
	    		$query = "DELETE FROM b_providers";

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
	    		$query = "DELETE FROM b_providerpayments";

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
	

			}

			

	  		if (strpos($delOrNot, 'B6') !== false) {

		  		

		  		$deleted .= 'Bar sales<br />';
	    		$query = "DELETE FROM b_sales";

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
	    		$query = "DELETE FROM b_salesdetails";

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
	

			}

			

	  		if (strpos($delOrNot, 'D1') !== false) {

		  		

		  		$deleted .= 'Dispensary categories<br />';
	    		$query = "DELETE FROM categories WHERE id > 2";

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
	

			}

			

	  		if (strpos($delOrNot, 'D2') !== false) {

		  		

		  		$deleted .= 'Dispensary products<br />';
	    		$query = "DELETE FROM flower";

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
	    		$query = "DELETE FROM extract";
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
	    		$query = "DELETE FROM products";
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
	

			}

			

	  		if (strpos($delOrNot, 'D3') !== false) {

		  		

		  		$deleted .= 'Dispensary purchases<br />';
	    		$query = "DELETE FROM purchases";

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
	

			}

			

	  		if (strpos($delOrNot, 'D4') !== false) {

		  		

		  		$deleted .= 'Dispensary movements<br />';
	    		$query = "DELETE FROM productmovements";

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
	

			}

			

	  		if (strpos($delOrNot, 'D5') !== false) {

		  		

		  		$deleted .= 'Dispensary providers<br />';
	    		$query = "DELETE FROM providers";
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
	    		$query = "DELETE FROM providerpayments";
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
	

			}

			

	  		if (strpos($delOrNot, 'D6') !== false) {

		  		

		  		$deleted .= 'Dispenses<br />';
	    		$query = "DELETE FROM sales";

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
	    		$query = "DELETE FROM salesdetails";

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
	

			}

			

	  		if (strpos($delOrNot, 'F1') !== false) {

		  		

		  		$deleted .= 'Expenses<br />';
	    		$query = "DELETE FROM expenses";

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
	

			}

			

	  		if (strpos($delOrNot, 'F2') !== false) {

		  		

		  		$deleted .= 'Donations<br />';
	    		$query = "DELETE FROM donations";

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
	

			}

			

	  		if (strpos($delOrNot, 'F3') !== false) {

		  		

		  		$deleted .= 'Member payments<br />';
	    		$query = "DELETE FROM memberpayments";
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
	

			}

			

	  		if (strpos($delOrNot, 'F4') !== false) {

		  		

		  		$deleted .= 'Banked money<br />';
	    		$query = "DELETE FROM banked";

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
	

			}

			

	  		if (strpos($delOrNot, 'F5') !== false) {

		  		

		  		$deleted .= 'Opening & Closing<br />';
	    		$query = "DELETE FROM closing";

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
	    		$query = "DELETE FROM closingdetails";
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
	    		$query = "DELETE FROM closingother";
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
	    		$query = "DELETE FROM opening";
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
	    		$query = "DELETE FROM openingdetails";
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
	    		$query = "DELETE FROM openingother";
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
	    		$query = "DELETE FROM recclosing";
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
	    		$query = "DELETE FROM recopening";
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
	    		$query = "DELETE FROM recshiftclose";
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
	    		$query = "DELETE FROM recshiftopen";
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
	    		$query = "DELETE FROM secclosing";
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
	    		$query = "DELETE FROM secopening";
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
	    		$query = "DELETE FROM secshiftclose";
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
	    		$query = "DELETE FROM secshiftopen";
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
	    		$query = "DELETE FROM shiftclose";
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
	    		$query = "DELETE FROM shiftclosedetails";
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
	    		$query = "DELETE FROM shiftcloseother";
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
	    		$query = "DELETE FROM shiftopen";
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
	    		$query = "DELETE FROM shiftopendetails";
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
	    		$query = "DELETE FROM shiftopenother";
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
	

			}

			

	  		if (strpos($delOrNot, 'M1') !== false) {

		  		

		  		$deleted .= 'Log<br />';
	    		$query = "DELETE FROM log";

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
	

			}

			

	  		if (strpos($delOrNot, 'M2') !== false) {

		  		

		  		$deleted .= 'Discounts<br />';
	    		$query = "DELETE FROM b_catdiscounts";

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
	    		$query = "DELETE FROM b_inddiscounts";

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
	    		$query = "DELETE FROM catdiscounts";

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
	    		$query = "DELETE FROM inddiscounts";

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
	

			}

			

			if (strpos($delOrNot, 'M3') !== false) {

				

		  		$deleted .= 'Visits<br />';
	    		$query = "DELETE FROM newvisits";

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
	

			}
			
			try {
				
			// Send e-mail(s)
			require_once 'PHPMailerAutoload.php';
			
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "mail.cannabisclub.systems";
			$mail->SMTPAuth = true;
			$mail->Username = "info@cannabisclub.systems";
			$mail->Password = "Insjormafon9191";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('info@cannabisclub.systems', 'CCSNube');
			$mail->addAddress("info@cannabisclub.systems", "CCS");
			$mail->addAddress("andreas@cannabisclub.systems", "Andreas");
			$mail->Subject = "Club '$domain' just purged their software!";
			$mail->isHTML(true);
			$mail->Body = "<strong>Items purged:</strong><br /><br />$deleted";
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "Error sending email!";
			}
			
		}
	}


	

	$validationScript = <<<EOD

    $(document).ready(function() {

	    

	  $( function() {

	    $( "#datepicker" ).datepicker({

			dateFormat: "dd-mm-yy"

	    });

	  });



	    	    

	  $('#registerForm').validate({

		  rules: {

			  userPass: {

				  required: true

			  },

			  "toDelete[]": {

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





	pageStart($lang['purge-data'], NULL, $validationScript, "pexpenses", 'dev-align-center', $lang['purge-data'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

		



?>
<br>
<a href='?dobackup' class='cta1' style="display: inline; padding: 10px 10px 10px;">Pincha aqui para descargar un backup</a><br /><br />
<form id="registerForm" action="" method="POST">
		<br />
<div class="actionbox-np2">
  <div class="boxcontent"  style="padding-bottom: 0; text-align: left;">
	
		<div class="mainboxheader">
 		<img src="images/settings-bar.png" style="margin-bottom: -4px; margin-right: 10px;">Bar </div><br>
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['categories']; ?>
		  <input type="checkbox" name="toDelete[]"  value="B1" >
		  <div class="fakebox"></div>
		 </label>
		</div>	
		<br>
		<br>	
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-products']; ?>
		  <input type="checkbox" name="toDelete[]"  value="B2" >
		  <div class="fakebox"></div>
		 </label>
		</div>	
		<br>
		<br>	
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['purchases']; ?>
		  <input type="checkbox" name="toDelete[]"  value="B3" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>		
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['add-movements']; ?>
		  <input type="checkbox" name="toDelete[]"  value="B4" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>		
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['providers']; ?>
		  <input type="checkbox" name="toDelete[]"  value="B5" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>		
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['sales']; ?>
		  <input type="checkbox" name="toDelete[]"  value="B6" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>		
	</div>
</div>
<div class="actionbox-np2">
	<div class="boxcontent"  style="padding-bottom: 0; text-align: left;">
 	<div class="mainboxheader">
 		<img src="images/settings-dispensary.png" style="margin-bottom: -4px; margin-right: 10px;"><?php echo $lang['title-dispensary']; ?></div><br>
	 	<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['categories']; ?>
		  <input type="checkbox" name="toDelete[]"  value="D1" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>	 	<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-products']; ?>
		  <input type="checkbox" name="toDelete[]"  value="D2" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>	 	
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['purchases']; ?>
		  <input type="checkbox" name="toDelete[]"  value="D3" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>	 	
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['add-movements']; ?>
		  <input type="checkbox" name="toDelete[]"  value="D4" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>	 	
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['providers']; ?>
		  <input type="checkbox" name="toDelete[]"  value="D5" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>	 	
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-dispenses']; ?>
		  <input type="checkbox" name="toDelete[]"  value="D6" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>
	</div>
</div>
<div class="actionbox-np2">
	<div class="boxcontent" style="padding-bottom: 0; text-align: left;">		
	   	<div class="mainboxheader">
 		  <?php echo $lang['closeday-finances']; ?></div><br>
 		  <div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-expenses']; ?>
		  <input type="checkbox" name="toDelete[]"  value="F1" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-donations']; ?>
		  <input type="checkbox" name="toDelete[]"  value="F2" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['membership-payments']; ?>
		  <input type="checkbox" name="toDelete[]"  value="F3" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['closeday-banked']; ?>
		  <input type="checkbox" name="toDelete[]"  value="F4" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['open-and-close']; ?>
		  <input type="checkbox" name="toDelete[]"  value="F5" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>
	</div>
</div>
<div class="actionbox-np2">
	<div class="boxcontent" style="padding-bottom: 0; text-align: left;">	
 	<div class="mainboxheader">
 		<img src="images/settings-members.png" style="margin-bottom: -4px; margin-right: 10px;"><?php echo $lang['global-members']; ?></div><br>
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['log']; ?>
		  <input type="checkbox" name="toDelete[]"  value="M1" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>		
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['discounts']; ?>
		  <input type="checkbox" name="toDelete[]"  value="M2" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>		
		<div class="fakeboxholder">	
		 <label class="control">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['visits']; ?>
		  <input type="checkbox" name="toDelete[]"  value="M3" >
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br>
		<br>
		</div>
	</div>
		<center>
			<div class="actionbox-np2">
					
				<div class="boxcontent">
					 <div class='mainboxheader'><?php echo $lang['index-password']; ?></div>
					 <input type="password" name="userPass" class="sixDigit defaultinput" /><br /><br />
				</div>

			</div>

		</center>
		 <button class='oneClick cta1' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>
