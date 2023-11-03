<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$domain = $_SESSION['domain'];

	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['user_id'])) {
		
		$user_id = $_POST['user_id'];
		$discount      = $_POST['discount'];
		$discountBar      = $_POST['discountBar'];

		
		// DISPENSARY DISCOUNTS
		// First, delete from inddiscounts for this user_id, then set new values
		$deleteDiscounts = "DELETE FROM catdiscounts WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$deleteDiscounts")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		foreach($_POST['indCatDiscount'] as $catDiscount) {
			
			$catD = $catDiscount['catDiscount'];
			$catID = $catDiscount['catID'];


			if ($catD > 100) {
				$catD = 100;
			}
			
			if ($catD != '') {
			
				$addDiscount = sprintf("INSERT INTO catdiscounts (user_id, categoryid, discount) VALUES ('%d', '%d', '%f');",
				  $user_id, $catID, $catD);
		try
		{
			$result = $pdo3->prepare("$addDiscount")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			}
				
		}
		
		$deleteDiscounts = "DELETE FROM inddiscounts WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$deleteDiscounts")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		foreach($_POST['indDiscount'] as $indDiscount) {
			
			$indD = $indDiscount['purchaseDiscount'];
			$indFijo = $indDiscount['purchaseFijo'];
			$purchaseid = $indDiscount['purchaseid'];

			if ($indD > 100) {
				$indD = 100;
			}
			
			if ($indD != '') {
			
				$addDiscount = sprintf("INSERT INTO inddiscounts (user_id, purchaseid, discount) VALUES ('%d', '%d', '%f');",
				  $user_id, $purchaseid, $indD);
		try
		{
			$result = $pdo3->prepare("$addDiscount")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			}
			
			if ($indFijo != '') {
			
				$addDiscount = sprintf("INSERT INTO inddiscounts (user_id, purchaseid, fijo) VALUES ('%d', '%d', '%f');",
				  $user_id, $purchaseid, $indFijo);
		try
		{
			$result = $pdo3->prepare("$addDiscount")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			}

		}

		
		// BAR DISCOUNTS
		// First, delete from inddiscounts for this user_id, then set new values
		$deleteDiscountsB = "DELETE FROM b_catdiscounts WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$deleteDiscountsB")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		foreach($_POST['indCatDiscountB'] as $catDiscountB) {
			
			$catDB = $catDiscountB['catDiscountB'];
			$catIDB = $catDiscountB['catIDB'];


			if ($catDB > 100) {
				$catDB = 100;
			}
			
			if ($catDB != '') {
			
				$addDiscountB = sprintf("INSERT INTO b_catdiscounts (user_id, categoryid, discount) VALUES ('%d', '%d', '%f');",
				  $user_id, $catIDB, $catDB);
		try
		{
			$result = $pdo3->prepare("$addDiscountB")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			}
				
		}
		
		$deleteDiscountsB = "DELETE FROM b_inddiscounts WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$deleteDiscountsB")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		foreach($_POST['indDiscountB'] as $indDiscountB) {
			
			$indDB = $indDiscountB['purchaseDiscountB'];
			$purchaseidB = $indDiscountB['purchaseidB'];
			$indFijoB = $indDiscountB['purchaseFijoB'];

			if ($indDB > 100) {
				$indDB = 100;
			}
			
			if ($indDB != '') {
			
				$addDiscountB = sprintf("INSERT INTO b_inddiscounts (user_id, purchaseid, discount) VALUES ('%d', '%d', '%f');",
				  $user_id, $purchaseidB, $indDB);
		try
		{
			$result = $pdo3->prepare("$addDiscountB")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			}
			
			if ($indFijoB != '') {
			
				$addDiscountB = sprintf("INSERT INTO b_inddiscounts (user_id, purchaseid, fijo) VALUES ('%d', '%d', '%f');",
				  $user_id, $purchaseidB, $indFijoB);
		try
		{
			$result = $pdo3->prepare("$addDiscountB")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			}

		}
		

	$updateUser = sprintf("UPDATE users SET discount = '%d', discountBar = '%d' WHERE user_id = '%d';",
$discount,
$discountBar,
$user_id
);

		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
						
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['discounts-applied'];
		header("Location: profile.php?user_id={$user_id}");
		exit();

	}
	/***** FORM SUBMIT END *****/
	
	
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
		
	// Query to look up user
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.friend2, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(), u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.dniext1, u.dniext2, u.workStation, u.bajaDate, u.starCat, u.interview, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
	$user_id = $row['user_id'];
	$memberno = $row['memberno'];
	$registeredSince = $row['registeredSince'];
	$membertime = date("M y", strtotime($registeredSince));
	$userGroup = $row['userGroup'];
	$groupName = $row['groupName'];
	$groupDesc = $row['groupDesc'];
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$email = $row['email'];
	$day = $row['day'];
	$month = $row['month'];
	$year = $row['year'];
	$nationality = $row['nationality'];
	$gender = $row['gender'];
	$dni = $row['dni'];
	$street = $row['street'];
	$streetnumber = $row['streetnumber'];
	$flat = $row['flat'];
	$postcode = $row['postcode'];
	$city = $row['city'];
	$country = $row['country'];
	$telephone = $row['telephone'];
	$mconsumption = $row['mconsumption'];
	$usageType = $row['usageType'];
	$signupsource = $row['signupsource'];
	$cardid = $row['cardid'];
	$photoid = $row['photoid'];
	$docid = $row['docid'];
	$doorAccess = $row['doorAccess'];
	$friend = $row['friend'];
	$friend2 = $row['friend2'];
	$paidUntil = $row['paidUntil'];
	$adminComment = $row['adminComment'];
	$daysMember = $row['daysMember'];
	$form1 = $row['form1'];
	$form2 = $row['form2'];
	$dniscan = $row['dniscan'];
	$paymentWarning = $row['paymentWarning'];
	$paymentWarningDate = $row['paymentWarningDate'];
	$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
	$userCredit = $row['credit'];
	$banComment = $row['banComment'];
	$creditEligible = $row['creditEligible'];
	$discount = $row['discount'];
	$discountBar = $row['discountBar'];
	$photoext = $row['photoext'];
	$dniext1 = $row['dniext1'];
	$dniext2 = $row['dniext2'];
	$workStation = $row['workStation'];
	$bajaDate = date('d-m-y', strtotime($row['bajaDate']));
	$starCat = $row['starCat'];	
	$interview = $row['interview'];
	$exento = $row['exento'];
	
	if ($starCat == 1) {
   		$userStar = "<img src='images/star-yellow.png'/>";
	} else if ($starCat == 2) {
   		$userStar = "<img src='images/star-black.png' />";
	} else if ($starCat == 3) {
   		$userStar = "<img src='images/star-green.png' />";
	} else if ($starCat == 4) {
   		$userStar = "<img src='images/star-red.png' />";
	} else {
   		$userStar = "";
	}

	// Look up notes
	try
	{
		$results = $pdo3->prepare("SELECT noteid, notetime, userid, note, worker FROM usernotes WHERE userid = $user_id ORDER by notetime DESC");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($results->rowCount()) {
		$userNotes = $results->fetchAll();
	} else {
		$userNotes = '';
	}
	
	

// Calculate Age - only if Birthday exists

if ($day != 0) {
	$bdayraw = $day . "." . $month . "." . $year;
	$bday = new DateTime($bdayraw);
	$today = new DateTime(); // for testing purposes
	$diff = $today->diff($bday);
	$age = $diff->y;
	
	$birthday = date("d M Y", strtotime($bdayraw));
} else {
	$birthday = '';
}	

	$script = <<<EOD
    $(document).ready(function() {
		$("input[type='text']").keyup(function() {
		   	  $(this).val($(this).val().replace(/,/g, '.'));
		   	  $(this).val($(this).val().replace(' ', ''));
		   	  $(this).val($(this).val().replace(/[a-z]/g, ''));
		});
	});
EOD;
			
	
	pageStart($lang['discounts'], NULL, $script, "avalpage", "editdiscounts", $lang['discounts'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$topimg = "images/_$domain/members/$user_id.$photoext";
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

?>
<center><a href="profile.php?user_id=<?php echo $user_id; ?>" class='cta1nm'>&laquo; <?php echo $lang['title-profile']; ?> &laquo;</a></center>
<?php
	echo <<<EOD
	
 <div id='mainbox'>
<center><div class='topaval'>
  <center> <span class="profilepicholder" style="float: left; margin-right: 18px;" ><img class="profilepic" src="$topimg" width="143" />$highroller</span>


 <table style="display: inline-block; vertical-align: top; text-align: left;">
  <tr>
   <td class='biggerfont'><span class='firsttext'>#$memberno</span>&nbsp;&nbsp;<span class='secondtext'></span><br />
   <span class='nametext'>$first_name $last_name</span><br /> $groupName<br /></td>
  </tr>
  <tr>
   <td><strong></td>
  </tr>
 </table>
 </center>
</div></center><br />

EOD;		
?>
 
  <div class="clearfloat"></div>
  
  


   <form id="registerForm" action="" method="POST">

    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
    
  <div class="clearfloat"></div><br />
 <div id="detailedinfoD">

<center><span class='cta3' style='padding: 20px;'><?php echo $lang['dispensary']; ?></span></center><br />
<?php

	if ($_SESSION['userGroup'] < 2) {
		echo "<span>";
	} else {
		echo "<span style='visibility: hidden;'>";
	}
	
	?>
  
<center><h3 class='title'><?php echo $lang['general-discounts']; ?></h3><br /><br /></center>
  <table class='padded' style='width: 100%;'>
   <tr>
    <td><?php echo $lang['member-discountD']; ?></td>
    <td></td>
    <td></td>
    <td class='right'><input type='text' class="oneDigit defaultinput" name="discount" value="<?php echo $discount; ?>" /> % </td>
    <td></td>
    <td></td>
   </tr>
   <tr>
    <td colspan='6'>&nbsp;</td>
   </tr>
   <tr>
    <td colspan='6'><center><h3 class='title'><?php echo $lang['category-discounts']; ?></h3></center></td>
   </tr>
<?php

	// Default categories
	$selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = 1";
		try
		{
			$result = $pdo3->prepare("$selectCategoryDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCD = $result->fetch();
		$catDiscount = $rowCD['discount'];
		
		echo  <<<EOD
			<tr>
			 <td>{$lang['global-flowers']} (g.)</td>
			 <td></td>
		     <td></td>
			 <td class='right'><input type='text' class='oneDigit defaultinput' name='indCatDiscount[0][catDiscount]' value='{$catDiscount}' /> %
			     <input type='hidden' class='oneDigit defaultinput' name='indCatDiscount[0][catID]' value='1' /></td>
   		 	 <td></td>
    		 <td></td>
			</tr>
EOD;

	$selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = 2";
		try
		{
			$result = $pdo3->prepare("$selectCategoryDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCD = $result->fetch();
		$catDiscount = $rowCD['discount'];
		
		echo  <<<EOD
			<tr>
			 <td>{$lang['global-extracts']} (g.)</td>
			 <td></td>
			 <td></td>
			 <td class='right'><input type='text' class='oneDigit defaultinput' name='indCatDiscount[1][catDiscount]' value='{$catDiscount}' /> %
			     <input type='hidden' class='oneDigit' name='indCatDiscount[1][catID]' value='2' /></td>
    		 <td></td>
    		 <td></td>
			</tr>
EOD;
	

	// Select categories
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	$c = 2;
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$catName = $category['name'];
		$catType = $category['type'];
		
		if ($catType == 1) {
			$catT = '(g.)';
		} else {
			$catT = '(u.)';
		}
		
		// Look up discount for THIS category
		$selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectCategoryDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCD = $result->fetch();
			$catDiscount = $rowCD['discount'];

	
		
			echo  <<<EOD
				<tr>
				 <td>$catName $catT</td>
				 <td></td>
				 <td></td>
				 <td class='right'><input type='text' class='oneDigit defaultinput' name='indCatDiscount[{$c}][catDiscount]' value='{$catDiscount}' /> %
				     <input type='hidden' class='oneDigit' name='indCatDiscount[{$c}][catID]' value='{$categoryid}' /></td>
    		 	 <td></td>
    		 	 <td></td>
				</tr>
EOD;
		
		
			$c++;
	
		
	}
?>
   <tr>
    <td colspan='6'>&nbsp;</td>
   </tr>
   <tr>
    <td colspan='6'><center><h3 class='title'><?php echo $lang['product-discounts']; ?></h3></center></td>
   </tr>
   <tr>
    <td colspan='6'>&nbsp;</td>
   </tr>
<?php

	// First, select open purchases, loop through.
	// For each product, look up user id + discount in inddiscount table
	
	$selectPurchases = "SELECT g.name, g.breed2, p.category, p.purchaseid, p.salesPrice, p.growType FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 UNION ALL SELECT h.name, '' AS breed2, p.category, p.purchaseid, p.salesPrice, p.growType FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY salesPrice ASC";
		try
		{
			$resultsX = $pdo3->prepare("$selectPurchases");
			$resultsX->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
		$d = 0;
		while ($purchase = $resultsX->fetch()) {
			
		
	$name = $purchase['name'];
	$breed2 = $purchase['breed2'];
	$category = $purchase['category'];
	$purchaseid = $purchase['purchaseid'];
	$salesPrice = $purchase['salesPrice'];
	$growtype = $purchase['growType'];
	
	// Look up discount for THIS product
	$selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectPurchaseDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowD = $result->fetch();
		$prodDiscount = $rowD['discount'];
		$prodFijo = $rowD['fijo'];

	if ($prodDiscount != '' && $prodDiscount != 0) {
		$discPrice = number_format((1 - ($prodDiscount / 100)) * $salesPrice,2);
	} else if ($prodFijo != '' && $prodFijo != 0) {
		$discPrice = $prodFijo;
	} else {
		$discPrice = $salesPrice;
	}
	
	if ($breed2 = '') {
		$name = $name . " x " . $breed2;
	} else {
		$name = $name;
	}
	
	
	if ($category == 1) {
		
	// Look up growtype
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$growtype = $row['growtype'];
	
	if ($growtype == '') {
		
	} else {
		
	}
	
		
		// 30 must turn to 0,7
		// 30 / 100
		// 1 - result
		
		$flowerOutput .= <<<EOD
			<tr class='flowertable' style='display: none;'>
			 <td>$name $growtype</td>
			 <td class='right'>$salesPrice &euro;</td>
    		 <td class='right yellow'></td>
			 <td class='right'>
			  <input type='text' class='oneDigit defaultinput' name='indDiscount[{$d}][purchaseDiscount]' value='{$prodDiscount}' id='percentage$d' step='0.01' /> %
			  <span style='font-size: 12px;'><input type='text' class='specialInput' id='newPrice$d' step='0.01' value='($discPrice' readonly />&euro;)
			  </span>
			 </td>
    		 <td class='yellow'></td>
    		 <td>
    		  <input type='text' class='fourDigit defaultinput' id='fijo$d' name='indDiscount[{$d}][purchaseFijo]' value='$prodFijo' step='0.01' /> &euro;
    		  
			  <input type='hidden' class='oneDigit' name='indDiscount[{$d}][purchaseid]' value='{$purchaseid}' />
			  
			  <input type='hidden' class='oneDigit' id='origPrice$d' value='$salesPrice' />

<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#origPrice$d').val();
          var b = $('#percentage$d').val();
          var total = (1 - (b / 100)) * a;
          var roundedtotal = total.toFixed(2);
          $('#newPrice$d').val('(' + roundedtotal);
          $('#fijo$d').val('');
        }
        $('#percentage$d').bind('keypress keyup blur', compute);

   function compute2() {
          var a = $('#fijo$d').val();
          $('#percentage$d').val('');
          $('#newPrice$d').val('(' + a);
        }
        $('#fijo$d').bind('keypress keyup blur', compute2);
       
  }); // end ready
</script>

			  
    		 </td>
			</tr>
EOD;

	} else if ($category == 2) {
		$extractOutput .= <<<EOD
			<tr class='extracttable' style='display: none;'>
			 <td>$name</td>
			 <td class='right'>$salesPrice &euro;</td>
    		 <td class='right yellow'></td>
			 <td class='right'>
			  <input type='text' class='oneDigit defaultinput' name='indDiscount[{$d}][purchaseDiscount]' value='{$prodDiscount}' id='percentage$d' step='0.01' /> %
			  <span style='font-size: 12px;'><input type='text' class='specialInput' id='newPrice$d' step='0.01' value='($discPrice' readonly />&euro;)
			  </span>
			 </td>
    		 <td class='yellow'></td>
    		 <td>
    		  <input type='text' class='fourDigit defaultinput' id='fijo$d' name='indDiscount[{$d}][purchaseFijo]' value='$prodFijo' step='0.01' /> &euro;
    		  
			  <input type='hidden' class='oneDigit' name='indDiscount[{$d}][purchaseid]' value='{$purchaseid}' />
			  
			  <input type='hidden' class='oneDigit' id='origPrice$d' value='$salesPrice' />

<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#origPrice$d').val();
          var b = $('#percentage$d').val();
          var total = (1 - (b / 100)) * a;
          var roundedtotal = total.toFixed(2);
          $('#newPrice$d').val('(' + roundedtotal);
          $('#fijo$d').val('');
        }
        $('#percentage$d').bind('keypress keyup blur', compute);

   function compute2() {
          var a = $('#fijo$d').val();
          $('#percentage$d').val('');
          $('#newPrice$d').val('(' + a);
        }
        $('#fijo$d').bind('keypress keyup blur', compute2);
       
  }); // end ready
</script>

			  
    		 </td>
			</tr>
EOD;
	}
	
	$d++;
}

	// First, select categories
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by id ASC";
		try
		{
			$resultsCats = $pdo3->prepare("$selectCats");
			$resultsCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($category = $resultsCats->fetch()) {
		
		$categoryid = $category['id'];
		$catName = $category['name'];
		$catType = $category['type'];
		
		if ($catType == 1) {
			$catT = '(g.)';
		} else {
			$catT = '(u.)';
		}

		
		$catProd .= "<tr><td colspan='2'>&nbsp;</td></tr>";
		$catProd .= "<tr><td colspan='6' style='cursor: pointer;' id='expand$categoryid'><center><strong class='cta4nm' style='width: 350px;'>$catName $catT &raquo;</strong></center></td></tr>
	<script>
	$('#expand$categoryid').click(function () {
	$('.header$categoryid').toggle();
	$('.table$categoryid').toggle();
	});
	</script>
";
		if (${'title'.$categoryid} != 'set') {
	$catProd .=  <<<EOD
	<tr class='header$categoryid' style='display: none;'>
	 <td style='vertical-align: bottom;'><strong>Producto</strong></td>
	 <td style='vertical-align: bottom;' class='right'><strong>Precio</strong></td>
     <td></td>
	 <td style='vertical-align: bottom;' class='right'><strong>Descuento%</strong></td>
     <td class='center'>&nbsp;&nbsp;&nbsp; o </td>
	 <td style='vertical-align: bottom;'><strong>Precio fijo</strong></td>	 
	</tr>
EOD;
}

${'title'.$categoryid} = 'set';


		// For each category, loop through all open purchases
		$selectProducts = "SELECT g.name, p.purchaseid, p.salesPrice FROM products g, purchases p WHERE p.category = $categoryid AND p.productid = g.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY salesPrice ASC";
		try
		{
			$results = $pdo3->prepare("$selectProducts");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($indProduct = $results->fetch()) {
		
			$name = $indProduct['name'];
			$purchaseid = $indProduct['purchaseid'];
			$salesPrice = $indProduct['salesPrice'];
	
			// Look up discount for THIS product
			$selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectPurchaseDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowD = $result->fetch();
				$prodDiscount = $rowD['discount'];
				$prodFijo = $rowD['fijo'];
		
			if ($prodDiscount != '' && $prodDiscount != 0) {
				$discPrice = number_format((1 - ($prodDiscount / 100)) * $salesPrice,2);
			} else if ($prodFijo != '' && $prodFijo != 0) {
				$discPrice = $prodFijo;
						} else {
				$discPrice = $salesPrice;
			}

			$catProd .=  <<<EOD
							<tr class='table$categoryid' style='display: none;'>
			 <td>$name $growtype</td>
			 <td class='right'>$salesPrice &euro;</td>
    		 <td class='right yellow'></td>
			 <td class='right'>
			  <input type='text' class='oneDigit defaultinput' name='indDiscount[{$d}][purchaseDiscount]' value='{$prodDiscount}' id='percentage$d' step='0.01' /> %
			  <span style='font-size: 12px;'><input type='text' class='specialInput' id='newPrice$d' step='0.01' value='($discPrice' readonly />&euro;)
			  </span>
			 </td>
    		 <td class='yellow'></td>
    		 <td>
    		  <input type='text' class='fourDigit defaultinput' id='fijo$d' name='indDiscount[{$d}][purchaseFijo]' value='$prodFijo' step='0.01' /> &euro;
    		  
			  <input type='hidden' class='oneDigit' name='indDiscount[{$d}][purchaseid]' value='{$purchaseid}' />
			  
			  <input type='hidden' class='oneDigit' id='origPrice$d' value='$salesPrice' />

<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#origPrice$d').val();
          var b = $('#percentage$d').val();
          var total = (1 - (b / 100)) * a;
          var roundedtotal = total.toFixed(2);
          $('#newPrice$d').val('(' + roundedtotal);
          $('#fijo$d').val('');
        }
        $('#percentage$d').bind('keypress keyup blur', compute);

   function compute2() {
          var a = $('#fijo$d').val();
          $('#percentage$d').val('');
          $('#newPrice$d').val('(' + a);
        }
        $('#fijo$d').bind('keypress keyup blur', compute2);
       
  }); // end ready
</script>

			  
    		 </td>
			</tr>
EOD;
		
		
			$d++;
		}
	
		
	}

	echo "<tr><td colspan='6' class='yellow' style='cursor: pointer;' id='flowerexpand'><center><strong class='cta4nm' style='width: 350px;' style='width: 350px;'>{$lang['global-flowerscaps']} (g.) &raquo;</strong></center></td></tr>
	<script>
	$('#flowerexpand').click(function () {
	$('#flowerheader').toggle();
	$('.flowertable').toggle();
	});
	</script>
";
	echo <<<EOD
	
	<tr style='display: none;' id='flowerheader'>
	 <td style='vertical-align: bottom;'><strong>Producto</strong></td>
	 <td style='vertical-align: bottom;' class='right'><strong>Precio</strong></td>
     <td></td>
	 <td style='vertical-align: bottom;' class='right'><strong>Descuento%</strong></td>
     <td class='center'>&nbsp;&nbsp;&nbsp; o </td>
	 <td style='vertical-align: bottom;'><strong>Precio fijo</strong></td>	 
	</tr>
	
EOD;
	echo $flowerOutput;
	echo "<tr><td colspan='6'>&nbsp;</td></tr>";
	echo "<tr><td colspan='6' class='yellow' style='cursor: pointer;' id='extractexpand'><center><strong class='cta4nm' style='width: 350px;'>{$lang['global-extractscaps']} (g.) &raquo;</strong></center></td></tr>
	<script>
	$('#extractexpand').click(function () {
	$('#extractheader').toggle();
	$('.extracttable').toggle();
	});
	</script>
";
	echo <<<EOD
	<tr style='display: none;' id='extractheader'>
	 <td style='vertical-align: bottom;'><strong>Producto</strong></td>
	 <td style='vertical-align: bottom;'><strong>Precio</strong></td>
     <td></td>
	 <td style='vertical-align: bottom;'><strong>Descuento</strong></td>
     <td class='center'>&nbsp;&nbsp;&nbsp; o </td>
	 <td style='vertical-align: bottom;'><strong>Precio nuevo</strong></td>	 
	</tr>
EOD;
	echo $extractOutput;
	echo $catProd;
	echo "</table>";

	echo "</span>";
 ?>
  
 </div> <!-- END DETAILEDINFO -->
 
 
 
 
 
 
 
 
 
 
 <br /><br />
 
 
 
 
 
 
 
 
 
 
 <div id="statisticsD">

<center><span class='cta3' style='padding: 20px;'><?php echo $lang['bar']; ?></span></center><br />
<?php

	if ($_SESSION['userGroup'] < 2) {
		echo "<span>";
	} else {
		echo "<span style='visibility: hidden;'>";
	}
	
	?>
  
<center><h3 class='title'><?php echo $lang['general-discounts']; ?></h3><br /><br /></center>
  <table class='padded' style='width: 100%;'>
   <tr>
    <td><?php echo $lang['member-discountBar']; ?></td>
    <td></td>
    <td></td>
    <td class='right'><input type='text' class="oneDigit defaultinput" name="discountBar" value="<?php echo $discountBar; ?>" /> % </td>
	<td></td>
	<td></td>
   </tr>
   <tr>
    <td colspan='6'>&nbsp;</td>
   </tr>
   <tr>
    <td colspan='6'><center><h3 class='title'><?php echo $lang['category-discounts']; ?></h3></center></td>
   </tr>
<?php

	// Select categories
	$selectCatsB = "SELECT id, name from b_categories ORDER by id ASC";
		try
		{
			$resultsCatsB = $pdo3->prepare("$selectCatsB");
			$resultsCatsB->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	$e = 0;
		while ($categoryB = $resultsCatsB->fetch()) {
			
		$categoryidB = $categoryB['id'];
		$catNameB = $categoryB['name'];
		
		// Look up discount for THIS category
		$selectCategoryDiscountB = "SELECT discount FROM b_catdiscounts WHERE user_id = $user_id AND categoryid = $categoryidB";
		try
		{
			$result = $pdo3->prepare("$selectCategoryDiscountB");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCDB = $result->fetch();
			$catDiscountB = $rowCDB['discount'];

	
		
			echo  <<<EOD
				<tr>
				 <td>$catNameB</td>
				 <td></td>
				 <td></td>
				 <td class='right'><input type='text' class='oneDigit defaultinput' name='indCatDiscountB[{$e}][catDiscountB]' value='{$catDiscountB}' /> %
				     <input type='hidden' class='oneDigit' name='indCatDiscountB[{$e}][catIDB]' value='{$categoryidB}' /></td>
				 <td></td>
				 <td></td>
				</tr>
EOD;
		
		
			$e++;
	
		
	}
?>
   <tr>
    <td colspan='6'>&nbsp;</td>
   </tr>
   <tr>
    <td colspan='6'><center><h3 class='title'><?php echo $lang['product-discounts']; ?></h3></center></td>
   </tr>
<?php
	// First, select categories
	$selectCatsB = "SELECT id, name from b_categories ORDER by id ASC";
		try
		{
			$resultsCatsB = $pdo3->prepare("$selectCatsB");
			$resultsCatsB->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		$f = 0;
		while ($categoryB = $resultsCatsB->fetch()) {
		
		$categoryidB = $categoryB['id'];
		$catNameB = $categoryB['name'];
		
		$catProdB .= "<tr><td colspan='2'>&nbsp;</td></tr>";
		$catProdB .= "<tr><td colspan='6' style='cursor: pointer;' id='expandB$categoryidB'><center><strong class='cta4nm' style='width: 350px;'>$catNameB &raquo;</strong></center></td></tr>
	<script>
	$('#expandB$categoryidB').click(function () {
	$('.headerB$categoryidB').toggle();
	$('.tableB$categoryidB').toggle();
	});
	</script>
";
		if (${'titleB'.$categoryidB} != 'set') {
	$catProdB .=  <<<EOD
	<tr class='headerB$categoryidB' style='display: none;'>
	 <td style='vertical-align: bottom;'><strong>Producto</strong></td>
	 <td style='vertical-align: bottom;' class='right'><strong>Precio</strong></td>
     <td></td>
	 <td style='vertical-align: bottom;' class='right'><strong>Descuento%</strong></td>
     <td class='center'>&nbsp;&nbsp;&nbsp; o </td>
	 <td style='vertical-align: bottom;'><strong>Precio fijo</strong></td>	 
	</tr>
EOD;
}
${'title'.$categoryid} = 'set';

		// For each category, loop through all open purchases
		$selectProductsB = "SELECT g.name, p.purchaseid, p.salesPrice, p.purchaseQuantity FROM b_products g, b_purchases p WHERE p.category = $categoryidB AND p.productid = g.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY salesPrice ASC";
		try
		{
			$results = $pdo3->prepare("$selectProductsB");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($indProductB = $results->fetch()) {
		
			$nameB = $indProductB['name'];
			$purchaseidB = $indProductB['purchaseid'];
			$salesPriceB = $indProductB['salesPrice'];
			
			$salesPriceB = number_format($salesPriceB,2);
	
			// Look up discount for THIS product
			$selectPurchaseDiscountB = "SELECT discount, fijo FROM b_inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseidB";
		try
		{
			$result = $pdo3->prepare("$selectPurchaseDiscountB");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowDB = $result->fetch();
				$prodDiscountB = $rowDB['discount'];
				$prodFijoB = $rowDB['fijo'];
		
			if ($prodDiscountB != '' && $prodDiscountB != 0) {
				$discPriceB = number_format((1 - ($prodDiscountB / 100)) * $salesPriceB,2);
			} else if ($prodFijoB != '' && $prodFijoB != 0) {
				$discPriceB = $prodFijoB;
			} else {
				$discPriceB = $salesPriceB;
			}
			
			$catProdB .=  <<<EOD
							<tr class='tableB$categoryidB' style='display: none;'>
			 <td>$nameB</td>
			 <td class='right'>$salesPriceB &euro;</td>
    		 <td class='right yellow'></td>
			 <td class='right'>
			  <input type='text' class='oneDigit defaultinput' name='indDiscountB[{$f}][purchaseDiscountB]' value='{$prodDiscountB}' id='percentageB$f' step='0.01' /> %
			  <span style='font-size: 12px;'><input type='text' class='specialInput' id='newPriceB$f' step='0.01' value='($discPriceB' readonly />&euro;)
			  </span>
			 </td>
    		 <td class='yellow'></td>
    		 <td>
    		  <input type='text' class='fourDigit defaultinput' id='fijoB$f' name='indDiscountB[{$f}][purchaseFijoB]' value='$prodFijoB' step='0.01' /> &euro;
    		  
			  <input type='hidden' class='oneDigit' name='indDiscountB[{$f}][purchaseidB]' value='{$purchaseidB}' />
			  
			  <input type='hidden' class='oneDigit' id='origPriceB$f' value='$salesPriceB' />

<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#origPriceB$f').val();
          var b = $('#percentageB$f').val();
          var total = (1 - (b / 100)) * a;
          var roundedtotal = total.toFixed(2);
          $('#newPriceB$f').val('(' + roundedtotal);
          $('#fijoB$f').val('');
        }
        $('#percentageB$f').bind('keypress keyup blur', compute);

   function compute2() {
          var a = $('#fijoB$f').val();
          $('#percentageB$f').val('');
          $('#newPriceB$f').val('(' + a);
        }
        $('#fijoB$f').bind('keypress keyup blur', compute2);
       
  }); // end ready
</script>

			  
    		 </td>
			</tr>
EOD;
		
			$f++;
		}
	
		
	}

	echo $catProdB;
	echo "</table>";

 ?>
 </div>
 <div class="clearfloat"></div><br />
 <br /><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button></center>

   </form>
  </div> <!-- END PROFILEWRAPPER -->

<?php displayFooter(); ?>