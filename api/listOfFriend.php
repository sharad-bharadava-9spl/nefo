<?php
// created by priyank for list of friend task on 27-12-2022
include('connection_chat.php');

$lang = '';
if (isset($_POST['language'])) {
    $lang = $_POST['language'];
}

//check user id enter or not
if (isset($_REQUEST['user_id'])) {

    if ($_REQUEST['user_id'] == '') {
        if ($lang == 'es') {
            $response = array('flag' => 0, 'message' => "Fields can not be null.(es)");
        } else {
            $response = array('flag' => 0, 'message' => "Fields can not be null.");
        }
        echo json_encode($response);
    } else {
        //fetch fired
        //$selectUsers = "SELECT * from members WHERE `username` like '%" . $_REQUEST['username'] . "%'";
        $selectFriends = "SELECT * FROM `friend_request` WHERE (`send_user_id`=". $_REQUEST['user_id'] ." OR `receive_user_id`=". $_REQUEST['user_id'] .") AND `fr_status`=1";
        $friend_result = $pdo->prepare("$selectFriends");
        $friend_result->execute();
        $friendCount = $friend_result->rowCount();
        if ($friendCount <= 0) {
            if ($lang == 'es') {
                $response = array('flag' => 0, 'message' => "No Friend Found.(es)");
            } else {
                $response = array('flag' => 0, 'message' => "No Friend Found.");
            }
            //echo json_encode($response);
        } else {
            if ($lang == 'es') {
                $response = array('flag' => 1, 'message' => "Friend found.(es)", 'user_count' => $friendCount);
            } else {
                $response = array('flag' => 1, 'message' => 'Friend found', 'user_count' => $friendCount);
            }
            $new_arr = array();
            while ($friend = $friend_result->fetch()) {
                $new_arr['fr_id']  = $friend['fr_id'];
                $new_arr['send_user_id']  = $friend['send_user_id'];
                $new_arr['receive_user_id']  = $friend['receive_user_id'];
                $new_arr['chat_id']  = $friend['chat_id'];
                $new_arr['send_date']  = $friend['send_date'];
                $new_arr['accept_date']  = $friend['accept_date'];
                $new_arr['fr_status']  = $friend['fr_status'];
                
                if($new_arr['send_user_id']==$_REQUEST['user_id'])
                {
                    $uid=$friend['receive_user_id'];
                }else{
                    $uid=$friend['send_user_id'];
                }
                $new_arr['uid']  = $uid;

                //Friend detail fetch
                $selectUser = "SELECT * FROM `members` WHERE `id`=".$uid;
                //$user_result = $pdo->prepare("$selectUser");
                try {
                    $user_result = $pdo->prepare("$selectUser");
                    $user_result->execute();
                    $userCount = $user_result->rowCount();
                    $user = $user_result->fetch();
                } catch (PDOException $e) {
                    $response = array('flag' => 0, 'message' => $e->getMessage());
                }
                
                $new_arr['username']  = $user['username'];
                $new_arr['email']  = $user['email'];
                


                $response['data'][] = $new_arr;
            }
        }
        echo json_encode($response);
    }
} else {
    $response = array('flag' => 0, 'message' => "All fields are mendatory");
    echo json_encode($response);
}
