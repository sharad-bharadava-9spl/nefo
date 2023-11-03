<?php
include('connection_chat.php');

$lang = '';
if (isset($_POST['language'])) {
    $lang = $_POST['language'];
}


if ($_POST['request'] == 'friendRequest') {
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
                    if ($friend_request['fr_status'] == 2) {
                        if ($lang == 'es') {
                            $response = array('flag' => 0, 'message' => "This user is block.(es)");
                        } else {
                            $response = array('flag' => 0, 'message' => "This user is block.");
                        }
                    } else {
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
                        if ($friend_request2['fr_status'] == 2) {
                            if ($lang == 'es') {
                                $response = array('flag' => 0, 'message' => "This user block You.(es)");
                            } else {
                                $response = array('flag' => 0, 'message' => "This user block You.");
                            }
                        } else {
                            if ($lang == 'es') {
                                $response = array('flag' => 0, 'message' => "This user already send you request.(es)");
                            } else {
                                $response = array('flag' => 0, 'message' => "This user already send you request.");
                            }
                        }
                        echo json_encode($response);
                    } else {
                        //send request
                        $date = new DateTime("now", new DateTimeZone('Asia/Calcutta'));
                        $accept_date = $date->format('Y-m-d H:i:s');
                        //$accept_date = date('Y-m-d H:i:s');
                        $insertAppRequest = sprintf(
                            "INSERT INTO friend_request (send_user_id, receive_user_id,send_date) VALUES ('%s', '%s', '%s');",
                            $send_request,
                            $receive_request,
                            $accept_date,
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
}

if ($_POST['request'] == 'acceptOrRejectRequest') {

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

                    if ($friendrequest_status == 2) {
                        if ($lang == 'es') {
                            $response = array('flag' => 0, 'message' => "Block User.(es)");
                        } else {
                            $response = array('flag' => 0, 'message' => "Block User.");
                        }
                    } else {
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
}

if ($_POST['request'] == 'searchFriend') {
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
}

if ($_POST['request'] == 'listOfFriend') {
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
            $selectFriends = "SELECT * FROM `friend_request` WHERE (`send_user_id`=" . $_REQUEST['user_id'] . " OR `receive_user_id`=" . $_REQUEST['user_id'] . ") AND `fr_status`=1";
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

                    if ($new_arr['send_user_id'] == $_REQUEST['user_id']) {
                        $uid = $friend['receive_user_id'];
                    } else {
                        $uid = $friend['send_user_id'];
                    }
                    $new_arr['uid']  = $uid;

                    //Friend detail fetch
                    $selectUser = "SELECT * FROM `members` WHERE `id`=" . $uid;
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
}


if ($_POST['request'] == 'blockuser') {
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
                $found = 0;
                $block_status = $_REQUEST['block_status'];
                $fr_status = null;
                //check friend Request already send or not.
                $block_request = "SELECT * from friend_request WHERE send_user_id = '" . $_REQUEST['user_id'] . "' AND receive_user_id = '" . $_REQUEST['block_id'] . "'";
                $result = $pdo->prepare("$block_request");
                $result->execute();
                $block_Count = $result->rowCount();

                if ($block_Count > 0) {
                    $found = 1;
                    $block_user = $result->fetch();
                    $fr_id = $block_user['fr_id'];
                    $fr_status = $block_user['fr_status'];
                    $fr_chat_id = $block_user['chat_id'];
                    //$fr_status=2;
                } else {
                    //check opposite user send you request.
                    $block_request2 = "SELECT * from friend_request WHERE send_user_id = '" . $_REQUEST['block_id'] . "' AND receive_user_id = '" . $_REQUEST['user_id'] . "'";
                    $result2 = $pdo->prepare("$block_request2");
                    $result2->execute();
                    $block_Count2 = $result2->rowCount();
                    if ($block_Count2 > 0) {
                        $found = 1;
                        $block_user = $result2->fetch();
                        $fr_id = $block_user['fr_id'];
                        $fr_status = $block_user['fr_status'];
                        $fr_chat_id = $block_user['chat_id'];
                    }
                }
                if ($_REQUEST['block_status'] == 2) {
                    if ($fr_status == 2) {
                        if ($lang == 'es') {
                            $response = array('flag' => 1, 'message' => 'Already Blocked.(es)');
                        } else {
                            $response = array('flag' => 1, 'message' => 'Already Blocked.');
                        }
                    } else {
                        if ($found == 1) {

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
                        } else {
                            $date = new DateTime("now", new DateTimeZone('Asia/Calcutta'));
                            $accept_date = $date->format('Y-m-d H:i:s');
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
                if ($_REQUEST['block_status'] == 0) {
                    if ($found == 0) {
                        if ($lang == 'es') {
                            $response = array('flag' => 1, 'message' => "User not found.(es)");
                        } else {
                            $response = array('flag' => 1, 'message' => "User not found.");
                        }
                    }
                    if ($found == 1) {
                        if ($fr_status == 0 or $fr_status == 1) {
                            if ($lang == 'es') {
                                $response = array('flag' => 1, 'message' => "Already Unblocked.(es)");
                            } else {
                                $response = array('flag' => 1, 'message' => "Already Unblocked.");
                            }
                        } else {

                            $fr_chat_id = $block_user['chat_id'];
                            if ($fr_chat_id != '') {
                                $block_status = 1;
                            } else {
                                $block_status = 0;
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
}
