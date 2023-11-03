<?php
include('connectionM.php'); 

    try{

        if(!empty($_POST['language'])){
            $language = $_POST['language'];
        }else{
            $language = "";
        }

        /*if(!empty($_POST['title']) && !empty($_POST['description']) && !empty($_POST['image']) && !empty($_POST['status'])){*/

            $title = $_POST['title'];
            $description = $_POST['description'];
            $image = $_POST['image'];
            $pushToken = $_POST['pushToken'];

                // API access key from Google API's Console
                define('API_ACCESS_KEY','AAAAeJy7Bm0:APA91bEF1NNNbKfZA9p0vBl0r9S1IT4nZTFodgbdcdv5CQL7PcR_zMqzVuKMQeZ6jLvF6x9DyqMUT3xpzxJbkcd2TSXci6G2S86uLdDVkmVrZs9JL78DcBKyOeXuGSp9mFPeNciDlc0C');
                $url = 'https://fcm.googleapis.com/fcm/send';
                $registrationIds = array($_POST['pushToken']);
                // prepare the message
                $message = array( 
                    'title'     => $_POST['title'],
                    'body'      => $_POST['description'],
                    'image'     => $_POST['image'],
                    'status'    => 0,
                    'create_at' => date('Y-m-d H:i:s')
                );
                $fields = array( 
                    'registration_ids' => $registrationIds, 
                    'data'             => $message
                );
                $headers = array( 
                    'Authorization: key='.API_ACCESS_KEY, 
                    'Content-Type: application/json'
                );
                $ch = curl_init();
                curl_setopt( $ch,CURLOPT_URL,$url);
                curl_setopt( $ch,CURLOPT_POST,true);
                curl_setopt( $ch,CURLOPT_HTTPHEADER,$headers);
                curl_setopt( $ch,CURLOPT_RETURNTRANSFER,true);
                curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt( $ch,CURLOPT_POSTFIELDS,json_encode($fields));
                $result = curl_exec($ch);
                curl_close($ch);
                echo $result;


             /*insert data in push notification*/
                $six_digit_random_number = mt_rand(100000, 999999);
    

                $pushQuery = "INSERT INTO pushnotification(title,description,image,status,create_at,unique_num) VALUES('$title','$description','$image','0','".date('Y-m-d H:i:s')."','$six_digit_random_number')";
                $stmt= $pdo->prepare($pushQuery);
                $pushinsert = $stmt->execute();
                if($pushinsert){
                    if($lang=='es')
                    {	
                        $response = array('flag' => '1', 'message' => 'Producto añadido al carrito con éxito.','title'=> $title ,'description' => $description, 'image' => $image ,'status' => 0 );
                    }else{
                        $response = array('flag' => '1', 'message' => 'Product added to cart successfully.','title'=> $title ,'description' => $description, 'image' => $image ,'status' => 0 );
                    }
                    //$response = array('flag' => '1', 'message' => 'Add to cart in product successfully.','title'=> $title ,'description' => $description, 'image' => $image ,'status' => 0 );
                    echo json_encode($response);

                }else{
                    if($lang=='es')
                    {	
                        $response = array('flag' => '0', 'message' => 'Algo ha ido mal, por favor inténtelo de nuevo.');
                    }else{
                        $response = array('flag' => '0', 'message' => 'Something went wrong, please try again.');
                    }
                    //$response = array('flag' => '0', 'message' => 'Product not add to cart,please try again');
                    echo json_encode($response);
                }
       /* }else{

            $response = array('flag' => '0', 'message' => 'Please add parameter all parameter.');
            echo json_encode($response);
            
        }
*/

    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }