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
	
	if (isset($_GET['type']) && $_GET['type'] == 'hhd') {
            if (isset($_GET['action']) && $_GET['action'] == 'add') {
                include "happy-hour-discounts.php";
            }else{
                include "happy-hour-list.php";
            }
	} else if (isset($_GET['type']) && $_GET['type'] == 'ud') {
            if (isset($_GET['action']) && $_GET['action'] == 'add') {
                include "usergroup-discounts.php";
            }else{
                include "usergroup-list.php";
            }
	} else { 
            pageStart($lang['global-discounts'], NULL, NULL, "pprofile", "global-discounts dev-align-center", $lang['discounts'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
            include "global-discount-menu.php"; ?>
            <h3 class="title"><?php echo $lang['currently-registered-discounts']; ?></h3>   
            <div class="actionbox-np2">
                <div class='boxcontent'>
                    <div class='mainboxheader'><?php echo strtoupper($lang['medical-discount']); ?></div>
                    <div class="linkholder">
                        <?php 
                            $medicalDiscount = '';
                            $discounts = "SELECT medicalDiscount,medicalDiscountPercentage FROM systemsettings";
                            try {
                                $result = $pdo3->prepare("$discounts");
                                $result->execute();
                            } catch (PDOException $e) {
                                $error = 'Error fetching user: ' . $e->getMessage();
                                echo $error;
                                exit();
                            }
                            while ($discount = $result->fetch()) {
                                if($discount['medicalDiscountPercentage'] == 0){
                                    $medicalDiscount .= "<p>-".$discount['medicalDiscount']."".$_SESSION['currencyoperator']." ".$lang['for-all-medical-users']."</p>";
                                }else{
                                     $medicalDiscount .= "<p>-".$discount['medicalDiscount']."% ".$lang['for-all-medical-users']."</p>";
                                }    
                            }
                            if($medicalDiscount){
                                echo $medicalDiscount;
                            }else{
                                echo $lang['no-discount-available'];
                            }
                            
                        ?>
                    </div>
                </div>
            </div>
             <div class="actionbox-np2">
                <div class='boxcontent'>
                    <div class='mainboxheader'><?php echo strtoupper($lang['medical-discount-per-purchase']); ?></div>
                    <div class="linkholder">
                        <?php 
                            $medicalDiscount = '';
                            $discounts = "SELECT GROUP_CONCAT(products.name) AS name,GROUP_CONCAT(purchases.medicalDiscount) AS medicalDiscount FROM products LEFT JOIN purchases on purchases.productid = products.productid WHERE medicalDiscount > 0 group by products.productid";
                            try {
                                $result = $pdo3->prepare("$discounts");
                                $result->execute();
                            } catch (PDOException $e) {
                                $error = 'Error fetching user: ' . $e->getMessage();
                                echo $error;
                                exit();
                            }
                            while ($discount = $result->fetch()) {
                                $medicalDiscount .= "<p>-".$discount['name'].": ".$discount['medicalDiscount']."% ".$lang['for-all-medical-users']."</p>";
                            }
                            if($medicalDiscount){
                                echo $medicalDiscount;
                            }else{
                                echo $lang['no-discount-available'];
                            }
                        ?>
                    </div><br>
                    <?php 
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
                </div>
            </div>
            <div class="actionbox-np2">
                <div class="boxcontent">
                    <div class='mainboxheader'><?php echo strtoupper($lang['happy-hour-discounts']); ?></div>
                    <div class="linkholder">
                        <?php
                        $medicalDiscount = '';
                        while ($discount = $result->fetch()) { 
                            $medicalDiscount .= "<p>- ". $discount['discount_name'].": ".ucfirst($discount['discount_date'])." ".$discount['time_from'].' - '.$discount['time_to']. "</p>";
                        } 
                        if($medicalDiscount){
                            echo $medicalDiscount;
                        }else{
                            echo $lang['no-discount-available'];
                        }                    
                        ?>
                    </div><br>
                    <?php 
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
                </div>
            </div>
            <div class="actionbox-np2">
                <div class="boxcontent">
                    <div class='mainboxheader'><?php echo strtoupper($lang['usergroup-discounts']); ?></div>
                    <div class="linkholder">
                        <?php 
                            $medicalDiscount = '';
                            while ($discount = $result->fetch()) { 
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
                            $disval = '';
                            if($discount['discount_price'] > 1){
                                $medicalDiscount .= "<p>".$gn['name']." : ".$discount['discount_price']."".$_SESSION['currencyoperator']."</p>";
                            }else{
                                $medicalDiscount .= "<p>".$gn['name']." : ".$discount['discount_percentage']."%</p>";
                            }
                        } 
                        if($medicalDiscount){
                            echo $medicalDiscount;
                        }else{
                            echo $lang['no-discount-available'];
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>    
 <?php displayFooter(); ?>
 <script type="text/javascript">
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
 </script>
