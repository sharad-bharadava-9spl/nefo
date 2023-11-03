<?php
// created by priyank for send friend request task on 27-12-2022
include('connection_chat.php');


$lang = '';
if (isset($_POST['language'])) {
    $lang = $_POST['language'];
}

//send request id and receive id required
if (isset($_REQUEST['user_id']) && isset($_REQUEST['receive_id'])) {

    if ($_REQUEST['user_id'] == '' || $_REQUEST['receive_id'] == '') {
        if ($lang == 'es') {
            $response = array('flag' => 0, 'message' => "Fields can not be null.(es)");
        } else {
            $response = array('flag' => 0, 'message' => "Fields can not be null.");
        }
        echo json_encode($response);
    } else {

        //check user id sender id and receiver id valid or not.
        $selectmembers = "SELECT * from members WHERE `id` = " . $_REQUEST['user_id'] . " OR `id` = " . $_REQUEST['receive_id'];

        try {
            $result = $pdo->prepare("$selectmembers");
            $result->execute();
            $membersCount = $result->rowCount();
        } catch (PDOException $e) {
            $response = array('flag' => 0, 'message' => $e->getMessage());
            $membersCount = 0;
        }


        if ($membersCount <= 1) {
            if ($lang == 'es') {
                $response = array('flag' => 0, 'message' => "User not Found.(es)");
            } else {
                $response = array('flag' => 0, 'message' => "User not Found.");
            }
            echo json_encode($response);
        } else {

            $send_request = $_REQUEST['user_id'];
            $receive_request = $_REQUEST['receive_id'];

            //check friend Request already send or not.
            $selectfriend_request = "SELECT * from friend_request WHERE send_user_id = '" . $_REQUEST['user_id'] . "' AND receive_user_id = '" . $_REQUEST['receive_id'] . "'";
            $result = $pdo->prepare("$selectfriend_request");
            $result->execute();
            $friend_requestCount = $result->rowCount();
            $friend_request = $result->fetch();
            
            
            if ($friend_requestCount > 0) {
                if($friend_request['fr_status']==2)
                {
                    if ($lang == 'es') {
                        $response = array('flag' => 0, 'message' => "This user is block.(es)");
                    } else {
                        $response = array('flag' => 0, 'message' => "This user is block.");
                    }
                }else{
                    if ($lang == 'es') {
                        $response = array('flag' => 0, 'message' => "Already send request.(es)");
                    } else {
                        $response = array('flag' => 0, 'message' => "Already send request");
                    }
                }
                echo json_encode($response);
            } else {
                //check opposite user send you request.
                $selectfriend_request2 = "SELECT * from friend_request WHERE send_user_id = '" . $_REQUEST['receive_id'] . "' AND receive_user_id = '" . $_REQUEST['user_id'] . "'";
                $result2 = $pdo->prepare("$selectfriend_request2");
                $result2->execute();
                $friend_requestCount2 = $result2->rowCount();
                $friend_request2 = $result2->fetch();
                if ($friend_requestCount2 > 0) {
                    if($friend_request2['fr_status']==2)
                    {
                        if ($lang == 'es') {
                            $response = array('flag' => 0, 'message' => "This user block You.(es)");
                        } else {
                            $response = array('flag' => 0, 'message' => "This user block You.");
                        }
                    }else{
                        if ($lang == 'es') {
                            $response = array('flag' => 0, 'message' => "This user already send you request.(es)");
                        } else {
                            $response = array('flag' => 0, 'message' => "This user already send you request.");
                        }
                    }
                    echo json_encode($response);
                } else {
                    //send request
                    $date = new DateTime("now", new DateTimeZone('Asia/Calcutta') );
                    $accept_date=$date->format('Y-m-d H:i:s');
                    $fr_status=0;
                    //$accept_date = date('Y-m-d H:i:s');
                    $insertAppRequest = sprintf(
                        "INSERT INTO friend_request (send_user_id, receive_user_id,send_date,fr_status) VALUES ('%s', '%s', '%s', '%d');",
                        $send_request,
                        $receive_request,
                        $accept_date,
                        $fr_status,
                    );

                    try {
                        $pdo->prepare("$insertAppRequest")->execute();
                        if ($lang == 'es') {
                            $response = array('flag' => 1, 'message' => 'Your Request sent successfully.(es)');
                        } else {
                            $response = array('flag' => 1, 'message' => 'Your Request sent successfully.');
                        }
                    } catch (PDOException $e) {
                        $response = array('flag' => 0, 'message' => $e->getMessage());
                    }
                    echo json_encode($response);
                }
            }
        }
    }
} else {
    $response = array('flag' => 0, 'message' => "All fields are mendatory");
    echo json_encode($response);
}
