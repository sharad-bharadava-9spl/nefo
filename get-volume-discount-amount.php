<?php 

require_once 'cOnfig/connection.php';
require_once 'cOnfig/languages/common.php';
require_once 'applyMaxDiscount.php';
/*ini_set("log_errors", TRUE);  
  
// setting the logging file in php.ini 
ini_set('error_log', "error.log"); */
$accessLevel = '3';
getSettings();
if(isset($_GET['id']) && isset($_GET['amount'])){
    $purchaseid = $_GET['id'];
    $amount = $_GET['amount'];
    $user_id = $_GET['uid'];
    $qty = 0;
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
    $getMaxDisocunt = checkMaxDisocunt($user_id, $purchaseid, $categoryId, $salesPrice, $qty, $amount, $offsetSec);
    if($getMaxDisocunt){
        $getMaxDisocunt = explode("_", $getMaxDisocunt);
        $dispDiscount1 = $getMaxDisocunt[0];
        $discountType = $getMaxDisocunt[1];
        if($discountType != 6){
            $applyDisc = (100 - $dispDiscount1) / 100;
            $price = number_format($salesPrice * $applyDisc,2);
            $quantity = number_format($amount / $price,2);
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
            $remAmount = $amount;
            $applyOn = 0;
            $final_qty = $quantity = $categoryId = $volume_per = 0;
            $arrayKey = [];
            $discountArr = [];
            $amountArr = [];
            while($rs_arr = $result_arr->fetch()){
                $arrayKey[$rs_arr['units']] = $rs_arr['units'];
                $arrayKey[$rs_arr['units'].'_amount'] = $rs_arr['amount'];
                $discountArr[$rs_arr['amount']] = $rs_arr['units'];
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
            if($numItems > 0){
                $categoryId = $rs1['category'];
                $dynaimcPrice = 0;
               foreach ($arrayKey as $arrayKey_key => $arrayKey_value) {
                   $int_key =  number_format((float)$arrayKey_key, 2);
                   
                  if($arrayKey_key == $int_key."_amount"){
                      $amountArr[$int_key] = $arrayKey_value;
                  }
               }
                for($k=min($discountArr); $k<= max($discountArr); $k++){
                        $deci_num = number_format($k, 2);
                        if(isset($arrayKey[$deci_num])){
                            $array_key_num = number_format($arrayKey[$deci_num], 2);
                        }
                        //echo $array_key_num."<br>";
                        if( $array_key_num === $deci_num){
                            $dynaimcPrice = $arrayKey[$deci_num."_amount"];
                        }else{
                            $dynaimcPrice += $salesPrice;
                        } 
                }

                $checkVal =1;
                $lastChecked = 0;
                $i = 1;
                while($remAmount > 0){ 
                        if($checkVal == 1){
                            $closeAmount  = closest($amountArr, $remAmount, $salesPrice, $checkVal); 
                            $closeArr = explode("_", $closeAmount);
                            $closestAmount = $closeArr[1];
                            $checkVal = $closeArr[0];
                        }else{
                            $closestAmount = $remAmount;
                        }
                        if($checkVal == 0 && in_array($closestAmount, $amountArr) && $lastChecked == 0){
                            $lastChecked = 1;
                            $maxkeyAmount = $amountArr[max(array_keys($amountArr))];
                            if($closestAmount >= $maxkeyAmount){
                                $quantity += max(array_keys($amountArr));
                            }
                        }else if(in_array($closestAmount, $amountArr)){
                            foreach($amountArr as $amountkey=>$amountval){
                                if($amountval == $closestAmount){
                                      $key_arr[] = $amountkey;
                                }
                            }
                            if(count($key_arr) > 1){
                                $quantity += end($key_arr);
                            }else{
                                $quantity += array_search($closestAmount, $amountArr);
                            }
                        }else if($amountArr[max(array_keys($amountArr))] < $closestAmount){
                            $quantity += max(array_keys($amountArr));
                            $closestAmount = $amountArr[max(array_keys($amountArr))];
                        }else if($remAmount > 0){
                            $without_vol_amount = $remAmount;
                            $without_vol_qty = $without_vol_amount / $salesPrice;
                            $final_qty += number_format(($without_vol_qty), 2);
                            $quantity += number_format(($without_vol_qty), 2);
                        } 

                        $remAmount = $remAmount - $closestAmount;
                    }
            }
        }
    }else{
        $quantity = number_format($amount / $salesPrice,2);
    }
        $quantity = str_replace(",", "", $quantity);    
        //echo $quantity.'_'.$dispDiscount1.'_'.$discountType;
        $response = [];
        $response['quantity'] = $quantity;
        $response['discount_per'] = $dispDiscount1;
        $response['discount_type'] = $discountType;
        echo json_encode($response);      
            
       //echo $vol_disount_string = end($price_arr);
    die;
}
