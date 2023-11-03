<?php

require_once 'cOnfig/connection.php';
require_once 'cOnfig/viewv6.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

//session_start();
$accessLevel = '3';

// Authenticate & authorize
authorizeUser($accessLevel);

require_once '../PHPMailerAutoload.php';

if(strpos($siteroot, "ccsnube.com/ttt") !== false){
    $base_url = "http://ccsnube.com/ttt/";
}else{
    $base_url = "http://192.168.0.41/ccs/";
}



// submit the change request

if(isset($_POST['add_contract'])){

    $email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email'])));
    $subject = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['subject'])));
    $description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
    $insertTime = date("Y-m-d H:i:s");

    try
    {
        //-----------------------------------------------
        $insertCustomContract = $pdo3->prepare("INSERT INTO custom_contracts (email, subject, contract, created_at) VALUES (?,?,?,?)");
        $insertCustomContract->bindValue(1, $email);
        $insertCustomContract->bindValue(2, $subject);
        $insertCustomContract->bindValue(3, $description);
        $insertCustomContract->bindValue(4, $insertTime);
        $insertCustomContract->execute();
        $last_id = $pdo3->lastInsertId();

        $authToken = md5($last_id.",".$email);

        $insertAuthToken = $pdo3->prepare("INSERT INTO custom_contract_signatures (contract_id, authtoken) VALUES (?,?)");
        $insertAuthToken->bindValue(1, $last_id);
        $insertAuthToken->bindValue(2, $authToken);
        $insertAuthToken->execute();
        //-----------------------------------------------

        // $insert_result = $pdo2->prepare("$insertTempCustomer")->execute();

        // send contract email

        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->isSMTP();
        $mail->Host = "mail.cannabisclub.systems";
        $mail->SMTPAuth = true;
        $mail->Username = "info@cannabisclub.systems";
        $mail->Password = "Insjormafon9191";
        $mail->SMTPSecure = 'ssl'; 
        $mail->Port = 465;
        $mail->setFrom('info@cannabisclub.systems', 'CCSNube');
        $mail->addAddress("$email");
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $link = $base_url."contract-ct.php?auth=".$authToken;
        $mail->Body = "<p>Please click on this <a href='".$link."'>contract link</a> to verify contract !</p>";
        $mail->send();

        $_SESSION['successMessage'] = "Contract sent successfully!";
        header("Location: custom-contracts.php");
        exit();
    }
    catch (PDOException $e)
    {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
    }

}	