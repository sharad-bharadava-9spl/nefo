<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	getSettings();
	
	// Did this page re-submit with a form? If so, check & store details	
	if (isset($_POST['amount'])) {
		
		$providerid = $_POST['providerid'];
		$amount = $_POST['amount'];
		$comment = $_POST['comment'];
		$registertime = date('Y-m-d H:i:s');
		
		// Look up provider credit
		$userCredit = "SELECT credit FROM providers WHERE id = '$providerid'";
	
		$result = mysql_query($userCredit)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$oldCredit = $row['credit'];

		$newCredit = $amount + $oldCredit;
		
		// Query to add to Donations table
		 $query = sprintf("INSERT INTO providerpayments (providerid, paymentTime, amount, comment) VALUES ('%d', '%s', '%f', '%s');",
		  $providerid, $registertime, $amount, $comment);
		  
		mysql_query($query)
			or handleError($lang['error-savedonation'],"Error inserting donation: " . mysql_error());
			
		// Query to update user profile
		$updateUser = sprintf("UPDATE providers SET credit = '%f' WHERE id = '%d';",
			mysql_real_escape_string($newCredit),
			mysql_real_escape_string($providerid)
			);
				
		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error updating user profile: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Pago hecho con éxito!";
		header("Location: provider.php?providerid=$providerid");
		exit();
	}
	/***** FORM SUBMIT END *****/
	

	if (isset($_GET['providerid'])) {
		$providerid = $_GET['providerid'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
		
	// Query to look up provider
	$providerDetails = "SELECT registered, name, comment, providernumber, credit FROM providers WHERE id = $providerid";
				
	$result = mysql_query($providerDetails)
		or handleError($lang['error-loadpurchase'],"Error loading purchase: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$registered = $row['registered'];
		$name = $row['name'];
		$comment = $row['comment'];
		$providernumber = $row['providernumber'];
		$credit = $row['credit'];
		
	$selectPurchases = "SELECT purchaseDate, purchaseid, category, productid, purchaseQuantity, realQuantity, purchasePrice, paid FROM purchases WHERE provider = $providerid ORDER BY purchaseDate DESC";
	
	$resultPurchases = mysql_query($selectPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());


	pageStart($lang['providers'], NULL, $deleteUserScript, "pmembership", "pmembership", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 
	
	

?>


<br /><br /><br />

<center><div id='productoverview'>
 
 <table style="display: inline-block; vertical-align: top; <?php if ($category == '2') { echo 'margin-top: 9px;'; } ?>">
  <tr>
   <td><?php echo $lang['provider']; ?>:</td>
   <td class='yellow fat'>#<?php echo $providernumber; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-name']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-credit']; ?>:</td>
   <td class='yellow fat'><?php echo $credit; ?> €</td>
  </tr>
 </table>
</div></center>

 <br /><br />


 <div id="overviewWrap">
 <div class="overview" style="padding: 10px 50px;">
 <form id="registerForm" action="" method="POST">
 
 <h5>Pagar proveedor</h5>
  
  <input type="hidden" name="providerid" value="<?php echo $providerid; ?>" />
  <input type="number" lang="nb" name="amount" placeholder="&euro;" class="fourDigit" step="0.01" /><br />
  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea><br /><br />
  
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>
 </div>
 
	 <br /><br />
   
<?php displayFooter(); ?>
