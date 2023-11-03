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
if (isset($_POST['usergroup_id']) && $_POST['usergroup_id'] != '') {
        $usergroup_id = $_POST['usergroup_id'];
        $usergroup_discount_id = $_POST['usergroup_discount_id'];
        $discount_price = $_POST['discount_price'];
        $b_discount_price = $_POST['b_discount_price'];
        $discount_percentage = $_POST['discount_percentage'];
        $b_discount_percentage = $_POST['b_discount_percentage'];
        $created = date("Y-m-d H:i:s");
        
        // DISPENSARY DISCOUNTS
        // First, delete from inddiscounts for this user_id, then set new values
        $deleteDiscounts = "DELETE FROM usergroup_discounts WHERE id = $usergroup_discount_id";
        try {
            if(!empty($usergroup_discount_id)){
                $result = $pdo3->prepare("$deleteDiscounts")->execute();
            }
            $addDiscount = sprintf("INSERT INTO usergroup_discounts (usergroup_id, discount_price, discount_percentage, b_discount_price, b_discount_percentage, created) VALUES ('%d', '%f', '%f', '%f', '%f', '%s');", $usergroup_id, $discount_price, $discount_percentage, $b_discount_price, $b_discount_percentage, $created);
            try {
                $result = $pdo3->prepare("$addDiscount")->execute();
                $usergroup_discount_id = $pdo3->lastInsertId();
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

                $addDiscount = sprintf("INSERT INTO catdiscounts (usergroup_discount_id, categoryid, discount) VALUES ('%d', '%d', '%f');", $usergroup_discount_id, $catID, $catD);
                try {
                    $result = $pdo3->prepare("$addDiscount")->execute();
                } catch (PDOException $e) {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                }
            }
        }

        $deleteDiscounts = "DELETE FROM inddiscounts WHERE usergroup_discount_id = $usergroup_discount_id";
        try {
            if(!empty($usergroup_discount_id)){
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

                    $addDiscount = sprintf("INSERT INTO inddiscounts (usergroup_discount_id, purchaseid, discount) VALUES ('%d', '%d', '%f');", $usergroup_discount_id, $purchaseid, $indD);
                    try {
                        $result = $pdo3->prepare("$addDiscount")->execute();
                    } catch (PDOException $e) {
                        $error = 'Error fetching user: ' . $e->getMessage();
                        echo $error;
                        exit();
                    }
                }

                if ($indFijo != '') {

                    $addDiscount = sprintf("INSERT INTO inddiscounts (usergroup_discount_id, purchaseid, fijo) VALUES ('%d', '%d', '%f');", $usergroup_discount_id, $purchaseid, $indFijo);
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
        $deleteDiscountsB = "DELETE FROM b_catdiscounts WHERE usergroup_discount_id = $usergroup_discount_id";
        try {
            if(!empty($usergroup_discount_id)){
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

                $addDiscountB = sprintf("INSERT INTO b_catdiscounts (usergroup_discount_id, categoryid, discount) VALUES ('%d', '%d', '%f');", $usergroup_discount_id, $catIDB, $catDB);
                try {
                    $result = $pdo3->prepare("$addDiscountB")->execute();
                } catch (PDOException $e) {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                }
            }
        }

        $deleteDiscountsB = "DELETE FROM b_inddiscounts WHERE usergroup_discount_id = $usergroup_discount_id";
        try {
            if(!empty($usergroup_discount_id)){
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

                $addDiscountB = sprintf("INSERT INTO b_inddiscounts (usergroup_discount_id, purchaseid, discount) VALUES ('%d', '%d', '%f');", $usergroup_discount_id, $purchaseidB, $indDB);
                try {
                    $result = $pdo3->prepare("$addDiscountB")->execute();
                } catch (PDOException $e) {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                }
            }

            if ($indFijoB != '') {

                $addDiscountB = sprintf("INSERT INTO b_inddiscounts (usergroup_discount_id, purchaseid, fijo) VALUES ('%d', '%d', '%f');", $usergroup_discount_id, $purchaseidB, $indFijoB);
                try {
                    $result = $pdo3->prepare("$addDiscountB")->execute();
                } catch (PDOException $e) {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                }
            }
        }
/*        if (isset($_POST['edit']) && $_POST['edit'] != '') { 
            $id = $_POST['edit'];
            $_SESSION['successMessage'] = $lang['discount-updated'];
            $query = sprintf("UPDATE usergroup_discounts SET usergroup_id = '%s', discount_price = '%f', discount_percentage = '%f', b_discount_price = '%f', b_discount_percentage = '%f', created = '%s' WHERE id = '%d';",
            $usergroup_id,$discount_price,$discount_percentage,$b_discount_price,$b_discount_percentage, $created, $id);
            
        }else{
            $_SESSION['successMessage'] = $lang['discount-added'];
            $query = sprintf("INSERT INTO usergroup_discounts (usergroup_id, discount_price, discount_percentage, b_discount_price, b_discount_percentage, created) VALUES ('%s', '%d', '%d', '%d', '%d', '%s');",
            $usergroup_id, $discount_price,$discount_percentage,$b_discount_price,$b_discount_percentage, $created);
        }
        try
        {
            $result = $pdo3->prepare("$query")->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }*/
        // On success: redirect.
        $_SESSION['successMessage'] = $lang['discounts-applied'];
        header("Location: global-discounts.php?type=ud");
        exit();
}
$usergroup_id = $discount_price = $discount_percentage =  $b_discount_price =  $b_discount_percentage = '';
if (isset($_GET['id']) && $_GET['id'] != '') {
        $usergroup_discount_id = $_GET['id'];
        $query = "SELECT * FROM usergroup_discounts WHERE id =" . $usergroup_discount_id;
        try {
            $result = $pdo3->prepare("$query");
            $result->execute();
        } catch (PDOException $e) {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $results = $result->fetch();
        if(isset($results['usergroup_id'])){
            $usergroup_id = $results['usergroup_id'];
            $discount_price = $results['discount_price'];
            $b_discount_price = $results['b_discount_price'];
            $discount_percentage = $results['discount_percentage'];
            $b_discount_percentage = $results['b_discount_percentage'];
        }
}
$validationScript = <<<EOD
    $(document).ready(function() {
        function compute() {
            $('#discount_price').val('');
            $('#b_discount_price').val('');
        }
        $('#discount_percentage').bind('keypress keyup blur', compute);
        $('#b_discount_percentage').bind('keypress keyup blur', compute);
        function compute2() {
            $('#discount_percentage').val('');
            $('#b_discount_percentage').val('');
        }
        $('#discount_price').bind('keypress keyup blur', compute2);
        $('#b_discount_price').bind('keypress keyup blur', compute2);
       
  }); // end ready
EOD;
pageStart($lang['usergroup-discounts'], NULL, $validationScript, "pprofile", "usergroup-discounts dev-align-center", $lang['usergroup-discounts'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
include "global-discount-menu.php";
?>
<div class="actionbox-np2">
    <!-- END OVERVIEW -->
    <div class="clearfloat"></div>
        <div class="boxcontent">
        <form id="usergroupDiscount" action="" method="POST">
           <strong><?php echo $lang['member-usergroup']; ?></strong>
           <input type="hidden" name="usergroup_discount_id" value="<?php echo $usergroup_discount_id; ?>" />
            <select name="usergroup_id" id="usergroup_id" class="defaultinput">
                <?php
                // Query to look up usergroups
                $selectGroups = "SELECT id, name FROM usergroups2 ORDER by id ASC";
                try {
                    $result = $pdo3->prepare("$selectGroups");
                    $result->execute();
                } catch (PDOException $e) {
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                }
                while ($group = $result->fetch()) {
                    $selected = "";
                    if ($group['id'] == $usergroup_id) {
                        $selected = "selected";
                    }
                    $group_row = sprintf("<option value='%d' $selected>%d - %s</option>", $group['id'], $group['id'], $group['name']);
                    echo $group_row;
                }
                ?>
            </select>
            <?php if (isset($_GET['id']) && $_GET['id'] != '') { ?>
                <input type="hidden" name="edit" value="<?php echo $_GET['id'] ?>" >
            <?php } ?>
             <br /><br />
            <span class="cta3" style='padding: 20px;'><?php echo $lang['discountsC'] . " " . $lang['dispensary']; ?></span><br />
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
                    <td class='right'>
                        <input type="text" id="discount_price" name="discount_price" autocomplete="nope" class="sixDigit defaultinput" value="<?php echo $discount_price ?>"/> <?php echo $_SESSION['currencyoperator'] ?>
                        <input type="text" id="discount_percentage" name="discount_percentage" autocomplete="nope" class="sixDigit defaultinput"  value="<?php echo $discount_percentage ?>"/> %
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan='6'><center><h3 class="title"><?php echo $lang['category-discounts']; ?></h3></center></td>
                </tr>
                <?php
                // Default categories
                $catDiscount = '';
                if($usergroup_discount_id){
                    $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = 1 and usergroup_discount_id = ".$usergroup_discount_id;
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
                if($usergroup_discount_id){
                    $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = 2 and usergroup_discount_id = ".$usergroup_discount_id;
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
                    if($usergroup_discount_id){
                        $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = ".$categoryid." and usergroup_discount_id = ".$usergroup_discount_id;
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
                    if($usergroup_discount_id){
                        $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE purchaseid = ".$purchaseid." and usergroup_discount_id = ".$usergroup_discount_id;
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
     <th class='center'>&nbsp;&nbsp;&nbsp;  </th>
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
                        if($usergroup_discount_id){
                            $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE purchaseid = ".$purchaseid." and usergroup_discount_id = ".$usergroup_discount_id;
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
     <th class='center'>&nbsp;&nbsp;&nbsp;  </th>
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
     <th style='vertical-align: bottom;'><strong>Descuento%</strong></th>
     <th class='center'>&nbsp;&nbsp;&nbsp;  </th>
     <th style='vertical-align: bottom;'><strong>Precio nuevo</strong></th>  
    </tr>
EOD;
                echo $extractOutput;
                echo $catProd;
                echo "</table>";

                echo "</span>";
                ?>
            </span>
            <br />
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
                    <td class='right'>
                        <input type="text" id="b_discount_price" name="b_discount_price" autocomplete="nope" class="sixDigit defaultinput" value="<?php echo $b_discount_price ?>"/> <?php echo $_SESSION['currencyoperator'] ?>
                        <input type="text" id="b_discount_percentage" name="b_discount_percentage" autocomplete="nope" class="sixDigit defaultinput"  value="<?php echo $b_discount_percentage ?>"/> %
                    </td>
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
                    if($usergroup_discount_id){
                        $selectCategoryDiscountB = "SELECT discount FROM b_catdiscounts WHERE categoryid = ".$categoryidB." and usergroup_discount_id = ".$usergroup_discount_id;
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
     <th class='center'>&nbsp;&nbsp;&nbsp;  </th>
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
                        if($usergroup_discount_id){
                            $selectPurchaseDiscountB = "SELECT discount, fijo FROM b_inddiscounts WHERE purchaseid = ".$purchaseidB." and usergroup_discount_id = ".$usergroup_discount_id;
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
             </td>
            </tr>
EOD;

                        $f++;
                    }
                }

                echo $catProdB;
                echo "</table>";
                ?>
            </span>
        </div><br />
        <div class="clearfloat"></div><br />
            <button class='oneClick cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
        </form>
    </div>
</div>
    <?php displayFooter(); ?>
<!-- When script submits, check to see if password+salt matches pw+salt in db. If yes, leave. If no, change. Hepp! 
Conversely: Leave Password out of the form, and replace with a link 'change password' -->
