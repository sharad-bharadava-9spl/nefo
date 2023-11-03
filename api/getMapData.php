<?php
include('connectionM_club.php'); 


if(!empty($_POST['language'])){
    $lang = $_POST['language'];
}else{
    $lang = ""; 
}

function getGeoCode($address)
{
        // geocoding api url
        $url = "https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyB37T3DT1fLza9MGhJgDYbqh8oODJTuYsk";
        // send api request
        $geocode = file_get_contents($url);
        $json = json_decode($geocode);
        if(!empty($json->results)){
            $data['lat'] = $json->results[0]->geometry->location->lat;
            $data['lng'] = $json->results[0]->geometry->location->lng;
        }else{
            $data['error'] = "Please provide the valid address!";
        }
        return $data;
}

    try{


        if(!empty($_POST['club_name'])){
            $club_name = $_POST['club_name'];
        }else{
            $club_name = ""; 
        }
        

        if(!empty($club_name) && $_REQUEST['club_name'] != '' ){

            // get club id

            $getClubId = "SELECT customer from db_access WHERE domain = '$club_name'";
            $result = $pdo->prepare("$getClubId");
            $result->execute();
            $row = $result->fetch();

            $club_id = $row['customer'];

            // get the address of club

            $selectClubAddress = "SELECT street, streetnumber, flat, city, state, country FROM customers where number = ".$club_id;
            $club_results = $pdo2->prepare("$selectClubAddress");
            $club_results->execute();
            $club_row = $club_results->fetch();
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
            if(!empty($json->results)){
                $data['lat'] = $json->results[0]->geometry->location->lat;
                $data['lng'] = $json->results[0]->geometry->location->lng;
                $data['club_address'] = $address_str;
                $response = array('flag'=> 1, 'data'=> $data);
                echo json_encode($response);
            }else{

                if($lang=='es')
                {	
                    $response = array('flag'=>'0', 'message' => 'Invalid address - or address unavailable.');
                }else{
                    $response = array('flag'=>'0', 'message' => 'Invalid address - or address unavailable.');
                }
                //$response = array('flag'=>'0', 'message' => 'Please provide the valid address or address is not available for this club.');
                echo json_encode($response);
            }


        }else{
            if($lang=='es')
            {	
                $response = array('flag'=>'0', 'message' => 'El nombre del club es obligatorio.');
            }else{
                $response = array('flag'=>'0', 'message' => 'Club name is required.');
            }
            //$response = array('flag'=>'0', 'message' => 'Club name is required.');
            echo json_encode($response);
        }
        


    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }