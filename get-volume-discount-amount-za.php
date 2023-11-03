<?php 

require_once 'cOnfig/connection.php';
require_once 'cOnfig/languages/common.php';

$accessLevel = '3';
/*ini_set("log_errors", TRUE);  
  
// setting the logging file in php.ini 
ini_set('error_log', "error.log"); */
  
getSettings();
function getHighest($amount,$val_array){
    foreach ($val_array as $key => $value) {
        if($amount >= (int)$value){
            return $value;
        }
    }
    return $amount;
}
$amountArr = [];
   function getQuantity($amount, $amountArr, $salesPrice){

            $remAmount = $amount;
            $checkVal =1;
            $lastChecked = 0;
            $i = 1;
            $quantity = $final_qty =0;
            while($remAmount > 0){ 
                    // getting the nearest value in array
                //echo "checkVal ".$checkVal.'<br>';
               // echo "remAmount ".$remAmount.'<br>';  
                if($checkVal == 1){
                    $closeAmount  = closest($amountArr, $remAmount, $salesPrice, $checkVal); 
                    $closeArr = explode("_", $closeAmount);
                    $closestAmount = $closeArr[1];
                    $checkVal = $closeArr[0];
                }else{
                    $closestAmount = $remAmount;
                }
                //echo $closestAmount."-closest<br>";
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
                    
                   /* if($checkVal == 0 && $closestAmount >= $amountArr[max(array_keys($amountArr))]){
                        $quantity += max(array_keys($amountArr));
                    }*/
                }
                else if(!empty($amountArr)){
                    if($amountArr[max(array_keys($amountArr))] < $closestAmount){
                        $quantity += max(array_keys($amountArr));
                        $closestAmount = $amountArr[max(array_keys($amountArr))];
                    }else if($remAmount > 0){
                        $without_vol_amount = $remAmount;
                        $without_vol_qty = $without_vol_amount / $salesPrice;
                        $final_qty += number_format(($without_vol_qty), 2);
                        $quantity += number_format(($without_vol_qty), 2);
                    }
                }else if($remAmount > 0){
                    $without_vol_amount = $remAmount;
                    $without_vol_qty = $without_vol_amount / $salesPrice;
                    $final_qty += number_format(($without_vol_qty), 2);
                    $quantity += number_format(($without_vol_qty), 2);
                } 

                $remAmount = $remAmount - $closestAmount;

        }
        return $quantity;
   }
    

/*function getHighestQuant($closetAmount, $amount, $amountArr, $salesPrice){
  if($amount > $closetAmount){  
         $minusAmount = $amount - $closetAmount;

                if($minusAmount >= $salesPrice){
                    $calcQuant = max(array_keys($amountArr));
                }else{
                    $calcQuant = array_search($closetAmount, $amountArr);
                }
            }else{
                 if(in_array($amount, $amountArr)){
                    foreach ($amountArr as $amount_quant => $amount_val) {
                         if($amount_val == $amount){
                            $calcQuant = $amount_quant;
                         }
                     } 
                }else{
                    return $amount;
                }
            }
            return $calcQuant;
}*/

function closest($array, $number, $salesPrice, $checkVal = 1) {
    if(!empty($array)){
        $remaining = $number - max($array);
        if($number > max($array) && $remaining >= $salesPrice){  
            $maxKey = max(array_keys($array)); // or return NULL;
            $checkVal = 0;
            return $checkVal."_".$array[$maxKey];
        }else if($number > max($array) && $remaining < $salesPrice){
            return $checkVal."_".max($array);        
        }else{
            sort($array);
            for($k = 0; $k<= count($array); $k++){
                if((isset($array[$k]) && $array[$k] < $number) && (isset($array[$k+1]) && $array[$k+1] > $number)){
                   return $checkVal."_".$array[$k];
                }
            }
        }
        return $checkVal."_".$number;
    }
    return "0_0";
}

if($_GET['id'] && $_GET['amount']){
    $purchaseid = $_GET['id'];
    $amount = $_GET['amount'];
   
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
    //while($rs = $result->fetch()){
    if($numItems > 0){
         $categoryId = $rs1['category'];
/*        while ($remAmount > 0) {
            if(isset($rs1['purchaseid'])){
                    $highestValue = getHighest($remAmount,$arrayKey, $salesPrice);
                    if(isset($arrayKey[$highestValue])){
                        $final_qty += $arrayKey[$highestValue.'_quantity'];
                        $quantity += $arrayKey[$highestValue.'_quantity'];
                    }else if($remAmount > 0){
                        $without_vol_amount = $remAmount;
                        $without_vol_qty = $without_vol_amount / $salesPrice;
                        $final_qty += number_format(($without_vol_qty), 2);
                        $quantity += number_format(($without_vol_qty), 2);
                    } 
                    $categoryId = $rs1['category'];
                    $remAmount = $remAmount - $highestValue;
            }
          
        }*/

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
                 
                //  $amountArr[$deci_num] = $dynaimcPrice;
            
        }

        // echo "<pre>";
        // print_r($amountArr);
          
            /*if(in_array($amount, $amountArr)){
                foreach ($amountArr as $amount_quant => $amount_val) {
                     if($amount_val == $amount){
                        $quantity = $amount_quant;
                        $final_qty = $quantity;
                     }
                 } 
            }else{*/
                $checkVal =1;
                $lastChecked = 0;
                $i = 1;
                while($remAmount > 0){ 
                        // getting the nearest value in array
                    //echo "checkVal ".$checkVal.'<br>';
                   // echo "remAmount ".$remAmount.'<br>';  
                    if($checkVal == 1){
                        $closeAmount  = closest($amountArr, $remAmount, $salesPrice, $checkVal); 
                        $closeArr = explode("_", $closeAmount);
                        $closestAmount = $closeArr[1];
                        $checkVal = $closeArr[0];
                    }else{
                        $closestAmount = $remAmount;
                    }
                   // echo $closestAmount."-closest<br>";
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
                       /* if($checkVal == 0 && $closestAmount >= $amountArr[max(array_keys($amountArr))]){
                            $quantity += max(array_keys($amountArr));
                        }*/
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
           
       // }
            $volume_per = number_format((((($quantity * $salesPrice) - $amount) * 100) / ($quantity * $salesPrice)),2);
            //$volume_per = 0;
            $final_qty = $quantity;
       
}else{
        $productQuantity = $amount / $salesPrice;
        $final_qty = $quantity = $productQuantity;
}

       //echo $final_qty."-qty";       
        //Start happy hour discount
        $getDay = date('l').'s';
        $getTime =  date("H:i:s", strtotime(date('H:i:s') . "+$offsetSec seconds"));
    $hHDiscount = "SELECT * FROM global_happy_hour_discounts where discount_date = 'Every day' and time_from <= '".$getTime."' and time_to > '".$getTime."'";
        try
        {
            $result2 = $pdo3->prepare("$hHDiscount");
            $result2->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $happy_hour_id = 0;
        $dispDiscount1 = 0;
        while ($hHDiscount = $result2->fetch()) {
            $happy_hour_id = $hHDiscount['id'];
            if(isset($hHDiscount['discount']) && $hHDiscount['discount'] > 0){
                $dispDiscount1 = $hHDiscount['discount'];
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $productPrice = number_format($salesPrice * $dispDiscount,2);
                $discountPrice = number_format($amount * $dispDiscount,2);
                $originalAmount = $amount / $dispDiscount;
                if(!empty($amountArr)){
                    $quantity = getQuantity($originalAmount, $amountArr, $salesPrice);
                }else{
                    $quantity = number_format($amount / $productPrice, 2);
                }
            }
        }
        //End happy hour discount
        
    $hHDiscount = "SELECT * FROM global_happy_hour_discounts where discount_date = '$getDay' and time_from <= '".$getTime."' and time_to > '".$getTime."'";
        try
        {
            $result3 = $pdo3->prepare("$hHDiscount");
            $result3->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
        while ($hHDiscount = $result3->fetch()) {
            $happy_hour_id = $hHDiscount['id'];
            if(isset($hHDiscount['discount']) && $hHDiscount['discount'] > 0){
                $dispDiscount1 = $hHDiscount['discount'];
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $productPrice = number_format($salesPrice * $dispDiscount,2);
                $discountPrice = number_format($amount * $dispDiscount,2);
                $originalAmount = $amount / $dispDiscount;
                if(!empty($amountArr)){
                    $quantity = getQuantity($originalAmount, $amountArr, $salesPrice);
                }else{
                    $quantity = number_format($amount / $productPrice, 2);
                }
            }
        }
        if($happy_hour_id){
            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = $categoryId and happy_hour_id = ".$happy_hour_id;
            try
            {
                $result4 = $pdo3->prepare("$selectCategoryDiscount");
                $result4->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }

            $rowCD = $result4->fetch();
            $catDiscount = $rowCD['discount'];
            $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE purchaseid = $purchaseid and happy_hour_id = $happy_hour_id";
            try
            {
                $result5 = $pdo3->prepare("$selectPurchaseDiscount");
                $result5->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }

            $rowD = $result5->fetch();
            $prodDiscountHD = $rowD['discount'];
            $prodFijoHD = $rowD['fijo'];

            if ($prodFijoHD != 0 || $prodFijoHD != '') {
               // $total_amount = $amount + $prodFijoHD;
                $prodFijoHD = number_format((($prodFijoHD * 100) / $salesPrice),2);
                //$price = number_format($prodFijoHD,2);
                if($prodFijoHD > $dispDiscount1){
                    $dispDiscount1 = $prodFijoHD;
                    $prodDiscountHD = ($prodFijoHD) / 100;
                   // $prodDiscountHD = (100 - $prodFijoHD) / 100;
                    $productPrice = number_format($salesPrice * $prodDiscountHD,2);
                    $discountPrice = number_format($amount * $prodDiscountHD,2);
                    $originalAmount = $amount / $prodDiscountHD;
                    if(!empty($amountArr)){
                        $quantity = getQuantity($originalAmount, $amountArr, $salesPrice);
                    }else{
                        $quantity = number_format($amount / $productPrice, 2);
                    }
                }
            } else if (($prodDiscountHD != 0 || $prodDiscountHD != '')  && ($prodDiscountHD > $dispDiscount1)) {
                $dispDiscount1 = $prodDiscountHD;
                $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                $productPrice = number_format($salesPrice * $prodDiscountHD,2);
                $discountPrice = number_format($amount * $prodDiscountHD,2);
                $originalAmount = $amount / $prodDiscountHD;
                if(!empty($amountArr)){
                    $quantity = getQuantity($originalAmount, $amountArr, $salesPrice);
                }else{
                    $quantity = number_format($amount / $productPrice, 2);
                }
            } else if (($catDiscount != 0 || $catDiscount != '') && ($catDiscount > $dispDiscount1)) {
                $dispDiscount1 = $catDiscount;
                $catCurrentDiscount = (100 - $catDiscount) / 100;
                $productPrice = number_format($salesPrice * $catCurrentDiscount,2);
                $discountPrice = number_format($amount * $catCurrentDiscount,2);
                $originalAmount = $amount / $catCurrentDiscount;
                if(!empty($amountArr)){
                    $quantity = getQuantity($originalAmount, $amountArr, $salesPrice);
                }else{
                    $quantity = number_format($amount / $productPrice, 2);
                }
            }
        }
            
            if($dispDiscount1>0 || $volume_per >0){    
                echo str_replace(",", "", $quantity).'_'.$dispDiscount1.'_'.$volume_per;
            }
            
       //echo $vol_disount_string = end($price_arr);
    die;
}
