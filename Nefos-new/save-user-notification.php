<?php

require_once 'cOnfig/connection-master.php';
$ids = explode(",", $_POST['cust_id']);
$cust_numbers = explode(",", $_POST['cust_number']);

// Add This For Multipal Club And User Notification
foreach ($ids as $keys => $values) {
    $result = $pdo->prepare("SELECT domain FROM db_access WHERE customer = ?");
    $result->execute(array($cust_numbers[$keys]));
    $result_data = $result->fetch(PDO::FETCH_ASSOC);
   
    try {
        $pdo_domain = new PDO('mysql:host='.DATABASE_HOST.';dbname=ccs_'.$result_data['domain'],USERNAME,PASSWORD);
        $pdo_domain->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $output = 'Unable to connect to the database server: ' . $e->getMessage();
        echo $output;
        exit();
    }
    
    // Club Notification
    try{
        $query = "
        INSERT INTO notification_contact_update 
        (cust_id,user_id,cust_number,notification_type,is_read,reject_reason,admin_notification_type,admin_notification_url,domain,created_at) 
        VALUES (:cust_id,:user_id,:cust_numbers,:notification_type,:is_read,:reject_reason,:admin_notification_type,:admin_notification_url,:domain,:created_at)";
        $member = $pdo->prepare($query);
        $member->execute(
            array(
                ':cust_id'                 => $values,
                ':user_id'                 => '0',
                ':cust_numbers'            => $cust_numbers[$keys],
                ':notification_type'       => $_POST['notification_type'],
                ':is_read'                 => '0',
                ':reject_reason'           => $_POST['reject_reason'],
                ':admin_notification_type' => $_POST['admin_notification_type'],
                ':admin_notification_url'  => $_POST['admin_notification_link'],
                ':domain'                  => $result_data['domain'],
                ':created_at'              => $_POST['rejected_date'],
            )
        );
    }
    catch (PDOException $e)
    {
            $error = 'Error Addin Notification: ' . $e->getMessage();
            echo $error;
            exit();
    }

    // User Selection Base On Its Group And If Group Not Avaliabel Then All The Mumbers Get Notification
    try {
        if(!empty($_POST['group'])){
            $sql = "SELECT * FROM `users`  WHERE (`users`.`userGroup` IN ( " . $_POST['group'] . " ) OR `users`.`userGroup2` IN ( " . $_POST['group'] . " ))";
        } else {
            $sql = "SELECT * FROM `users`";
        }
        $result = $pdo_domain->query($sql);
        $result->execute();
        $result_data_user = $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching user: ' . $e->getMessage();
        echo $error;
        exit();
    }
    // Sent user Notification
    foreach ($result_data_user as $key => $value) {
        // try {
        //     $notification_query = 'INSERT INTO notification_contact_update (`cust_id`,`user_id`,`cust_number`,`reject_reason`,`notification_type`,`created_at`,`is_read`,`domain`,`admin_notification_type`,`admin_notification_url`) VALUES (0,' . $value['user_id'] . ',' .  $cust_numbers[$keys] . ',"' . $_POST['reject_reason'] . '",4,"' . $_POST['rejected_date'] . '",0,"","' . $_POST['admin_notification_type'] . '","' . $_POST['admin_notification_link'] . '")';
        //     $result = $pdo->prepare($notification_query);
        //     $result->execute();
        // } catch (PDOException $e) {
        //     $error = 'Error Inserting Data: ' . $e->getMessage();
        //     echo $error;
        //     exit();
        // }
        try{
            $query = "
            INSERT INTO notification_contact_update 
            (cust_id,user_id,cust_number,notification_type,is_read,reject_reason,admin_notification_type,admin_notification_url,created_at) 
            VALUES (:cust_id,:user_id,:cust_numbers,:notification_type,:is_read,:reject_reason,:admin_notification_type,:admin_notification_url,:created_at)";
            $member = $pdo->prepare($query);
            $member->execute(
                array(
                    ':cust_id'                 => 0,
                    ':user_id'                 => $value['user_id'],
                    ':cust_numbers'            => $cust_numbers[$keys],
                    ':notification_type'       => $_POST['notification_type'],
                    ':is_read'                 => '0',
                    ':reject_reason'           => $_POST['reject_reason'],
                    ':admin_notification_type' => $_POST['admin_notification_type'],
                    ':admin_notification_url'  => $_POST['admin_notification_link'],
                    ':created_at'              => $_POST['rejected_date'],
                )
            );
        }
        catch (PDOException $e)
        {
            $error = 'Error Addin Notification: ' . $e->getMessage();
            echo $error;
            exit();
        }
    }
}
