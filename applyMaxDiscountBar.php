<?php 

function getHighest($qty,$val_array){
    foreach ($val_array as $key => $value) {
        if($qty >= (int)$key){
            return $key;
        }
    }
    return $qty;
}
/* Find the closest for amount  */
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
// parameters need to pass to check discounts
/*
	$user_id = 1382;
	$purchaseid = 761;
	$categoryId = 1;
	$salesPrice = 7;
	$qty = 5;
	$amount = 0;
*/
function checkMaxDisocunt($user_id, $purchaseid, $categoryId, $salesPrice, $qty=0, $amount=0, $offsetSec = 0){
		global $pdo3;
		// fetch user details
		$userDetails = "SELECT usageType FROM users WHERE user_id = {$user_id}";
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
			$usageType = $row['usageType'];

		// create an arry to store applied discounts	
		$discountArr = [];
		// fetch all disocunts

		// -------General Medical Discounts--------
		$generalMediDiscount = 0;



		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];

		if($usageType == 1){
			if ($medicalDiscountPercentage == 1) {
				$medicalDiscountPercentage = $medicalDiscount;
				$generalMediDiscount = $medicalDiscount;
				$medicalDiscount = '';
			}else if(isset($medicalDiscount) && $medicalDiscountPercentage == 0){
				$generalMediDiscount =  number_format((($medicalDiscount * 100) / $salesPrice),2);
			}else{
				$generalMediDiscount =0;
			}
		}
		//general_medical_disocunt
		$discountArr[0]['discount'] = $generalMediDiscount; 
		$discountArr[0]['discountType'] =2;

		// fetch purchase medical disocunt

		$selectPurchaseMedicalDiscount = "SELECT medDiscount FROM b_purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectPurchaseMedicalDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$rowPMD = $result->fetch();
			//purchase_medical_disocunt
		if($usageType == 1){
			$discountArr[1]['discount'] = $rowPMD['medDiscount'];
			$discountArr[1]['discountType'] =2;
		}

		// -------Individual Disocunt Dispensary START----------

		// Look up dispensary and bar disocunt for user
		$selectGeneralUserDisocunt = "SELECT discount, discountBar, usergroup2 FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$selectGeneralUserDisocunt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$rowGUD = $result->fetch();
			$userDisocunt = $rowGUD['discount'] == '' ? 0 : $rowGUD['discount'];
			$userDisocuntBar = $rowGUD['discountBar'] == '' ? 0 : $rowGUD['discountBar'];
			$userGroup = $rowGUD['usergroup2'];
			$discountType = 1;
			//general_user_discount
			$discountArr[2]['discount'] = $userDisocuntBar;
			$discountArr[2]['discountType'] =1;

		// Look up category dicount for user

		$selectUserCategoryDiscount = "SELECT discount FROM b_catdiscounts WHERE user_id = $user_id AND categoryid = $categoryId";
		try
		{
			$result = $pdo3->prepare("$selectUserCategoryDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$rowUCD = $result->fetch();
			//user_cat_discount
			$discountArr[3]['discount'] = $rowUCD['discount'] == '' ? 0 : $rowUCD['discount'];	
			$discountArr[3]['discountType'] =1;
		// Look up for purchase disocunt

		$selectUserPurchaseDiscount = "SELECT discount, fijo FROM b_inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectUserPurchaseDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$rowUPD = $result->fetch();
			//user_purchase_discount
			$discountArr[4]['discount'] = $rowUPD['discount'] == '' ? 0 :  $rowUPD['discount'];
			$discountArr[4]['discountType'] =1;
			$userFixedPriceDiscount = $rowUPD['fijo'] == '' ? 0 : $rowUPD['fijo'];
			$userFixedDiscount = number_format((($userFixedPriceDiscount * 100) / $salesPrice),2);
			//user_purchase_fixed_discount
			$discountArr[5]['discount'] = is_nan($userFixedDiscount) ? 0 : $userFixedDiscount;
			$discountArr[5]['discountType'] =1;

		// -------Individual Disocunt Dispensary END----------	

		// ---------Usergroup Disocunts Dipensary START----------

		// Look up dispensary disocunt for usergroup

		$selectGeneralUserGroupDisocunt = "SELECT * FROM usergroup_discounts WHERE usergroup_id = $userGroup";
		try
		{
			$result = $pdo3->prepare("$selectGeneralUserGroupDisocunt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$rowGUGD = $result->fetch();
		    $usergroup_discount_id = $rowGUGD['id'];
	        $usergroup_discount_price = $rowGUGD['b_discount_price'];
	        $usergroup_discount_percentage = $rowGUGD['b_discount_percentage'];
	        $usergroup_dicount_fixed =  number_format((($usergroup_discount_price * 100) / $salesPrice),2);
	        //usergroup_dicount
	        $discountArr[6]['discount'] = $rowGUGD['b_discount_percentage'] == '' ? 0 : $rowGUGD['b_discount_percentage'];
	        $discountArr[6]['discountType'] =4;
	        //usergroup_dicount_fixed
	        $discountArr[7]['discount'] = is_nan($usergroup_dicount_fixed) ? 0 : $usergroup_dicount_fixed;
	        $discountArr[7]['discountType'] =4;

	     if($usergroup_discount_id){
	       // Look Up category discount for usergroup
		     $selectUgCategoryDiscount = "SELECT discount FROM b_catdiscounts WHERE usergroup_discount_id = $usergroup_discount_id AND categoryid = $categoryId";
			try
			{
				$result = $pdo3->prepare("$selectUgCategoryDiscount");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$rowUGCD = $result->fetch();
				//usergroup_cat_discount
				$discountArr[8]['discount'] = $rowUGCD['discount'];
				$discountArr[8]['discountType'] =4;

			// Look Up for product disocunts in usergroup
			$selectUgPurchaseDiscount = "SELECT discount, fijo FROM b_inddiscounts WHERE usergroup_discount_id = $usergroup_discount_id AND purchaseid = $purchaseid";
			try
			{
				$result = $pdo3->prepare("$selectUgPurchaseDiscount");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

			$rowUGPD = $result->fetch();
				//usergroup_purchase_discount
				$discountArr[9]['discount'] = $rowUGPD['discount'] == '' ? 0 :  $rowUGPD['discount'];
				$discountArr[9]['discountType'] =4;
				$userGroupFixedPriceDiscount = $rowUGPD['fijo'] == '' ? 0 : $rowUGPD['fijo'];
				$userGroupFixedDiscount = number_format((($userGroupFixedPriceDiscount * 100) / $salesPrice),2);
				//usergroup_purchase_fixed_discount
				$discountArr[10]['discount'] = is_nan($userGroupFixedDiscount) ? 0 : $userGroupFixedDiscount;
				$discountArr[10]['discountType'] =4;

		}
		// ---------Usergroup Disocunts Dipensary END---------- 


		// ---------Happy Hour Disocunts Dipensary START--------

	  	$hHFlag = 0;
	    $getDay = date('l').'s';
	    $getTime = date("H:i:s", strtotime(date('H:i:s') . "+$offsetSec seconds"));

	    // fetch every day happy hour disocunts
		$hHDiscountEveryday = "SELECT * FROM global_happy_hour_discounts where discount_date = 'Every day' and time_from <= '".$getTime."' and time_to > '".$getTime."'";
		    try
		    {
		        $result = $pdo3->prepare("$hHDiscountEveryday");
		        $result->execute();
		    }
		    catch (PDOException $e)
		    {
		        $error = 'Error fetching user: ' . $e->getMessage();
		        echo $error;
		        exit();
		    }
		    $happy_hour_id = 0;
		    while ($hHDiscount = $result->fetch()) {
		        $happy_hour_id = $hHDiscount['id'];
		        if(isset($hHDiscount['discount_bar']) && $hHDiscount['discount_bar'] > 0){
		        	//happy_hour_everyday_disocunt
		            $discountArr[11]['discount'] = $hHDiscount['discount_bar'];
		            $discountArr[11]['discountType'] =3;
		        }
		    }

		  // Look Up Day wise happy hour discounts
		  
		$hHDiscount = "SELECT * FROM global_happy_hour_discounts where discount_date = '$getDay' and time_from <= '".$getTime."' and time_to > '".$getTime."'";
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
	        while ($hHDiscount = $result->fetch()) {
	            $happy_hour_id = $hHDiscount['id'];
	            if(isset($hHDiscount['discount_bar']) && $hHDiscount['discount_bar'] > 0){
	            	//happy_hour_day_disocunt
	                $discountArr[12]['discount'] = $hHDiscount['discount_bar'];
	                $discountArr[12]['discountType'] =3;
	            }
	        }

	    if($happy_hour_id){

	    	// Look up for category divount for happy hours

	    	$selectCategoryHHDiscount = "SELECT discount FROM b_catdiscounts WHERE categoryid = $categoryId and happy_hour_id = ".$happy_hour_id;
	            try
	            {
	                $result = $pdo3->prepare("$selectCategoryHHDiscount");
	                $result->execute();
	            }
	            catch (PDOException $e)
	            {
	                $error = 'Error fetching user: ' . $e->getMessage();
	                echo $error;
	                exit();
	            }

	            $rowCHHD = $result->fetch();
	            	//happy_hour_cat_disocunt
	            	$discountArr[13]['discount'] = $rowCHHD['discount'];
	            	$discountArr[13]['discountType'] =3;

	         // Look up for purchase divount for happy hours 
	           	
	        $selectPurchaseHHDiscount = "SELECT discount, fijo FROM b_inddiscounts WHERE purchaseid = $purchaseid and happy_hour_id = $happy_hour_id";
	            try
	            {
	                $result = $pdo3->prepare("$selectPurchaseHHDiscount");
	                $result->execute();
	            }
	            catch (PDOException $e)
	            {
	                $error = 'Error fetching user: ' . $e->getMessage();
	                echo $error;
	                exit();
	            }

	            $rowPHHD = $result->fetch();
	            	//happy_hour_purchase_discount
		            $discountArr[14]['discount'] = $rowPHHD['discount'] == '' ? 0 :  $rowPHHD['discount'];
		            $discountArr[14]['discountType'] =3;
					$happyHourFixedPriceDiscount = $rowPHHD['fijo'] == '' ? 0 : $rowPHHD['fijo'];
					$happyHourFixedDiscount = number_format((($happyHourFixedPriceDiscount * 100) / $salesPrice),2);
					//happy_hour_purchase_fixed_discount
					$discountArr[15]['discount'] = is_nan($happyHourFixedDiscount) ? 0 : $happyHourFixedDiscount;
					$discountArr[15]['discountType'] =3;    	

	    }
	    // ---------Happy Hour Discounts Dipensary END--------   

	    // ---------Volume Discounts Dipensary START--------

	    if($qty){
			    $selectSales = "SELECT * FROM b_volume_discounts WHERE purchaseid =". $purchaseid." order by units desc";
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
			    if($numItems > 0){
				    while ($remQty > 0) {
				        if(isset($purchaseid)){
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
			    $discountArr[16]['discount'] = number_format((((($qty * $salesPrice) - $price) * 100) / ($qty * $salesPrice)),2);
			    $discountArr[16]['discountType'] =6;    
		    }
		}

		 // ---------Volume Discounts Dipensary END--------

		 // ---------Volume Amount Discounts Dipensary START--------

		if($amount){
		    $selectSales = "SELECT * FROM b_volume_discounts WHERE purchaseid =". $purchaseid." order by units desc";
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
		    $discount_arr = [];
		    $amountArr = [];
		    while($rs_arr = $result_arr->fetch()){
		        $arrayKey[$rs_arr['units']] = $rs_arr['units'];
		        $arrayKey[$rs_arr['units'].'_amount'] = $rs_arr['amount'];
		        $discount_arr[$rs_arr['amount']] = $rs_arr['units'];
		    }


		    $selectSales = "SELECT purchaseid,category,salesPrice FROM b_purchases WHERE purchaseid =". $purchaseid;
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
		        for($k=min($discount_arr); $k<= max($discount_arr); $k++){
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
		           
		      
		        $discountArr[17]['discount'] = number_format((((($quantity * $salesPrice) - $amount) * 100) / ($quantity * $salesPrice)),2);
		        $discountArr[17]['discountType'] =6;

		       
			}
		}
		// ---------Volume Amount Discounts Dipensary END--------
		$applied_discounts = [];

		$x = 0;
		foreach($discountArr as $discountValue){
			$disocunt_type = $discountValue['discountType'];
			$value = 0;
			if(isset($discountValue['discount'])){
				$value = (float)$discountValue['discount'];
			}
			if(!empty($value)){
				$applied_discounts[$x]['discount'] = $value;
				$applied_discounts[$x]['discountType'] = $disocunt_type;
				$x++;
			}
		}
		if(!empty($applied_discounts)){
			$max_applied_dicount_arr = max($applied_discounts);

			$apply_discount = $max_applied_dicount_arr['discount'];
			$apply_discountType = $max_applied_dicount_arr['discountType'];

			return $apply_discount."_".$apply_discountType;
		}else{
			return false;
		}
}
