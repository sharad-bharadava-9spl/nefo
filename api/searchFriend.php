<?php
// created by priyank for serach username task on 27-12-2022
include('connection_chat.php');

$lang = '';
if (isset($_POST['language'])) {
    $lang = $_POST['language'];
}

//check username enter or not
if (isset($_REQUEST['username'])) {

    if ($_REQUEST['username'] == '') {
        if ($lang == 'es') {
            $response = array('flag' => 0, 'message' => "Fields can not be null.(es)");
        } else {
            $response = array('flag' => 0, 'message' => "Fields can not be null.");
        }
        echo json_encode($response);
    } else {

        //fetch user serach username
        $selectUsers = "SELECT * from members WHERE `username` like '%" . $_REQUEST['username'] . "%'";
        $user_result = $pdo->prepare("$selectUsers");
        $user_result->execute();
        $userCount = $user_result->rowCount();

        if ($userCount <= 0) {
            if ($lang == 'es') {
                $response = array('flag' => 0, 'message' => "User not Found.(es)");
            } else {
                $response = array('flag' => 0, 'message' => "User not Found.");
            }
            //echo json_encode($response);
        } else {
            if ($lang == 'es') {
                $response = array('flag' => 1, 'message' => "User found.(es)", 'user_count' => $userCount);
            } else {
                $response = array('flag' => 1, 'message' => 'User found', 'user_count' => $userCount);
            }
            $new_arr = array();
            $username = $_REQUEST['username'];
            while ($user = $user_result->fetch()) {
                $new_arr['id']  = $user['id'];
                $new_arr['username']  = $user['username'];
                $response['data'][] = $new_arr;
            }
        }
        echo json_encode($response);
    }
} else {
    $response = array('flag' => 0, 'message' => "All fields are mendatory");
    echo json_encode($response);
}
