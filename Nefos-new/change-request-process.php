<?php

require_once 'cOnfig/connection.php';
require_once 'cOnfig/viewv6.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

//session_start();
$accessLevel = '3';

// Authenticate & authorize
authorizeUser($accessLevel);


$user_id = $_SESSION['user_id'];
$insertTime = date("Y-m-d H:i:s");
// submit the change request

if(isset($_POST['add_request'])){

    $topic = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['topic'])));
    $description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
    $priority = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['priority'])));

    try
    {
        //-----------------------------------------------
        $insertChangeRequest = $pdo3->prepare("INSERT INTO change_requests (topic, description, priority, user_id, created_at) VALUES (?,?,?,?,?)");
        $insertChangeRequest->bindValue(1, $topic);
        $insertChangeRequest->bindValue(2, $description);
        $insertChangeRequest->bindValue(3, $priority);
        $insertChangeRequest->bindValue(4, $user_id);
        $insertChangeRequest->bindValue(5, $insertTime);
        $insertChangeRequest->execute();
        //-----------------------------------------------

        // $insert_result = $pdo2->prepare("$insertTempCustomer")->execute();

        $_SESSION['successMessage'] = "Request added successfully!";
        header("Location: change-requests.php");
        exit();
    }
    catch (PDOException $e)
    {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
    }

}	