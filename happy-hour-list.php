<?php
session_start();
$accessLevel = '1';

// Authenticate & authorize
authorizeUser($accessLevel);

$deleteDiscountScript = <<<EOD
    function delete_discount(discountid) {
        if (confirm("{$lang['discount-deleteconfirm']}")) {
            window.location = "uTil/delete-happy-hour-discount.php?discountid=" + discountid;
        }
    }
EOD;

pageStart($lang['global-discounts'], NULL, $deleteDiscountScript, "pprofile", "global-discounts dev-align-center", $lang['happy-hour-discounts'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

include "global-discount-menu.php";

$discounts = "SELECT * FROM global_happy_hour_discounts";
try {
    $result = $pdo3->prepare("$discounts");
    $result->execute();
} catch (PDOException $e) {
    $error = 'Error fetching user: ' . $e->getMessage();
    echo $error;
    exit();
}
?>
<br />
<div class="actionbox-np2">
    <div class='boxcontent'>
         <a href="global-discounts.php?type=hhd&action=add" class="cta1 custom-width"><?php echo $lang['add-discount']; ?></a>
        <table class='default' id='mainTable'>
                <thead>
                    <tr style='cursor: pointer;'>
                        <th><?php echo $lang['discount-name']; ?></th>
                        <th><?php echo $lang['discount-date']; ?></th>
                        <th><?php echo $lang['time-from']; ?></th>
                        <th><?php echo $lang['time-to']; ?></th>
                        <th><?php echo $lang['last-modified-date']; ?></th>
                        <th class='noExl' colspan="2"><?php echo $lang['global-actions']; ?></th>
                    </tr>
                </thead>
                <tbody>

            <?php
            while ($discount = $result->fetch()) {

                $discount_id = $discount['id'];
                $discount_name = $discount['discount_name'];
                $discountDate = ucfirst($discount['discount_date']);
                $discount_from = $discount['time_from'];
                $discount_to = $discount['time_to'];
                $creditAfter = date("Y-m-d H:i:s", strtotime($discount['created'] . "+$offsetSec seconds"));
                $deleteOrNot = "<td class='noExl' style='text-align: center;'>&nbsp;&nbsp;<a href='javascript:delete_discount($discount_id)'><img src='images/delete.png' height='15' title='{$lang['delete-discount']}' /></a></td>";

                $expense_row .= sprintf("
                                    <tr>
                                        <td class='left clickableRow' href='global-discounts.php?type=hhd&action=add&id=$discount_id'>%s</td>
                                        <td class='left clickableRow' href='global-discounts.php?type=hhd&action=add&id=$discount_id'>%s</td>
                                        <td class='left clickableRow' href='global-discounts.php?type=hhd&action=add&id=$discount_id'>%s</td>
                                        <td class='left clickableRow' href='global-discounts.php?type=hhd&action=add&id=$discount_id'>%s</td>
                                        <td class='left clickableRow' href='global-discounts.php?type=hhd&action=add&id=$discount_id'>%s</td>
                                        <td class='noExl clickableRow' href='global-discounts.php?type=hhd&action=add&id=$discount_id'></td>%s
                                    </tr>", $discount_name, $discountDate, $discount_from, $discount_to, $creditAfter, $deleteOrNot
                );
            }

            if(empty($expense_row)){
                echo "<tr><td colspan='6' align='center'>" . $lang['no-record-found'] . "</td></tr>";
            }else {
                echo $expense_row;
            }
            ?>

                </tbody>
        </table>
    </div>
</div>
<?php displayFooter(); ?>
