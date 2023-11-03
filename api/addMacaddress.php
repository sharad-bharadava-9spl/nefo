<?php 
    include('connectionM.php');

	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}

	try{
        // if(!empty($_POST['language'])){
        //     $lang = $_POST['language'];
        // }else{
        //     $lang = ""; 
        // }

      //print_r($_POST['macadd_request']);
		if(!empty($_POST['macadd_request'])){
            $macadd_request = json_decode($_POST['macadd_request']);
        }else{
            $macadd_request = ""; 
        }
		// print_r($_POST['macadd_request']);
		// exit;
        $deleteMacaddress = "DELETE FROM moblie_macaddress";
        $result = $pdo->prepare("$deleteMacaddress");
	    $result->execute();
		$clubnm = $_POST['club_name'];

	    $updateMacaddress = "UPDATE systemsettings SET topcredit_option = 0,preorder_option = 0,showprice_option = 0,showmenu_option = 0 WHERE domain = '$clubnm'";
	    $result = $pdo->prepare("$updateMacaddress");
	    $result->execute();
        
        if(!empty($lang == 'es') || !empty($lang == 'en')){
        	
        	$domain = $_POST['club_name'];
        	$checkSystemsetting = "SELECT * FROM systemsettings WHERE domain = '$domain'";
			$result = $pdo->prepare("$checkSystemsetting");
			$result->execute();
			$clubSystem = $result->fetch();
			$domain = $clubSystem['domain'];
			$domainid = $clubSystem['id'];

			/*check system wise multiple data check*/
	        $checkDomainMulitpleData = "SELECT * FROM moblie_macaddress WHERE domain_name = '$domain'";
	        $result = $pdo->prepare("$checkDomainMulitpleData");
	        $result->execute();
	        $macarr = array();
	        if($result->rowCount() > 0){
	            while($macaddress = $result->fetch()){
	            	$macarr[] = $macaddress['mac_address'];
	            }
	        }
/*
			if(!empty($macadd_request->Top_up_credit)){
				$topcredit = $macadd_request->Top_up_credit;
			}else{
				$topcredit = 0;
			}

			if(!empty($macadd_request->Pre_order)){
				$preorder = $macadd_request->Pre_order;
			}else{
				$preorder = 0;
			}

			if(!empty($macadd_request->Show_price)){
				$showprice = $macadd_request->Show_price;
			}else{
				$showprice = 0;
			}

			if(!empty($macadd_request->user_id)){
				$user_id = $macadd_request->user_id;
			}else{
				$user_id = "";
			}*/			
			if(isset($_POST['Top_up_credit'])){
				$topcredit = $_POST['Top_up_credit'];
			}else{
				$topcredit = 0;
			}

			if(isset($_POST['Pre_order'])){
				$preorder = $_POST['Pre_order'];
			}else{
				$preorder = 0;
			}

			if(isset($_POST['Show_price'])){
				$showprice = $_POST['Show_price'];
			}else{
				$showprice = 0;
			}

			if(isset($_POST['Show_menu'])){
				$showmenu = $_POST['Show_menu'];
			}else{
				$showmenu = 0;
			}

			if(isset($_POST['user_id'])){
				$user_id = $_POST['user_id'];
			}else{
				$user_id = "";
			}

			//echo $showprice;

		    $updateMacaddress = "UPDATE systemsettings SET topcredit_option = '$topcredit',preorder_option = '$preorder',showprice_option = '$showprice',showmenu_option = '$showmenu' WHERE domain = '$clubnm'";
		    //echo $updateMacaddress; exit;
		    $result = $pdo->prepare("$updateMacaddress");
		    $updateQuery = $result->execute();
		    if($updateQuery){
				if($lang=='es')
				{	
					$response = array('flag' => '1', 'message' => 'Los ajustes guardados con éxito.');
				}else{
					$response = array('flag' => '1', 'message' => 'Settings saved successfully.');
				}
		    	//$response = array('flag' => '1', 'message' => 'Settings updated successfully.');
		    }
		    
			$checkUserDetail = "SELECT * FROM users WHERE user_id = '$user_id'";
			$result1 = $pdo->prepare("$checkUserDetail");
			$result1->execute();
			$userData = $result1->fetch();
            $currentDate = date('Y-m-d H:i:s');
            $returnarr = '';
            $macduplicatedata = '';
            $array_temp = array();
            if(!empty($macadd_request->data)){
				foreach ($macadd_request->data as $key => $macData) {
					$mac_address = $macData->macaddress;
		            if(!in_array($mac_address, $macarr)){
		            	if (!in_array($macData, $array_temp)){
		            		 $array_temp[] = $macData;
							if($userData['userGroup'] == 1){
                             
							    $insertMacAddress ="INSERT INTO moblie_macaddress(domain_id,domain_name,mac_address,status,created_at,updated_at) VALUES('$domainid','$domain','$mac_address','0','$currentDate','$currentDate')";
							    $insertdata = $pdo->prepare($insertMacAddress);
							    $insertQuery = $insertdata->execute();


							    if($insertQuery){

							    	/* get notification count */
			                        $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
				                    $resultcntdata = $pdo->prepare("$notificntdata");
				                    $resultcntdata->execute();
				                    $countnotfication = $resultcntdata->rowCount();

							    	//$response = array('flag' => '1', 'message' => 'System setting data added successfully.','notification_count' => $countnotfication);
							    	if($lang=='es')
									{
										$response = array('flag' => '1', 'message' => 'Los ajustes guardados con éxito.','notification_count' => $countnotfication);
									}else{
										$response = array('flag' => '1', 'message' => 'Settings saved successfully.','notification_count' => $countnotfication);
									}
								}else{
									if($lang=='es')
									{	
										$response = array('flag' => '0', 'message' => 'No se ha guardado la configuración, inténtelo de nuevo.');
									}else{
										$response = array('flag' => '0', 'message' => 'System setting not stored , please try again.');
									}
							    	//$response = array('flag' => '0', 'message' => 'System setting not stored , please try again.');
							    }

							}else{
								if($lang=='es')
								{	
									$response = array('flag' => '0', 'message' => 'No tienes privilegios del administrador.');
								}else{
									$response = array('flag' => '0', 'message' => 'You do not have admin privileges.');
								}
								//$response = array('flag' => '0', 'message' => 'You have not admin.');
							}
					    }else{

					    	$macduplicatedata .= $macData->macaddress .',';
							if($lang=='es')
							{	
								$response = array('flag' => '0', 'message' => 'Mac-address se agrega el mismo valor,'.' '.rtrim($macduplicatedata,',').' '.' por favor agregue otra mac-address.');
							}else{
								$response = array('flag' => '0', 'message' => 'Mac-address are added same value,'.' '.rtrim($macduplicatedata,',').' '.' please another mac-address add.');
							}
					    	//$response = array('flag' => '0', 'message' => 'Mac-address are added same value,'.' '.rtrim($macduplicatedata,',').' '.' please another mac-address add.');
					    }
					}else{
						
						$result =array_search($mac_address,$macarr);

	                    $returnarr .= $macarr[$result].",";
						if($lang=='es')
						{	
							$response = array('flag' => '0', 'message' => 'Mac-address ya existe,'.' '.rtrim($returnarr,',').' '.'por favor agregue otra mac-address.');
						}else{
							$response = array('flag' => '0', 'message' => 'Mac-address already exist,'.' '.rtrim($returnarr,',').' '.'please another mac-address add.');
						}
						//$response = array('flag' => '0', 'message' => 'Mac-address already exist,'.' '.rtrim($returnarr,',').' '.'please another mac-address add.');
					}
				}
			}
            echo json_encode($response);
        }else{
			if($lang=='es')
			{	
				$response = array('flag' => '0', 'message' => 'Todos los campos son obligatorios.');
			}else{
				$response = array('flag' => '0', 'message' => 'All fields are mandatory.');
			}
            //$response = array('flag' => '0', 'message' => 'Please add parameter all parameter.');
            echo json_encode($response);
        }
    }catch(PDOException $e){

      $response = array('flag'=>'0', 'message' => $e->getMessage());
      echo json_encode($response);
    }