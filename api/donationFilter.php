<?php 
    include('connectionM.php');
    include('language/common.php'); 

    try{
    	/*1 credit , 2 debit*/
    if(!empty($_POST['language'])){
        $lang = $_POST['language'];
    }else{
        $lang = ""; 
    }

    if(!empty($_POST['user_id'])){
        $user_id = $_POST['user_id'];
    }else{
        $user_id = ""; 
    }

    if(!empty($_POST['flag'])){
        $flag = $_POST['flag'];
    }else{
        $flag = ""; 
    }

    if(!empty($user_id) || !empty($lang == 'es') || !empty($lang == 'en'))
    	{
    		$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit,maxCredit, photoExt FROM users WHERE user_id = '{$_REQUEST['user_id']}'";
			try
			{
				$result = $pdo->prepare($userDetails);
				$result->execute();
			}
			catch (PDOException $e)
			{
				if($lang=='es')
                {	
					$response = array('flag'=>'0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
				}else{
					$response = array('flag'=>'0', 'message' => 'Something went wrong');
				}
				//$response = array('flag'=>'0', 'message' => 'Something went wrong');
    			echo json_encode($response); 
			}
		
			$row = $result->fetch();
			$userid = $_REQUEST['user_id'];

			if($flag == 1){
			    $selectExpenses = "SELECT donationid, donationTime, amount, creditBefore, creditAfter, donatedTo, comment, type, operator,donation_status FROM donations WHERE userid = $userid  AND type = 1  ORDER BY donationTime DESC";
			}

			if($flag == 2){
			    $selectExpenses = "SELECT donationid, donationTime, amount, creditBefore, creditAfter, donatedTo, comment, type, operator,donation_status FROM donations WHERE userid = $userid AND type = 2  ORDER BY donationTime DESC";
			}

			try
			{
				$results = $pdo->prepare($selectExpenses);
				$results->execute();
			}
			catch (PDOException $e)
			{
				if($lang=='es')
                {	
					$response = array('flag'=>'0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
				}else{
					$response = array('flag'=>'0', 'message' => 'Something went wrong');
				}
				//$response = array('flag'=>'0', 'message' => 'Something went wrong');
    			echo json_encode($response); 
			}
			$historyArr = array();
			while ($donation = $results->fetch()) {

				$donationid = $donation['donationid'];
				$amount = $donation['amount'];
				$creditBefore = $donation['creditBefore'];
				$creditAfter = $donation['creditAfter'];
				$donatedTo = $donation['donatedTo'];
				$donationDate = date("d M Y", strtotime($donation['donationTime']));
				$donationTime = date("H:i:s A", strtotime($donation['donationTime']));
				$type = $donation['type'];
				$operatorID = $donation['operator'];

				if($donation['donation_status'] == "in_club"){
				    $donation_status = $language['in_club'];
				}else if($donation['donation_status'] == "in_app"){
			        $donation_status = $language['in_app'];
				}else{
		            $donation_status = $language['in_club'];
				}

				if ($type == 1) {
					$operationType = "Donation";
				} else if ($type == 2) {
					$operationType = "Changed credit";
				} else if ($type == 3) {
					$operationType = "Edit";
				}
				
				/*if ($operatorID == 0) {
					$operator = '';
				} else {
					$operator = getOperator($operatorID);
				}*/

				if ($donatedTo == '2') {
					$donatedTo = "Bank";
				} else if ($donatedTo == '3') {
					$donatedTo = '';
				} else if ($donatedTo == '4') {
					$donatedTo = 'CashDro';
				} else {
					$donatedTo = "Till";
				}
				$history['donationDate'] = $donationDate;
				$history['donationTime'] = $donationTime;
				$history['operationType'] = $operationType;
				$history['amount'] = $amount;
				$history['creditBefore'] = $creditBefore;
				$history['creditAfter'] = $creditAfter;
				$history['donationid'] = $donationid;
			    $history['donation_status'] = $donation_status;


				array_push($historyArr, $history);

			}

            $avalible_credit = $row['credit'];
            $maxcredit = $row['maxCredit'];

            /* get notification count */
            $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
            $resultcntdata = $pdo->prepare("$notificntdata");
            $resultcntdata->execute();
            $countnotfication = $resultcntdata->rowCount();

			if($lang=='es')
			{	
				$response = array('flag' => '1','message' => '¡El historial de donaciones se ha cargado con eñxito!','avalible_credit' => $avalible_credit,'maxcredit' => $maxcredit,'notification_count' => $countnotfication);
			}else{
				$response = array('flag' => '1','message' => 'Donation history loaded successfully!','avalible_credit' => $avalible_credit,'maxcredit' => $maxcredit,'notification_count' => $countnotfication);
			}
			//$response = array('flag' => '1','message' => 'Donation history fetched successfully.','avalible_credit' => $avalible_credit,'maxcredit' => $maxcredit,'notification_count' => $countnotfication);
            $response['data'] = array();
            $response['data']['memberno'] = $row['memberno'];
            $response['data']['first_name'] = $row['first_name'];
            $response['data']['last_name'] = $row['last_name'];
            $response['data']['paidUntil'] = $row['paidUntil'];
            $response['data']['userGroup'] = $row['userGroup'];
            $response['data']['credit'] = $row['credit'];
            $response['data']['photoExt'] = $row['photoExt'];
            $response['data']['history'] = $historyArr;
            echo json_encode($response);

    	}else{
			if($lang=='es')
			{	
				$response = array('flag'=>'0', 'message' => 'User id is required.');
			}else{
				$response = array('flag'=>'0', 'message' => 'User id is required.');
			}
    		//$response = array('flag'=>'0', 'message' => 'User id is required.');
		    echo json_encode($response); 
    	}

    }catch(PDOException $e){

      $response = array('flag'=>'0', 'message' => $e->getMessage());
      echo json_encode($response);
    }