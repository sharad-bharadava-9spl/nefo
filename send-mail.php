<?php

require "vendor/autoload.php";



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$developmentMode = true;
$mailer = new PHPMailer($developmentMode);

try {
    $mailer->SMTPDebug = 0;
    $mailer->isSMTP();

    if ($developmentMode) {
    $mailer->SMTPOptions = [
        'ssl'=> [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
        ]
    ];
    }


    $mailer->Host = 'ssl://smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = 'kipldeveloper@gmail.com';
    $mailer->Password = 'OneIndia';
    $mailer->SMTPSecure = 'ssl';
    $mailer->Port = 465;

    $mailer->setFrom('kipldeveloper@gmail.com', 'Name of sender');
    $mailer->addAddress('prateek.r@konstantinfosolutions.com', 'Name of recipient');

    $mailer->isHTML(true);
    $mailer->Subject = 'PHPMailer Test';
    $mailer->Body = 'This is a <b>SAMPLE<b> email sent through <b>PHPMailer<b>';

    $mailer->send();
    $mailer->ClearAllRecipients();
    echo "MAIL HAS BEEN SENT SUCCESSFULLY";

} catch (Exception $e) {
    echo "EMAIL SENDING FAILED. INFO: " . $mailer->ErrorInfo;
}