<?php
// created by priyank for send friend request task on 27-12-2022
include('connection_chat.php');


$lang = '';
if (isset($_POST['language'])) {
    $lang = $_POST['language'];
}

//send request id and receive id required
if (isset($_REQUEST['user_id']) && isset($_REQUEST['block_id']) && isset($_REQUEST['block_status'])) {

    if ($_REQUEST['user_id'] == '' || $_REQUEST['block_id'] == '' || $_REQUEST['block_status'] == '') {
        if ($lang == 'es') {
            $response = array('flag' => 0, 'message' => "Fields can not be null.(es)");
        } else {
            $response = array('flag' => 0, 'message' => "Fields can not be null.");
        }
        echo json_encode($response);
    } else {

        //check user id sender id and receiver id valid or not.
        $selectmembers = "SELECT * from members WHERE `id` = " . $_REQUEST['user_id'] . " OR `id` = " . $_REQUEST['block_id'];

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

            $send_id = $_REQUEST['user_id'];
            $block_id = $_REQUEST['block_id'];
            $found=0;
            $block_status = $_REQUEST['block_status'];
            $fr_status=null;
            //check friend Request already send or not.
            $block_request = "SELECT * from friend_request WHERE send_user_id = '" . $_REQUEST['user_id'] . "' AND receive_user_id = '" . $_REQUEST['block_id'] . "'";
            $result = $pdo->prepare("$block_request");
            $result->execute();
            $block_Count = $result->rowCount();

            if ($block_Count > 0) {
                $found=1;                
                $block_user = $result->fetch();
                $fr_id=$block_user['fr_id'];
                $fr_status=$block_user['fr_status'];
                $fr_chat_id=$block_user['chat_id'];
                //$fr_status=2;
            } else {
                //check opposite user send you request.
                $block_request2 = "SELECT * from friend_request WHERE send_user_id = '" . $_REQUEST['block_id'] . "' AND receive_user_id = '" . $_REQUEST['user_id'] . "'";
                $result2 = $pdo->prepare("$block_request2");
                $result2->execute();
                $block_Count2 = $result2->rowCount();
                if ($block_Count2 > 0) {
                    $found=1;
                    $block_user = $result2->fetch();
                    $fr_id=$block_user['fr_id'];
                    $fr_status=$block_user['fr_status'];
                    $fr_chat_id=$block_user['chat_id'];

                } 
            }
            if($_REQUEST['block_status']==2)
            {
                if($fr_status==2)
                {
                    if ($lang == 'es') {
                        $response = array('flag' => 1, 'message' => 'Already Blocked.(es)');
                    } else {
                        $response = array('flag' => 1, 'message' => 'Already Blocked.');
                    }
                }else{
                    if($found==1){
                        
                        $updateMember = sprintf("UPDATE friend_request SET fr_status = '%d' WHERE fr_id = '%d';", $block_status, $fr_id);
                        try {
                            $pdo->prepare("$updateMember")->execute();
                            if ($lang == 'es') {
                                $response = array('flag' => 1, 'message' => "Blocked.(es)");
                            } else {
                                $response = array('flag' => 1, 'message' => "Blocked.");
                            }
                        } catch (PDOException $e) {
                            $response = array('flag' => '0', 'message' => $e->getMessage());
                        }
                    }else{
                        $date = new DateTime("now", new DateTimeZone('Asia/Calcutta') );
                        $accept_date=$date->format('Y-m-d H:i:s');
                        $insertblockRequest = sprintf(
                                    "INSERT INTO friend_request (send_user_id, receive_user_id,send_date,fr_status) VALUES ('%s', '%s', '%s', '%s');",
                                    $send_id,
                                    $block_id,
                                    $accept_date,
                                    $block_status
                                );
                        try {
                            $pdo->prepare("$insertblockRequest")->execute();
                            if ($lang == 'es') {
                                $response = array('flag' => 1, 'message' => 'Blocked.(es)');
                            } else {
                                $response = array('flag' => 1, 'message' => 'Blocked.');
                            }
                        } catch (PDOException $e) {
                            $response = array('flag' => 0, 'message' => $e->getMessage());
                        }
                    }
                }
            }
            if($_REQUEST['block_status']==0)
            {
                if($found==0)
                {
                    if ($lang == 'es') {
                        $response = array('flag' => 1, 'message' => "User not found.(es)");
                    } else {
                        $response = array('flag' => 1, 'message' => "User not found.");
                    }
                }
                if($found==1)
                {
                    if($fr_status==0 OR $fr_status==1)
                    {
                        if ($lang == 'es') {
                            $response = array('flag' => 1, 'message' => "Already Unblocked.(es)");
                        } else {
                            $response = array('flag' => 1, 'message' => "Already Unblocked.");
                        }
                    }else{

                    $fr_chat_id=$block_user['chat_id'];
                    if($fr_chat_id!='')
                    {
                        $block_status=1;
                    }else{
                        $block_status=0;
                    }
                    $updateMember = sprintf("UPDATE friend_request SET fr_status = '%d' WHERE fr_id = '%d';", $block_status, $fr_id);
                        try {
                            $pdo->prepare("$updateMember")->execute();
                            if ($lang == 'es') {
                                $response = array('flag' => 1, 'message' => "Unblocked successfully.(es)");
                            } else {
                                $response = array('flag' => 1, 'message' => "Unblocked successfully.");
                            }
                        } catch (PDOException $e) {
                            $response = array('flag' => '0', 'message' => $e->getMessage());
                        }
                    }    
                }
            }
            echo json_encode($response);
        }
    }
} else {
    $response = array('flag' => 0, 'message' => "All fields are mendatory");
    echo json_encode($response);
}
