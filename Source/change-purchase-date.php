<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['month'])) {
		
		$purchaseid = $_POST['purchaseid'];
  	    $day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		
		if ($day > 0 && $month > 0 && $year > 0) {
			$registertime = date("Y-m-d H:i:s", strtotime($month . "/" . $day . "/" . $year));
		} else {
			$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		}
		

		// Query to add new sale to Sales table - 6 arguments
		  $query = "UPDATE purchases SET purchaseDate = '$registertime' WHERE purchaseid = '$purchaseid'";
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

			
			// On success: redirect.
			$_SESSION['successMessage'] = "Fecha de compra cambiado con &eacute;xito!";
			header("Location: purchase.php?purchaseid=$purchaseid");
			exit();
		}
	/***** FORM SUBMIT END *****/
	
	
	
	// Get the user ID
	if (isset($_GET['purchaseid'])) {
		$purchaseid = $_GET['purchaseid'];
	} else {
		handleError($lang['error-nouserid'],"");
	}


	pageStart("CCS", NULL, $validationScript, "pmembership", "admin", "FECHA DE COMPRA", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>
<br />
 <div id="overviewWrap">
 <div class="overview">


<form id="registerForm" action="" method="POST">
   <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" value="<?php echo $dayReg; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" value="<?php echo $monthReg; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" value="<?php echo $yearReg; ?>" />

<br /><br />

 <button class='oneClick' name='oneClick' type="submit">Cambiar</button>
 
</form>
</div></div>

<?php displayFooter(); ?>
