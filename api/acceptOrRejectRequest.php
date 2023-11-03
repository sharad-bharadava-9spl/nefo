<?php
// created by priyank for accept and reject request task on 28-12-2022
include('connection_chat.php');


$lang = '';
if (isset($_POST['language'])) {
    $lang = $_POST['language'];
}

//send request id and receive id required
if (isset($_REQUEST['user_id']) && isset($_REQUEST['request_id']) && isset($_REQUEST['request_status'])) {

    if ($_REQUEST['user_id'] == '' || $_REQUEST['request_id'] == '' || $_REQUEST['request_status'] == '') {
        if ($lang == 'es') {
            $response = array('flag' => 0, 'message' => "Fields can not be null.(es)");
        } else {
            $response = array('flag' => 0, 'message' => "Fields can not be null.");
        }
        echo json_encode($response);
    } else {

        //check user id sender id and receiver id valid or not.
        $selectmembers = "SELECT * from members WHERE `id` = " . $_REQUEST['user_id'] . " OR `id` = " . $_REQUEST['request_id'];

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

            $request_status = $_REQUEST['request_status'];
            $send_request = $_REQUEST['user_id'];
            $request_id = $_REQUEST['request_id'];

            //check friend Request already send or not.
            $selectfriend_request = "SELECT * from friend_request WHERE receive_user_id= '" . $_REQUEST['user_id'] . "' AND send_user_id  = '" . $_REQUEST['request_id'] . "'";
            $result = $pdo->prepare("$selectfriend_request");
            $result->execute();

            $friend_requestCount = $result->rowCount();
            if ($friend_requestCount == 0) {
                if ($lang == 'es') {
                    $response = array('flag' => 0, 'message' => "Not send request.(es)");
                } else {
                    $response = array('flag' => 0, 'message' => "Not send request.");
                }
                echo json_encode($response);
            } else {

                //accept and reject request.
                $friendrequest_row = $result->fetch();
                $fr_id = $friendrequest_row['fr_id'];
                $friendrequest_status = $friendrequest_row['fr_status'];
                
                if($friendrequest_status==2)
                {
                    if ($lang == 'es') {
                        $response = array('flag' => 0, 'message' => "Block User.(es)");
                    } else {
                        $response = array('flag' => 0, 'message' => "Block User.");
                    }
                }else{
                    if ($request_status == 1) {

                        $chat_id = time() . '' . mt_rand();
                        $date = new DateTime("now", new DateTimeZone('Asia/Calcutta'));
                        $accept_date = $date->format('Y-m-d H:i:s');
                        // $accept_date = date('Y-m-d H:i:s');
                        $fr_status = 1;
                        if ($friendrequest_status == 1) {
                            if ($lang == 'es') {
                                $response = array('flag' => 0, 'message' => "Already Request accpted.(es)");
                            } else {
                                $response = array('flag' => 0, 'message' => "Already Request accpted.");
                            }
                        } else {
                            $updateMember = sprintf("UPDATE friend_request SET chat_id = '%s', accept_date = '%s', fr_status = '%d' WHERE fr_id = '%d';", $chat_id, $accept_date, $fr_status, $fr_id);
                            try {
                                $pdo->prepare("$updateMember")->execute();
                                if ($lang == 'es') {
                                    $response = array('flag' => 1, 'message' => "Request accpted.(es)");
                                } else {
                                    $response = array('flag' => 1, 'message' => "Request accpted.");
                                }
                            } catch (PDOException $e) {
                                $response = array('flag' => '0', 'message' => $e->getMessage());
                            }
                        }
                    } else {
                        $deleteRequest  = "DELETE FROM friend_request WHERE fr_id = $fr_id";

                        try {
                            $pdo->prepare("$deleteRequest")->execute();
                            if ($lang == 'es') {
                                $response = array('flag' => 1, 'message' => "Request Denied.(es)");
                            } else {
                                $response = array('flag' => 1, 'message' => "Request Denied.");
                            }
                        } catch (PDOException $e) {
                            $response = array('flag' => '0', 'message' => $e->getMessage());
                        }
                    }
                }

                echo json_encode($response);
            }
        }
    }
} else {
    $response = array('flag' => 0, 'message' => "All fields are mendatory");
    echo json_encode($response);
}
