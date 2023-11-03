<?php
	
	session_start();
	
	require_once 'cOnfig/connection-tablet.php';
	require_once 'cOnfig/view-nohead.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	getSettings();
	
	if (isset($_GET['uid'])) {
		
		$user_id = $_GET['uid'];
		
	} else if (isset($_POST['uid'])) {
		
		$user_id = $_POST['uid'];
		
	} else {
		
		pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		echo "<center>An error has occured #a. Please try again or <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
		exit();
		
	}
	

	if (isset($_GET['hash'])) {
		
		$hash = $_GET['hash'];
		
		$query = "SELECT hash FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user24: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$hashreal = $row['hash'];	
	
			
		if ($hashreal != $hash) {
			
			pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			echo "<center>An error has occured #b. Please try again or <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
			exit();
			
		}
		
		$_SESSION['hash'] = 'verified';
		
	} else if ($_SESSION['hash'] != 'verified') {
		
			pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			echo "<center>An error has occured #c. Please try again or <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
			exit();
			
	}
	
	$package = $_GET['pck'];
	
	if ($package > 5) {
		
			pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			echo "<center>An error has occured #d. Please try again or <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
			exit();
			
	}
	
	if ($package == 0) {
		$packageName = 'Test plan - 1 day in the barn';
		$packagePrice = 5;
	} else if ($package == 1) {
		$packageName = 'Starter plan - 3 months';
		$packagePrice = 25;
	} else if ($package == 2) {
		$packageName = 'Basic - 1 year';
		$packagePrice = 50;
	} else if ($package == 3) {
		$packageName = 'Premium - 1 year';
		$packagePrice = 150;
	} else if ($package == 4) {
		$packageName = 'VIP plan - 1 year';
		$packagePrice = 420;
	} else if ($package == 5) {
		$packageName = 'Business package - 1 year';
		$packagePrice = 710;
	} else {
		
			pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			echo "<center>An error has occured #d. Please try again or <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
			exit();
			
	}

	// Look up user e-mail
	$query = "SELECT first_name, last_name, email FROM users WHERE user_id = '{$user_id}'";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user33: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$email = $row['email'];
		
	if ($package == 5 && (!isset($_GET['business']))) {
		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    	    
	  $('#registerForm').validate({
		  rules: {
			  company: {
				  required: true,
				  minlength: 2
			  },
			  taxid: {
				  required: true,
				  minlength: 2
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
	pageStart("Packages", NULL, $validationScript, "pmembership", NULL, "Packages", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	echo "<center><div id='profilearea'>$first_name $last_name<br /><span class='smallerfont4'>$packageName<br />$packagePrice USD</span><div class='clearfloat'></div></div>";

	echo <<<EOD
	<center><br />As you've selected a business accont, we need to ask you for some further details:</center><br />
<form id="registerForm" action="?business&uid=$user_id&pck=5" method="POST" onsubmit="return testInput()">
  <table>
   <tr>
    <td>Company name&nbsp;&nbsp;</td>
    <td><input type="text" name="company" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Tax ID #</td>
    <td><input type="text" name="taxid" /><br />&nbsp;</td>
   </tr>
  </table>
 <button class='oneClick' name='oneClick' type="submit">Save</button>
</form>
EOD;
exit();

} else if ($package == 5 && (isset($_GET['business']))) {
	
	$company = $_POST['company'];
	$taxid = $_POST['taxid'];
	
	
	$query = "UPDATE users SET company = '$company', taxid = '$taxid' WHERE user_id = $user_id";
	try
	{
		$resultc = $pdo3->prepare("$query");
		$resultc->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching userx: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
}
	pageStart("Packages", NULL, $validationScript, "pmembership", NULL, "Packages", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo "<center><h1>Please pay your membership using the Paypal link below.<!--<br />After making the payment, wait for the page to update - do not click any buttons!--></h1><br />";

	echo "<div id='profilearea'>$first_name $last_name<br /><span class='smallerfont4'>$packageName<br />$packagePrice USD</span><div class='clearfloat'></div></div>";
	
	// Look up user e-mail
	$query = "INSERT INTO sales (userid, amount, amountpaid, paid) VALUES ($user_id, $packagePrice, $packagePrice, 0)";
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
	$saleid = $pdo3->lastInsertId();


// Include and initialize paypal class
include 'PaypalExpress.class.php';
$paypal = new PaypalExpress;
// Get product ID from URL
$productID = $saleid;

$productData['name'] = $packageName;
$productData['price'] = sprintf("%0.2f", $packagePrice);
$productData['id'] = $saleid;
/*
echo "name: " . $productData['name'] . "<br />";
echo "PRICE: " . $productData['price'] . "<br />";
echo "id: " . $productData['id'] . "<br />";
*/

	
?>
<br /><br />
        <!-- Checkout button -->
        <div id="paypal-button"></div>
<br /><br />
<br /><br />
<script>
paypal.Button.render({
    // Configure environment
    env: '<?php echo $paypal->paypalEnv; ?>',
    client: {
        sandbox: '<?php echo $paypal->paypalClientID; ?>',
        production: '<?php echo $paypal->paypalClientID; ?>'
    },
    // Customize button (optional)
    locale: 'en_US',
    style: {
        size: 'small',
        color: 'gold',
        shape: 'pill',
    },
    // Set up a payment
    payment: function (data, actions) {
        return actions.payment.create({
            transactions: [{
                amount: {
                    total: '<?php echo $productData['price']; ?>',
                    currency: 'USD'
                }
            }]
      });
    },
    // Execute the payment
    onAuthorize: function (data, actions) {
        return actions.payment.execute()
        .then(function () {
            // Show a confirmation message to the buyer
            //window.alert('Thank you for your purchase!');
            
            // Redirect to the payment process page
            window.location = "process.php?paymentID="+data.paymentID+"&token="+data.paymentToken+"&payerID="+data.payerID+"&pid=<?php echo $productData['id']; ?>";
        });
    }
}, '#paypal-button');
</script>
