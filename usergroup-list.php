<?php

session_start();

$accessLevel = '1';



// Authenticate & authorize

authorizeUser($accessLevel);



$deleteDiscountScript = <<<EOD
        $(document).ready(function() {
            
            $('#mainTable').tablesorter({
                usNumberFormat: true,
                headers: {
                    3: {
                        sorter: "dates"
                    },
                    7: {
                        sorter: "dates"
                    }
                }
            }); 

        });
    function delete_discount(discountid) {

        if (confirm("{$lang['discount-deleteconfirm']}")) {

            window.location = "uTil/delete-group-discount.php?discountid=" + discountid;

        }

    }

EOD;



pageStart($lang['usergroup-discounts'], NULL, $deleteDiscountScript, "pprofile", "usergroup-discounts dev-align-center", $lang['usergroup-discounts'], $_SESSION['successMessage'], $_SESSION['errorMessage']);



include "global-discount-menu.php";



$discounts = "SELECT * FROM usergroup_discounts";

try {

    $result = $pdo3->prepare("$discounts");

    $result->execute();

} catch (PDOException $e) {

    $error = 'Error fetching user: ' . $e->getMessage();

    echo $error;

    exit();

}



?>

<div class="actionbox-np2">

    <div class='boxcontent'>

         <a href="global-discounts.php?type=ud&action=add" class="cta1 custom-width"><?php echo $lang['add-discount']; ?></a>

        <table class='default' id='mainTable'>

            <thead>

                <tr style='cursor: pointer;'>

                    <th><?php echo $lang['discount-name']; ?></th>

                    <th><?php echo $lang['discount-amount']; ?></th>

                    <th><?php echo $lang['discount-percentage']; ?></th>

                    <th><?php echo $lang['bardiscunt-amount']; ?></th>

                    <th><?php echo $lang['bardiscunt-percentage']; ?></th>

                    <th><?php echo $lang['last-modified-date']; ?></th>

                    <th class='noExl' colspan="2"><?php echo $lang['global-actions']; ?></th>

                </tr>

            </thead>

            <tbody>



        <?php

        while ($discount = $result->fetch()) {

            $discount_id = $discount['id'];

            $groupName = "SELECT name FROM usergroups2 WHERE id =" . $discount['usergroup_id'];

            try {

                $groupName = $pdo3->prepare("$groupName");

                $groupName->execute();

            } catch (PDOException $e) {

                $error = 'Error fetching user: ' . $e->getMessage();

                echo $error;

                exit();

            }

            $gn = $groupName->fetch();

            $discount_name = $gn['name'];

            $discount_price = $discount['discount_price'];

            $b_discount_price = $discount['b_discount_price'];

            $discount_percentage = $discount['discount_percentage'];

            $b_discount_percentage = $discount['b_discount_percentage'];

            $creditAfter = date("Y-m-d H:i:s", strtotime($discount['created'] . "+$offsetSec seconds"));

            $deleteOrNot = "<td class='noExl' style='text-align: center;'>&nbsp;&nbsp;<a href='javascript:delete_discount($discount_id)'><img src='images/delete.png' height='15' title='{$lang['delete-discount']}' /></a></td>";



            $expense_row .= sprintf("

                                <tr>

                                    <td class='left clickableRow' href='global-discounts.php?type=ud&action=add&id=$discount_id'>%s</td>

                                    <td class='left clickableRow' href='global-discounts.php?type=ud&action=add&id=$discount_id'>%s</td>

                                    <td class='left clickableRow' href='global-discounts.php?type=ud&action=add&id=$discount_id'>%s</td> 

                                    <td class='left clickableRow' href='global-discounts.php?type=ud&action=add&id=$discount_id'>%s</td> 

                                    <td class='left clickableRow' href='global-discounts.php?type=ud&action=add&id=$discount_id'>%s</td>

                                    <td class='left clickableRow' href='global-discounts.php?type=ud&action=add&id=$discount_id'>%s</td>

                                    <td class='noExl clickableRow' href='global-discounts.php?type=ud&action=add&id=$discount_id'></td>%s

                                </tr>", $discount_name, $discount_price, $discount_percentage, $b_discount_price, $b_discount_percentage, $creditAfter, $deleteOrNot

            );

        }



        if(empty($expense_row)){

            echo "<tr><td colspan='5' align='center'>" . $lang['no-record-found'] . "</td></tr>";

        }else {

            echo $expense_row;

        }

        ?>



            </tbody>

        </table>

    </div>

</div>



<?php displayFooter(); ?>
