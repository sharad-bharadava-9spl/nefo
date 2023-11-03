<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
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
	// Look up user details for showing profile on the Sales page
	$purchaseDetails = "SELECT category, salesPrice, productid, purchaseDate, photoExt FROM purchases WHERE purchaseid = '{$purchaseid}'";
		try
		{
			$result = $pdo3->prepare("$purchaseDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$productid = $row['productid'];
			$category = $row['category'];
			$photoExt = $row['photoExt'];
			$salesPrice = $row['salesPrice'];
			$dayReg = date("d", strtotime($row['purchaseDate']));
			$monthReg = date("m", strtotime($row['purchaseDate']));
			$yearReg = date("y", strtotime($row['purchaseDate']));
			// get product namre
			if($category == 1){
				$getProduct = "SELECT name from flower where flowerid = '{$productid}'";
			}else if($category == 2){
				$getProduct = "SELECT name from extract where extractid = '{$productid}'";
			}else{
				$getProduct = "SELECT name from products where productid = '{$productid}'";
			}
				try
				{
					$result = $pdo3->prepare("$getProduct");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$rowProduct = $result->fetch();
				$product_name = $rowProduct['name'];
				$validationScript = <<<EOD
	
    $(document).ready(function() {
	    
	    	    
	  $('#registerForm').validate({
		  ignore:'', //because the radio buttons are hidden, validation ignores them. This way it'll work.
		  rules: {
			  day: {
				  required: true,
				  range:[0,31]
			  },
			  month: {
				  required: true,
				  range:[0,12]
			  },
			  year: {
				  required: true,
				  range:[0,2030]
			  }
    	},
		  errorPlacement: function(error, element) {
			  
			  if (element.attr("name") == "expenseCat") {
        		error.appendTo($('#categoryLink'));
    		  } else if ( element.is(":radio") || element.is(":checkbox")){
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

	pageStart("CCS", NULL, $validationScript, "pprofilenew", "admin", "FECHA DE COMPRA", $_SESSION['successMessage'], $_SESSION['errorMessage']);

   $purchasePhoto = $google_root.'images/_' . $_SESSION['domain'] . '/purchases/' . $purchaseid . '.' .  $photoExt;
	
	$object_exist = object_exist($google_bucket, $google_root_folder.'images/_' . $_SESSION['domain'] . '/purchases/' . $purchaseid . '.'.  $photoExt);

	if($object_exist === false){
		$purchasePhoto = $google_root."images/admin-dispensary.png";
		$notexist = 'yes';
	}
	/*if (!file_exists($purchasePhoto)) {
		$purchasePhoto = "<img class='profilepic' src='images/admin-dispensary.png' />";
		$notexist = 'yes';
	} else {
		$purchasePhoto = "<img class='profilepic' src='$purchasePhoto' width='237' />";
	}*/

		$purchasePhoto = "<img class='profilepic' src='$purchasePhoto' width='237' />";
		$highRollerWeekly = $_SESSION['highRollerWeekly'];
		$consumptionPercentage = $_SESSION['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly && $notexist == 'yes') {
		$highroller = "<br /><img src='images/highroller-big.png' style='margin-top: -4px;' />";
	} else if ($totalAmountPerWeek >= $highRollerWeekly && $notexist != 'yes') {
		$highroller = "<br /><img src='images/highroller-xl.png' style='margin-top: -4px;' />";
	} else {
		$highroller = "";
	}
	

?>
<br />
<div id="mainbox">
 <div id="mainleft">
 	 <span id="profilepicholder"><a href="purchase.php?purchaseid=<?php echo $purchaseid; ?>"><?php echo $purchasePhoto; ?></a><?php echo $highroller; ?></span>
<?php

	echo <<<EOD
   <span class='nametext'>$product_name</span><br />
EOD;
	echo "<br /><br /><a href='purchase.php?purchaseid=" . $purchaseid . "'><span class='creditDisplay'>Price: <span class='creditAmount $userClass'>" . number_format($salesPrice,2) . " ".$_SESSION['currencyoperator']."</span></span></a><br /><br />";

   echo "</div>";
?>
 <div id="donationholder">



<form id="registerForm" action="" method="POST">
	 <h4><?php echo $lang['change-purchase-date']; ?></h4>
	 <br>
	<table>
	  <tr>
		  	<td style='vertical-align: top;'>
			   <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />
			   <input type="number" lang="nb" name="day" class="twoDigit defaultinput" maxlength="2" value="<?php echo $dayReg; ?>" placeholder="dd"  />
			   <input type="number" lang="nb" name="month" class="twoDigit defaultinput" maxlength="2" value="<?php echo $monthReg; ?>"  placeholder="mm" />
			   <input type="number" lang="nb" name="year" class="fourDigit defaultinput" maxlength="4" value="<?php echo $yearReg; ?>"  placeholder="<?php echo $lang['member-yyyy']; ?>" />
			   <br>
			   <br>
			 	<button class='oneClick okbutton2' name='oneClick' type="submit" style="margin-left: 1px; width: 208px;">Cambiar</button>
			</td>
		</tr>
	</table>

 
</form>
</div>
</div>
<?php displayFooter(); ?>
