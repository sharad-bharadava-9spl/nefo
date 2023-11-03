<?php 
	// created by konstant for member register task on 12-01-2022
    include('connectionM_club.php');

	if(!empty($_POST['language'])){
		$lang = $_POST['language'];
	}else{
		$lang = ""; 
	}

    if(isset($_POST['member_id'])){

    	if($_POST['member_id'] == ''){
			if($lang=='es')
			{	
				$response = array('flag'=>'0', 'message' => "Todos los campos son obligatorios.");
			}else{
				$response = array('flag'=>'0', 'message' => "All fields are mandatory.");
			}
    		//$response = array('flag'=>'0', 'message' => "Fields can not be null.");
			echo json_encode($response); 
			die();
    	}else{
    		// check memebr id

			$checkMember = "SELECT id FROM members WHERE id=".$_POST['member_id'];
			try{
				$mem_results = $pdo->prepare("$checkMember");
				$mem_results->execute();
			}catch (PDOException $e){
					$response = array('flag'=>'0', 'message' => $e->getMessage());
					echo json_encode($response); 
					die();
			}

			$memberCount = $mem_results->rowCount();
			if($memberCount == 0){
				if($lang=='es')
				{	
					$response = array('flag'=>'0', 'message' => "El socio no ha sido aprobado.");
				}else{
					$response = array('flag'=>'0', 'message' => "Member is not approved.");
				}
				//$response = array('flag'=>'0', 'message' => "Member is not valid!");
				echo json_encode($response); 
				die();
			}

			// fetch club lists

			$userData = "SELECT * FROM `app_requests` WHERE member_id = '".$_POST['member_id']."'";
            $resultuser = $pdo->prepare("$userData");
            $resultuser->execute();
            $response['data'] = array();
            $new_arr = array();
                if($resultuser->rowCount() > 0){
					if($lang=='es')
					{	
						$response = array('flag' => '1','message' => '¡Club encontrado con éxito!' );
					}else{
						$response = array('flag' => '1','message' => 'Club Found Successfull!' );
					}
                    //$response = array('flag' => '1','message' => 'Club Found Successfull' );
                    
                    while ($club = $resultuser->fetch()) {

                    	$selectClubNumber = "SELECT customer FROM db_access WHERE club_code = '".$club['club_code']."'";
                    	$result_customer = $pdo->prepare("$selectClubNumber");
            			$result_customer->execute();
            			$result_row = $result_customer->fetch();
            			$customer_number = $result_row['customer'];

            			// fetch club data from nefos 

            			$selectCLub ="SELECT longName, shortName, street, streetnumber, flat, city, state, country FROM customers WHERE number =".$customer_number;
            			$result_club = $pdo2->prepare("$selectCLub");
            			$result_club->execute();
            			$club_row = $result_club->fetch();
            			//$registeredSince = $club_row['registeredSince'];
            			$longName = $club_row['longName'];
            			$shortName = $club_row['shortName'];
            			$street = $club_row['street'];
            			$streetnumber = $club_row['streetnumber'];
            			$flat = $club_row['flat'];
            			$city = $club_row['city'];
            			$state = $club_row['state'];
            			$country = $club_row['country'];

			          	$address = $street.", ".$streetnumber.", ".$flat.", ".$city.", ".$state.", ".$country;
			            $address = str_replace(' ', '+', $address);
			            $addres_arr = array($street, $streetnumber, $flat, $city, $state, $country);

			            $addres_arr = array_filter($addres_arr); 

			            $address_str = implode(",", $addres_arr);
						$url = "https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyB37T3DT1fLza9MGhJgDYbqh8oODJTuYsk";
			            // send api request
			            $geocode = file_get_contents($url);
			            $json = json_decode($geocode);
			            $lat = 0;
			            $lng = 0;
			            $club_address = "";
						if(!empty($json->results)){
			                $lat = $json->results[0]->geometry->location->lat;
			                $lng = $json->results[0]->geometry->location->lng;
			                $club_address = $address_str;
			            }

            			// fetch club details from club db
            				if(isset($club['club_name']) && $club['club_name'] != '' )
						    {
								try
								{
									$result = $pdo->prepare("SELECT db_pwd FROM db_access WHERE domain = :domain");
									$result->bindValue(':domain', $club['club_name']);
									$result->execute();
									//print_r($results); exit;
								}
								catch (PDOException $e)
								{
									$response = array('flag'=>'0', 'message' => $e->getMessage());
								   	echo json_encode($response); 
								}

								$row = $result->fetch();
							    $db_name = 'ccs_'.$club['club_name'];
								$db_user = $db_name . "u";
								$db_pwd = $row['db_pwd'];;
/*								$db_user = "root";
								$db_pwd = "";*/
							    try	{
									//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
							 		$pdo_club = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
							 		$pdo_club->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							 		$pdo_club->exec('SET NAMES "utf8"');
								}
								catch (PDOException $e)	{
									
							  		$response = array('flag'=>'0', 'message' => $e->getMessage());
									echo json_encode($response);
									die();
							 		
								}

								// fetch usergroup and bantime from club users table
								try
								{
									$result_club = $pdo_club->prepare("SELECT registeredSince, userGroup, banTime FROM users WHERE app_member = :app_member");
									$result_club->bindValue(':app_member', $_POST['member_id']);
									$result_club->execute();
									$club_userCount = $result_club->rowCount();
									$club_user = $result_club->fetch();
									//print_r($results); exit;
								}
								catch (PDOException $e)
								{
									$response = array('flag'=>'0', 'message' => $e->getMessage());
								   	echo json_encode($response); 
								   	die();
								}

							}
							else
							{
								if($lang=='es')
								{	
									$response = array('flag'=>'0', 'message' => "El nombre del club es obligatorio.");
								}else{
									$response = array('flag'=>'0', 'message' => "Club name is required.");
								}
								//$response = array('flag'=>'0', 'message' => "Club name is required.");
								echo json_encode($response);
								die();
							}
						$banTime = 	'';
						$userGroup = 	0;
						if($club_userCount > 0){
							if($club_user['banTime'] != ''){
								$banTime = $club_user['banTime'];
							}
							$userGroup = $club_user['userGroup'];
						}	
                        $new_arr['club_name']  = $club['club_name'];
                        $new_arr['club_code']  = $club['club_code'];
                        $new_arr['club_approve']  = $club['allow_request'];
                        $new_arr['registeredSince']  = $club_user['registeredSince'];
                        $new_arr['shortName']  = $shortName;
                        $new_arr['city']  = $city;
                        $new_arr['state']  = $state;
                        $new_arr['country']  = $country;
                        $new_arr['lat']  = $lat;
                        $new_arr['lng']  = $lng;
                        $new_arr['club_address']  = $club_address;
                        $new_arr['usergroup']  = $userGroup;
                        $new_arr['banTIme']  = $banTime;

                        $response['data'][]    = $new_arr;
                    }
                    echo json_encode($response);

                }else{
					if($lang=='es')
					{	
						$response = array('flag' => '0', 'message' => 'Club no encontrado.');
					}else{
						$response = array('flag' => '0', 'message' => 'Club Not Found.');
					}
                    //$response = array('flag' => '0', 'message' => 'Club Not Found.');
                    echo json_encode($response);
                    die();
                }
    	}

    }else{
		if($lang=='es')
		{	
			$response = array('flag' => '0', 'message' => 'Todos los campos son obligatorios.');
		}else{
			$response = array('flag' => '0', 'message' => 'All fields are mandatory.');
		}
    	//$response = array('flag'=>'0', 'message' => "All fields are mendatory");
		echo json_encode($response); 
    }
    

