<?php 

require_once 'cOnfig/connection.php';
require_once 'cOnfig/languages/common.php';
require_once 'applyMaxDiscount.php';
/*ini_set("log_errors", TRUE);  
  
// setting the logging file in php.ini 
ini_set('error_log', "error.log");*/ 
$accessLevel = '3';

getSettings();
if(isset($_GET['id']) && isset($_GET['qty'])){
    $purchaseid = $_GET['id'];
    $qty = $_GET['qty'];
    $user_id = $_GET['uid'];
    $amount = 0;
    // fetch purchase details
    $selectSales = "SELECT category,salesPrice FROM purchases WHERE purchaseid =". $purchaseid;
    try
    {
        $result1 = $pdo3->prepare("$selectSales");
        $result1->execute();
    }
    catch (PDOException $e)
    {
        $error = 'Error fetching user: ' . $e->getMessage();
        echo $error;
        exit();
    }
    $rs1 = $result1->fetch();
    $salesPrice = $rs1['salesPrice'];
    $categoryId = $rs1['category'];
    $dispDiscount1 = 0;
    $discountType = 0;
    $getMaxDisocunt = checkMaxDisocunt($user_id, $purchaseid, $categoryId, $salesPrice, $qty, $amount,  $offsetSec);
    if($getMaxDisocunt){
        $getMaxDisocunt = explode("_", $getMaxDisocunt);
        $dispDiscount1 = $getMaxDisocunt[0];
        $discountType = $getMaxDisocunt[1];
        if($discountType != 6){
            $applyDisc = (100 - $dispDiscount1) / 100;
            $price = number_format($salesPrice * $applyDisc,2);
            $price = number_format($price * $qty,2);
        }else{
            $selectSales = "SELECT * FROM volume_discounts WHERE purchaseid =". $purchaseid." order by units desc";
            try
            {
                $result = $pdo3->prepare("$selectSales");
                $result_arr = $pdo3->prepare("$selectSales");
                $result->execute();
                $result_arr->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $numItems = $result->rowCount();
            $i = 0;
            $remQty = $qty;
            $applyOn = 0;
            $final_amount = $price = $categoryId = $volume_per = 0;
            $arrayKey = [];
            while($rs_arr = $result_arr->fetch()){
                $arrayKey[$rs_arr['units']] = $rs_arr['units'];
                $arrayKey[$rs_arr['units'].'_amount'] = $rs_arr['amount'];
            }
            
            $volumeArray=[];
            foreach ($arrayKey as $arrayKey_key => $arrayKey_value) {
                $int_key =  number_format((float)$arrayKey_key, 2);
                
               if($arrayKey_key == $int_key."_amount"){
                   $volumeArray[$int_key] = $arrayKey_value;
               }
            }

            $selectSales = "SELECT purchaseid,category,salesPrice FROM purchases WHERE purchaseid =". $purchaseid;
            try
            {
                $result1 = $pdo3->prepare("$selectSales");
                $result1->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $rs1 = $result1->fetch();
            $salesPrice = $rs1['salesPrice'];
            $categoryId = $rs1['category'];
            if($numItems > 0){
                while ($remQty > 0) {
                    if(isset($rs1['purchaseid'])){
                        //if($applyOn <= $qty){
                            $highestValue = getHighest($remQty,$arrayKey);
                            if(isset($arrayKey[$highestValue])){
                                if(in_array($highestValue,array_keys($volumeArray))){
                                    $final_amount += $arrayKey[$highestValue.'_amount'];
                                    $price += $arrayKey[$highestValue.'_amount'];
                                   
                                }else{
                                    $without_vol_qty = $remQty;
                                    $without_vol_price = $without_vol_qty * $salesPrice;
                                    $final_amount += number_format(($without_vol_price), 2);
                                    $price += number_format(($without_vol_price), 2);
                                    
                                }
                            }else if($remQty > 0){
                                
                                $without_vol_qty = $remQty;
                                $without_vol_price = $without_vol_qty * $salesPrice;
                                $final_amount += number_format(($without_vol_price), 2);
                                $price += number_format(($without_vol_price), 2);

                               
                            }
                            $remQty = $remQty - $highestValue;
                    }
                }
            }
        }
    }else{
        $price = number_format($salesPrice * $qty,2);
    }
    $price = str_replace(",", "", $price);
   // echo $price.'_'.$dispDiscount1.'_'.$discountType;
    $response = [];
    $response['price'] = $price;
    $response['discount_per'] = $dispDiscount1;
    $response['discount_type'] = $discountType;
    echo json_encode($response);
    die;
}
