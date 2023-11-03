<?php 

require_once 'cOnfig/connection.php';
require_once 'cOnfig/languages/common.php';

session_start();
$accessLevel = '3';

getSettings();
function getHighest($qty,$val_array){
    foreach ($val_array as $key => $value) {
        if($qty >= (int)$key){
            return $key;
        }
    }
    return $qty;
}
if($_GET['id'] && $_GET['qty']){
    $purchaseid = $_GET['id'];
    $qty = $_GET['qty'];
   
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
/* echo "<pre>";
 print_r($arrayKey);*/
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
    //while($rs = $result->fetch()){
    if($numItems > 0){
    while ($remQty > 0) {
        if(isset($rs1['purchaseid'])){
            //if($applyOn <= $qty){
                $highestValue = getHighest($remQty,$arrayKey);
                if(isset($arrayKey[$highestValue])){
                    $final_amount += $arrayKey[$highestValue.'_amount'];
                    $price += $arrayKey[$highestValue.'_amount'];
                }else if($remQty > 0){
                    $without_vol_qty = $remQty;
                    $without_vol_price = $without_vol_qty * $salesPrice;
                    $final_amount += number_format(($without_vol_price), 2);
                    $price += number_format(($without_vol_price), 2);
                } 
               
                $remQty = $remQty - $highestValue;
            //     $applyOn += $remQty;
            // }

        }
      //  echo '____'.$price;
        //echo "<br>";
    }

      
    
        $volume_per = number_format((((($qty * $salesPrice) - $price) * 100) / ($qty * $salesPrice)),2);
        $final_amount = $price;
    }else{
        $productPrice = $salesPrice * $qty;
        $final_amount = $price = $productPrice;
    }
       
       
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
                $price = number_format($final_amount * $dispDiscount,2);
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
                $price = number_format($final_amount * $dispDiscount,2);
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
                $prodFijoHD = number_format((($prodFijoHD * 100) / $final_amount),2);
                //$price = number_format($prodFijoHD,2);
                if($prodFijoHD > $dispDiscount1){
                    $dispDiscount1 = $prodFijoHD;
                    $prodDiscountHD = (100 - $prodFijoHD) / 100;
                    $price = number_format($final_amount * $prodDiscountHD,2);
                }
            } else if (($prodDiscountHD != 0 || $prodDiscountHD != '')  && ($prodDiscountHD > $dispDiscount1)) {
                $dispDiscount1 = $prodDiscountHD;
                $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                $price = number_format($final_amount * $prodDiscountHD,2);
            } else if (($catDiscount != 0 || $catDiscount != '') && ($catDiscount > $dispDiscount1)) {
                $dispDiscount1 = $catDiscount;
                $catCurrentDiscount = (100 - $catDiscount) / 100;
                $price = number_format($final_amount * $catCurrentDiscount,2);
            }
        }
            
            if($dispDiscount1>0 || $volume_per >0){    
                echo str_replace(",", "", $price).'_'.$dispDiscount1.'_'.$volume_per;
            }
            
       //echo $vol_disount_string = end($price_arr);
    die;
}
