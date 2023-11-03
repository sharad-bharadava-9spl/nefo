<?php
session_start();
//$accessLevel = '1';

// Authenticate & authorize
authorizeUser($accessLevel);

// Did this page re-submit with a form? If so, check & store details
if ($_POST) {
    $happy_hour_id = $_POST['happy_hour_id'];
    $discount = $_POST['discount'];
    $discountBar = $_POST['discountBar'];
    $discountName = $_POST['discountName'];
    $discountDate = $_POST['discountDate'];
    $fromTime = $_POST['fromTime'];
    $toTime = $_POST['toTime'];
    $checkMidTime = explode(":",$toTime);
    if(count($checkMidTime) == 2 && isset($checkMidTime[1]) && $checkMidTime[1] == 59){
	    $toTime .= ':59';
    }
    $created = date("Y-m-d H:i:s");

    if($happy_hour_id){
        $hHDiscount = "SELECT id,discount_date FROM global_happy_hour_discounts where id = $happy_hour_id";
        try
        {
            $result = $pdo3->prepare("$hHDiscount");
            $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $rs = $result->fetch();
        $checkOther = 1;
        if(isset($rs['discount_date']) && $rs['discount_date'] == 'Every day'){
            $checkOther = 0;
        }

        if($checkOther){
            $hHDiscount = "SELECT id,discount_name FROM global_happy_hour_discounts where discount_date = 'Every day' and ((time_from <= '".$fromTime.":00' and time_to > '".$fromTime.":00') or (time_from <= '".$toTime.":00' and time_to > '".$toTime.":00'))";
            try
            {
                $result = $pdo3->prepare("$hHDiscount");
                $result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $rs = $result->fetch();

            if(isset($rs['id'])){
                $link = '<a href=global-discounts.php?type=hhd&action=add&id='.$rs['id'].' class="bluetext">edit the '.$rs['discount_name'].'</a>';
                $errorMsg = str_replace('[link]', $link , $lang['duplicate-discount-error']);
                $_SESSION['errorMessage'] = $errorMsg;
                header("Location: global-discounts.php?type=hhd&action=add");
                exit();
            }
        }
    }
    // DISPENSARY DISCOUNTS
    // First, delete from inddiscounts for this user_id, then set new values
    $deleteDiscounts = "DELETE FROM global_happy_hour_discounts WHERE id = $happy_hour_id";
    try {
        if(!empty($happy_hour_id)){
            $result = $pdo3->prepare("$deleteDiscounts")->execute();
        }
        $addDiscount = sprintf("INSERT INTO global_happy_hour_discounts (discount_name, discount_date,time_from,time_to,discount,discount_bar, created) VALUES ('%s', '%s', '%s', '%s', '%d', '%d', '%s');", $discountName, $discountDate, $fromTime, $toTime, $discount, $discountBar, $created);
        try {
            $result = $pdo3->prepare("$addDiscount")->execute();
            $happy_hour_id = $pdo3->lastInsertId();
        } catch (PDOException $e) {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
    } catch (PDOException $e) {
        $error = 'Error fetching user: ' . $e->getMessage();
        echo $error;
        exit();
    }
    foreach ($_POST['indCatDiscount'] as $catDiscount) {

        $catD = $catDiscount['catDiscount'];
        $catID = $catDiscount['catID'];


        if ($catD > 100) {
            $catD = 100;
        }

        if ($catD != '') {

            $addDiscount = sprintf("INSERT INTO catdiscounts (happy_hour_id, categoryid, discount) VALUES ('%d', '%d', '%f');", $happy_hour_id, $catID, $catD);
            try {
                $result = $pdo3->prepare("$addDiscount")->execute();
            } catch (PDOException $e) {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
        }
    }

    $deleteDiscounts = "DELETE FROM inddiscounts WHERE happy_hour_id = $happy_hour_id";
    try {
        if(!empty($happy_hour_id)){
            $result = $pdo3->prepare("$deleteDiscounts")->execute();
        }
    } catch (PDOException $e) {
        $error = 'Error fetching user: ' . $e->getMessage();
        echo $error;
        exit();
    }

    foreach ($_POST['indDiscount'] as $indDiscount) {

        $indD = $indDiscount['purchaseDiscount'];
        $indFijo = $indDiscount['purchaseFijo'];
        $purchaseid = $indDiscount['purchaseid'];

        if ($indD > 100) {
            $indD = 100;
        }

        if ($indD != '') {

            $addDiscount = sprintf("INSERT INTO inddiscounts (happy_hour_id, purchaseid, discount) VALUES ('%d', '%d', '%f');", $happy_hour_id, $purchaseid, $indD);
            try {
                $result = $pdo3->prepare("$addDiscount")->execute();
            } catch (PDOException $e) {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
        }

        if ($indFijo != '') {

            $addDiscount = sprintf("INSERT INTO inddiscounts (happy_hour_id, purchaseid, fijo) VALUES ('%d', '%d', '%f');", $happy_hour_id, $purchaseid, $indFijo);
            try {
                $result = $pdo3->prepare("$addDiscount")->execute();
            } catch (PDOException $e) {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
        }
    }


    // BAR DISCOUNTS
    // First, delete from inddiscounts for this user_id, then set new values
    $deleteDiscountsB = "DELETE FROM b_catdiscounts WHERE happy_hour_id = $happy_hour_id";
    try {
        if(!empty($happy_hour_id)){
            $result = $pdo3->prepare("$deleteDiscountsB")->execute();
        }
    } catch (PDOException $e) {
        $error = 'Error fetching user: ' . $e->getMessage();
        echo $error;
        exit();
    }

    foreach ($_POST['indCatDiscountB'] as $catDiscountB) {

        $catDB = $catDiscountB['catDiscountB'];
        $catIDB = $catDiscountB['catIDB'];


        if ($catDB > 100) {
            $catDB = 100;
        }

        if ($catDB != '') {

            $addDiscountB = sprintf("INSERT INTO b_catdiscounts (happy_hour_id, categoryid, discount) VALUES ('%d', '%d', '%f');", $happy_hour_id, $catIDB, $catDB);
            try {
                $result = $pdo3->prepare("$addDiscountB")->execute();
            } catch (PDOException $e) {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
        }
    }

    $deleteDiscountsB = "DELETE FROM b_inddiscounts WHERE happy_hour_id = $happy_hour_id";
    try {
        if(!empty($happy_hour_id)){
            $result = $pdo3->prepare("$deleteDiscountsB")->execute();
        }
    } catch (PDOException $e) {
        $error = 'Error fetching user: ' . $e->getMessage();
        echo $error;
        exit();
    }

    foreach ($_POST['indDiscountB'] as $indDiscountB) {

        $indDB = $indDiscountB['purchaseDiscountB'];
        $purchaseidB = $indDiscountB['purchaseidB'];
        $indFijoB = $indDiscountB['purchaseFijoB'];

        if ($indDB > 100) {
            $indDB = 100;
        }

        if ($indDB != '') {

            $addDiscountB = sprintf("INSERT INTO b_inddiscounts (happy_hour_id, purchaseid, discount) VALUES ('%d', '%d', '%f');", $happy_hour_id, $purchaseidB, $indDB);
            try {
                $result = $pdo3->prepare("$addDiscountB")->execute();
            } catch (PDOException $e) {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
        }

        if ($indFijoB != '') {

            $addDiscountB = sprintf("INSERT INTO b_inddiscounts (happy_hour_id, purchaseid, fijo) VALUES ('%d', '%d', '%f');", $happy_hour_id, $purchaseidB, $indFijoB);
            try {
                $result = $pdo3->prepare("$addDiscountB")->execute();
            } catch (PDOException $e) {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
        }
    }


//    $updateUser = sprintf("UPDATE users SET discount = '%d', discountBar = '%d' WHERE user_id = '%d';", $discount, $discountBar, $user_id
//    );
//
//    try {
//        $result = $pdo3->prepare("$updateUser")->execute();
//    } catch (PDOException $e) {
//        $error = 'Error fetching user: ' . $e->getMessage();
//        echo $error;
//        exit();
//    }
    // On success: redirect.
    $_SESSION['successMessage'] = $lang['discounts-applied'];
    header("Location: global-discounts.php?type=hhd");
    exit();
}
/* * *** FORM SUBMIT END **** */


$discount = $discountBar = $userStar = $discountName = $discountDate = $fromTime = $toTime ="";
// Query to look up user
if(isset($_GET['id'])){
    $happy_hour_id = $_GET['id'];
    $userDetails = "SELECT * FROM global_happy_hour_discounts WHERE id = $happy_hour_id";
    try {
        $result = $pdo3->prepare("$userDetails");
        $result->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching user: ' . $e->getMessage();
        echo $error;
        exit();
    }

    $row = $result->fetch();
    $discount = $row['discount'];
    $discountBar = $row['discount_bar'];
    $discountName = $row['discount_name'];
    $discountDate = $row['discount_date'];
    $fromTime = $row['time_from'];
    $toTime = $row['time_to'];
}


pageStart($lang['global-discounts'], NULL, $validationScript, "pprofile", "global-discounts dev-align-center", $lang['happy-hour-discounts'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

include "global-discount-menu.php";
?>

<div id="mainbox">
    <!-- END OVERVIEW -->
    <div class="clearfloat"></div>
    <form id="happyHourDiscount" action="" method="POST">

        <input type="hidden" name="happy_hour_id" value="<?php echo $happy_hour_id; ?>" />

        <div class="clearfloat"></div><br />
        <div id="detailedinfoD">

            <center>
                <span class="smallgreen"><?php echo $lang['discount-name'] ?></span> : <input type="text" id="discountName" name="discountName" autocomplete="off" class="sixDigit defaultinput" required="required" value="<?php echo $discountName; ?>"/>
                 <span class="smallgreen"><?php echo $lang['discount-date'] ?> :</span>
                <select name="discountDate" class="defaultinput" style="width: 100px;">
                    <option value="Every day" <?php echo $discountDate == 'Everyday' ? 'selected' : ''; ?>>Every day</option>
                    <option value="Mondays"  <?php echo $discountDate == 'Mondays' ? 'selected' : ''; ?>>Mondays</option>
                    <option value="Tuesdays"  <?php echo $discountDate == 'Tuesdays' ? 'selected' : ''; ?>>Tuesdays</option>
                    <option value="Wednesdays"  <?php echo $discountDate == 'Wednesdays' ? 'selected' : ''; ?>>Wednesdays</option>
                    <option value="Thursdays"  <?php echo $discountDate == 'Thursdays' ? 'selected' : ''; ?>>Thursdays</option>
                    <option value="Fridays"  <?php echo $discountDate == 'Fridays' ? 'selected' : ''; ?>>Fridays</option>
                    <option value="Saturdays"  <?php echo $discountDate == 'Saturdays' ? 'selected' : ''; ?>>Saturdays</option>
                    <option value="Sundays"  <?php echo $discountDate == 'Sundays' ? 'selected' : ''; ?>>Sundays</option>
                </select>
                <br>
<!--                <input type="text" id="datepicker" name="discountDate" autocomplete="nope" class="sixDigit" required="required" value=""/>-->
                <span class="smallgreen"><?php echo $lang['from'] ?></span> : <input type="text" id="from" name="fromTime" autocomplete="off" class="sixDigit defaultinput" required="required" value="<?php echo $fromTime; ?>" readonly/>
                <span class="smallgreen"><?php echo $lang['to'] ?></span> : <input type="text" id="to" name="toTime" autocomplete="off" class="sixDigit defaultinput" required="required" value="<?php echo $toTime;?>" readonly/>

                <br /><br />
                <span class="cta3" style='padding: 20px;'><?php echo $lang['discountsC'] . " " . $lang['dispensary']; ?></span>
            </center><br />
            <?php
            if ($_SESSION['userGroup'] < 2) {
                echo "<span>";
            } else {
                echo "<span style='visibility: hidden;'>";
            }
            ?>

           
            <table class='padded' style='width: 100%;'>
                 <tr> 
                    <td colspan='6'><center><h3 class="title"><?php echo $lang['general-discounts']; ?></h3><br /></center></td>
                 </tr>
                <tr>
                    <td><?php echo $lang['member-discountD']; ?></td>
                    <td></td>
                    <td></td>
                    <td class='right'><input type="number" class="oneDigit defaultinput" name="discount" value="<?php echo $discount; ?>" /> % </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan='6'><center><h3 class="title"><?php echo $lang['category-discounts']; ?></h3></center></td>
                </tr>
                <?php
// Default categories
                $catDiscount = '';
                if($happy_hour_id){
                    $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = 1 and happy_hour_id = ".$happy_hour_id;
                    try {
                        $result = $pdo3->prepare("$selectCategoryDiscount");
                        $result->execute();
                    } catch (PDOException $e) {
                        $error = 'Error fetching user: ' . $e->getMessage();
                        echo $error;
                        exit();
                    }

                    $rowCD = $result->fetch();
                    $catDiscount = $rowCD['discount'];
                }    
                echo <<<EOD
			<tr>
			 <td>{$lang['global-flowers']} (g.)</td>
			 <td></td>
		     <td></td>
			 <td class='right'><input type='number' class='oneDigit defaultinput' name='indCatDiscount[0][catDiscount]' value='{$catDiscount}' /> %
			     <input type='hidden' class='oneDigit' name='indCatDiscount[0][catID]' value='1' /></td>
   		 	 <td></td>
    		 <td></td>
			</tr>
EOD;

                $catDiscount = '';
                if($happy_hour_id){
                    $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = 2 and happy_hour_id = ".$happy_hour_id;
                    try {
                        $result = $pdo3->prepare("$selectCategoryDiscount");
                        $result->execute();
                    } catch (PDOException $e) {
                        $error = 'Error fetching user: ' . $e->getMessage();
                        echo $error;
                        exit();
                    }
                    $rowCD = $result->fetch();
                    $catDiscount = $rowCD['discount'];
                }
                echo <<<EOD
			<tr>
			 <td>{$lang['global-extracts']} (g.)</td>
			 <td></td>
			 <td></td>
			 <td class='right'><input type='number' class='oneDigit defaultinput' name='indCatDiscount[1][catDiscount]' value='{$catDiscount}' /> %
			     <input type='hidden' class='oneDigit' name='indCatDiscount[1][catID]' value='2' /></td>
    		 <td></td>
    		 <td></td>
			</tr>
EOD;


// Select categories
                $selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by id ASC";
                try {
                    $results = $pdo3->prepare("$selectCats");
                    $results->execute();
                } catch (PDOException $e) {
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
                    $catDiscount = '';
                    if($happy_hour_id){
                        $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = ".$categoryid." and happy_hour_id = ".$happy_hour_id;
                        try {
                            $result = $pdo3->prepare("$selectCategoryDiscount");
                            $result->execute();
                        } catch (PDOException $e) {
                            $error = 'Error fetching user: ' . $e->getMessage();
                            echo $error;
                            exit();
                        }

                        $rowCD = $result->fetch();
                        $catDiscount = $rowCD['discount'];
                    }

                    echo <<<EOD
				<tr>
				 <td>$catName $catT</td>
				 <td></td>
				 <td></td>
				 <td class='right'><input type='number' class='oneDigit defaultinput' name='indCatDiscount[{$c}][catDiscount]' value='{$catDiscount}' /> %
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
                    <td colspan='6'><center><h3 class="title"><?php echo $lang['product-discounts']; ?></h3></center></td>
                </tr>
                <?php
                // First, select open purchases, loop through.
                // For each product, look up user id + discount in inddiscount table

                $selectPurchases = "SELECT g.name, g.breed2, p.category, p.purchaseid, p.salesPrice, p.growType FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 UNION ALL SELECT h.name, '' AS breed2, p.category, p.purchaseid, p.salesPrice, p.growType FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY salesPrice ASC";
                try {
                    $resultsX = $pdo3->prepare("$selectPurchases");
                    $resultsX->execute();
                } catch (PDOException $e) {
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
                    $prodDiscount = $prodFijo = "";
                    if($happy_hour_id){
                        $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE purchaseid = ".$purchaseid." and happy_hour_id = ".$happy_hour_id;
                        try {
                            $result = $pdo3->prepare("$selectPurchaseDiscount");
                            $result->execute();
                        } catch (PDOException $e) {
                            $error = 'Error fetching user: ' . $e->getMessage();
                            echo $error;
                            exit();
                        }
                        $rowD = $result->fetch();
                        $prodDiscount = $rowD['discount'];
                        $prodFijo = $rowD['fijo'];
                    }

                    if ($prodDiscount != '' && $prodDiscount != 0) {
                        $discPrice = number_format((1 - ($prodDiscount / 100)) * $salesPrice, 2);
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
                        try {
                            $result = $pdo3->prepare("$growDetails");
                            $result->execute();
                        } catch (PDOException $e) {
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
			 <td class='right'>$salesPrice {$_SESSION['currencyoperator']}</td>
    		 <td class='right yellow'></td>
			 <td class='right'>
			  <input type='number' class='oneDigit defaultinput' name='indDiscount[{$d}][purchaseDiscount]' value='{$prodDiscount}' id='percentage$d' step='0.01' /> %
			  <span style='font-size: 12px;'><input type='text' class='specialInput' id='newPrice$d' step='0.01' value='($discPrice' readonly /> {$_SESSION['currencyoperator']})
			  </span>
			 </td>
    		 <td class='yellow'></td>
    		 <td>
    		  <input type='number' class='fourDigit defaultinput' id='fijo$d' name='indDiscount[{$d}][purchaseFijo]' value='$prodFijo' step='0.01' /> {$_SESSION['currencyoperator']}
    		  
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
			 <td class='right'>$salesPrice {$_SESSION['currencyoperator']}</td>
    		 <td class='right yellow'></td>
			 <td class='right'>
			  <input type='number' class='oneDigit defaultinput' name='indDiscount[{$d}][purchaseDiscount]' value='{$prodDiscount}' id='percentage$d' step='0.01' /> %
			  <span style='font-size: 12px;'><input type='text' class='specialInput' id='newPrice$d' step='0.01' value='($discPrice' readonly />{$_SESSION['currencyoperator']})
			  </span>
			 </td>
    		 <td class='yellow'></td>
    		 <td>
    		  <input type='number' class='fourDigit defaultinput' id='fijo$d' name='indDiscount[{$d}][purchaseFijo]' value='$prodFijo' step='0.01' /> {$_SESSION['currencyoperator']}
    		  
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
                try {
                    $resultsCats = $pdo3->prepare("$selectCats");
                    $resultsCats->execute();
                } catch (PDOException $e) {
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
                    $catProd .= "<tr><td colspan='6' class='yellow' style='cursor: pointer;' id='expand$categoryid'><center><strong class='cta4nm' style='width: 350px;'>$catName $catT &raquo;</strong></center></td></tr>
	<script>
	$('#expand$categoryid').click(function () {
	$('.header$categoryid').toggle();
	$('.table$categoryid').toggle();
	});
	</script>
";
                    if (${'title' . $categoryid} != 'set') {
                        $catProd .= <<<EOD
	<tr class='header$categoryid' style='display: none;'>
	 <th style='vertical-align: bottom;'><strong>Producto</strong></th>
	 <th style='vertical-align: bottom;' class='right'><strong>Precio</strong></th>
     <th></th>
	 <th style='vertical-align: bottom;' class='right'><strong>Descuento%</strong></th>
     <th class='center'>&nbsp;&nbsp;&nbsp; o </th>
	 <th style='vertical-align: bottom;'><strong>Precio fijo</strong></th>	 
	</tr>
EOD;
                    }

                    ${'title' . $categoryid} = 'set';

                    // For each category, loop through all open purchases
                    $selectProducts = "SELECT g.name, p.purchaseid, p.salesPrice FROM products g, purchases p WHERE p.category = $categoryid AND p.productid = g.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY salesPrice ASC";
                    try {
                        $results = $pdo3->prepare("$selectProducts");
                        $results->execute();
                    } catch (PDOException $e) {
                        $error = 'Error fetching user: ' . $e->getMessage();
                        echo $error;
                        exit();
                    }

                    while ($indProduct = $results->fetch()) {

                        $name = $indProduct['name'];
                        $purchaseid = $indProduct['purchaseid'];
                        $salesPrice = $indProduct['salesPrice'];

                        // Look up discount for THIS product
                        $prodDiscount = $prodFijo = "";
                        if($happy_hour_id){
                            $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE purchaseid = ".$purchaseid." and happy_hour_id = ".$happy_hour_id;
                            try {
                                $result = $pdo3->prepare("$selectPurchaseDiscount");
                                $result->execute();
                            } catch (PDOException $e) {
                                $error = 'Error fetching user: ' . $e->getMessage();
                                echo $error;
                                exit();
                            }

                            $rowD = $result->fetch();
                            $prodDiscount = $rowD['discount'];
                            $prodFijo = $rowD['fijo'];
                        }

                        if ($prodDiscount != '' && $prodDiscount != 0) {
                            $discPrice = number_format((1 - ($prodDiscount / 100)) * $salesPrice, 2);
                        } else if ($prodFijo != '' && $prodFijo != 0) {
                            $discPrice = $prodFijo;
                        } else {
                            $discPrice = $salesPrice;
                        }

                        $catProd .= <<<EOD
							<tr class='table$categoryid' style='display: none;'>
			 <td>$name $growtype</td>
			 <td class='right'>$salesPrice {$_SESSION['currencyoperator']}</td>
    		 <td class='right yellow'></td>
			 <td class='right'>
			  <input type='number' class='oneDigit defaultinput' name='indDiscount[{$d}][purchaseDiscount]' value='{$prodDiscount}' id='percentage$d' step='0.01' /> %
			  <span style='font-size: 12px;'><input type='text' class='specialInput' id='newPrice$d' step='0.01' value='($discPrice' readonly />{$_SESSION['currencyoperator']})
			  </span>
			 </td>
    		 <td class='yellow'></td>
    		 <td>
    		  <input type='number' class='fourDigit defaultinput' id='fijo$d' name='indDiscount[{$d}][purchaseFijo]' value='$prodFijo' step='0.01' /> {$_SESSION['currencyoperator']}
    		  
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

                echo "<tr><td colspan='6' class='yellow' style='cursor: pointer;' id='flowerexpand'><center><strong class='cta4nm' style='width: 350px;'>{$lang['global-flowerscaps']} (g.) &raquo;</strong></center></td></tr>
	<script>
	$('#flowerexpand').click(function () {
	$('#flowerheader').toggle();
	$('.flowertable').toggle();
	});
	</script>
";
                echo <<<EOD
	
	<tr style='display: none;' id='flowerheader'>
	 <th style='vertical-align: bottom;'><strong>Producto</strong></th>
	 <th style='vertical-align: bottom;' class='right'><strong>Precio</strong></th>
     <th></th>
	 <th style='vertical-align: bottom;' class='right'><strong>Descuento%</strong></th>
     <th class='center'>&nbsp;&nbsp;&nbsp; o </th>
	 <th style='vertical-align: bottom;'><strong>Precio fijo</strong></th>	 
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
	 <th style='vertical-align: bottom;'><strong>Producto</strong></th>
	 <th style='vertical-align: bottom;'><strong>Precio</strong></th>
     <th></th>
	 <th style='vertical-align: bottom;'><strong>Descuento</strong></th>
     <th class='center'>&nbsp;&nbsp;&nbsp; o </th>
	 <th style='vertical-align: bottom;'><strong>Precio nuevo</strong></th>	 
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

            <center><span class="cta3" style="padding: 20px;"><?php echo $lang['discountsC'] . " " . $lang['bar']; ?></span></center><br />
            <?php
            if ($_SESSION['userGroup'] < 2) {
                echo "<span>";
            } else {
                echo "<span style='visibility: hidden;'>";
            }
            ?>

            
            <table class='padded' style='width: 100%;'>
                <tr>
                    <td colspan="6">
                        <center><h3 class="title" style='text-align: center; text-transform: uppercase;'><?php echo $lang['general-discounts']; ?></h3><br /><br /></center>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $lang['member-discountBar']; ?></td>
                    <td></td>
                    <td></td>
                    <td class='right'><input type="number" class="oneDigit defaultinput" name="discountBar" value="<?php echo $discountBar; ?>" /> % </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan='6'>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan='6'><center><h3 class="title" style='text-align: center; text-transform: uppercase;'><?php echo $lang['category-discounts']; ?></h3></center></td>
                </tr>
                <?php
                // Select categories
                $selectCatsB = "SELECT id, name from b_categories ORDER by id ASC";
                try {
                    $resultsCatsB = $pdo3->prepare("$selectCatsB");
                    $resultsCatsB->execute();
                } catch (PDOException $e) {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                }


                $e = 0;
                while ($categoryB = $resultsCatsB->fetch()) {

                    $categoryidB = $categoryB['id'];
                    $catNameB = $categoryB['name'];

                    // Look up discount for THIS category
                    $catDiscountB = '';
                    if($happy_hour_id){
                        $selectCategoryDiscountB = "SELECT discount FROM b_catdiscounts WHERE categoryid = ".$categoryidB." and happy_hour_id = ".$happy_hour_id;
                        try {
                            $result = $pdo3->prepare("$selectCategoryDiscountB");
                            $result->execute();
                        } catch (PDOException $e) {
                            $error = 'Error fetching user: ' . $e->getMessage();
                            echo $error;
                            exit();
                        }

                        $rowCDB = $result->fetch();
                        $catDiscountB = $rowCDB['discount'];
                    }
                    echo <<<EOD
				<tr>
				 <td>$catNameB</td>
				 <td></td>
				 <td></td>
				 <td class='right'><input type='number' class='oneDigit defaultinput' name='indCatDiscountB[{$e}][catDiscountB]' value='{$catDiscountB}' /> %
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
                    <td colspan='6'><center><h3 class="title" style='text-align: center; text-transform: uppercase;'><?php echo $lang['product-discounts']; ?></h3></center></td>
                </tr>
                <?php
// First, select categories
                $selectCatsB = "SELECT id, name from b_categories ORDER by id ASC";
                try {
                    $resultsCatsB = $pdo3->prepare("$selectCatsB");
                    $resultsCatsB->execute();
                } catch (PDOException $e) {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                }


                $f = 0;
                while ($categoryB = $resultsCatsB->fetch()) {

                    $categoryidB = $categoryB['id'];
                    $catNameB = $categoryB['name'];

                    $catProdB .= "<tr><td colspan='2'>&nbsp;</td></tr>";
                    $catProdB .= "<tr><td colspan='6' class='yellow' style='cursor: pointer;' id='expandB$categoryidB'><center><strong class='cta4nm' style='width: 350px;'>$catNameB &raquo;</strong></center></td></tr>
	<script>
	$('#expandB$categoryidB').click(function () {
	$('.headerB$categoryidB').toggle();
	$('.tableB$categoryidB').toggle();
	});
	</script>
";
                    if (${'titleB' . $categoryidB} != 'set') {
                        $catProdB .= <<<EOD
	<tr class='headerB$categoryidB' style='display: none;'>
	 <th style='vertical-align: bottom;'><strong>Producto</strong></th>
	 <th style='vertical-align: bottom;' class='right'><strong>Precio</strong></th>
     <th></th>
	 <th style='vertical-align: bottom;' class='right'><strong>Descuento%</strong></th>
     <th class='center'>&nbsp;&nbsp;&nbsp; o </th>
	 <th style='vertical-align: bottom;'><strong>Precio fijo</strong></th>	 
	</tr>
EOD;
                    }
                    ${'title' . $categoryid} = 'set';

                    // For each category, loop through all open purchases
                    $selectProductsB = "SELECT g.name, p.purchaseid, p.salesPrice, p.purchaseQuantity FROM b_products g, b_purchases p WHERE p.category = $categoryidB AND p.productid = g.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY salesPrice ASC";
                    try {
                        $results = $pdo3->prepare("$selectProductsB");
                        $results->execute();
                    } catch (PDOException $e) {
                        $error = 'Error fetching user: ' . $e->getMessage();
                        echo $error;
                        exit();
                    }

                    while ($indProductB = $results->fetch()) {

                        $nameB = $indProductB['name'];
                        $purchaseidB = $indProductB['purchaseid'];
                        $salesPriceB = $indProductB['salesPrice'];

                        $salesPriceB = number_format($salesPriceB, 2);

                        // Look up discount for THIS product
                        $prodDiscountB = $prodFijoB = "";
                        if($happy_hour_id){
                            $selectPurchaseDiscountB = "SELECT discount, fijo FROM b_inddiscounts WHERE purchaseid = ".$purchaseidB." and happy_hour_id = ".$happy_hour_id;
                            try {
                                $result = $pdo3->prepare("$selectPurchaseDiscountB");
                                $result->execute();
                            } catch (PDOException $e) {
                                $error = 'Error fetching user: ' . $e->getMessage();
                                echo $error;
                                exit();
                            }

                            $rowDB = $result->fetch();
                            $prodDiscountB = $rowDB['discount'];
                            $prodFijoB = $rowDB['fijo'];
                        }

                        if ($prodDiscountB != '' && $prodDiscountB != 0) {
                            $discPriceB = number_format((1 - ($prodDiscountB / 100)) * $salesPriceB, 2);
                        } else if ($prodFijoB != '' && $prodFijoB != 0) {
                            $discPriceB = $prodFijoB;
                        } else {
                            $discPriceB = $salesPriceB;
                        }

                        $catProdB .= <<<EOD
							<tr class='tableB$categoryidB' style='display: none;'>
			 <td>$nameB</td>
			 <td class='right'>$salesPriceB {$_SESSION['currencyoperator']}</td>
    		 <td class='right yellow'></td>
			 <td class='right'>
			  <input type='number' class='oneDigit defaultinput' name='indDiscountB[{$f}][purchaseDiscountB]' value='{$prodDiscountB}' id='percentageB$f' step='0.01' /> %
			  <span style='font-size: 12px;'><input type='text' class='specialInput' id='newPriceB$f' step='0.01' value='($discPriceB' readonly />{$_SESSION['currencyoperator']})
			  </span>
			 </td>
    		 <td class='yellow'></td>
    		 <td>
    		  <input type='number' class='fourDigit defaultinput' id='fijoB$f' name='indDiscountB[{$f}][purchaseFijoB]' value='$prodFijoB' step='0.01' /> {$_SESSION['currencyoperator']}
    		  
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
                                  
    $( function() {
        $( "#from" ).timepicker();
        $( "#to" ).timepicker({minutes: {
        interval: 10,
        manual: [ 0, 5, 30, 59 ]
    }});
        $( "#datepicker" ).datepicker();
      });	
      
      
</script>
<script>

$("#to").on("click change",function(){
    var timeFromValue = $( "#from" ).val().split(':')[0];
    var myEm = timeFromValue -1;
    myEm = myEm + 1;

    var timeFromValueSec = $( "#from" ).val().split(':')[1];
     timeFromValueSec = timeFromValueSec-1;
     timeFromValueSec = timeFromValueSec+1;

    var timeToValue = $( "#to" ).val().split(':')[0];
    var timeToValueSec = $( "#to" ).val().split(':')[1];
       
    $(".ui-timepicker tr td").each(function() {   
        if ($(this).attr('data-hour') < myEm) {
            var Mydataattr= $(this).attr("data-hour");
            $("td[data-hour='" + Mydataattr + "']").addClass("pointerEventsNone");
        }
        if(timeFromValue == timeToValue){
            if ($(this).attr('data-minute') <= timeFromValueSec) {
                var MydataattrSec= $(this).attr("data-minute");
                $("td[data-minute='" + MydataattrSec + "']").addClass("pointerEventsNone");
            }
        }else{
            if ($(this).attr('data-minute') <= timeFromValueSec) {
                var MydataattrSec= $(this).attr("data-minute");
                $("td[data-minute='" + MydataattrSec + "']").removeClass("pointerEventsNone");
            }
        }
    });


  });
  $("#from").on("click change",function(){
    $("#to").val('')
});


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

<br /><center><button class='oneClick cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button></center>

</form>
</div>
<?php displayFooter(); ?>