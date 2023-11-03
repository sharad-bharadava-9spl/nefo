<?php
// Include the bundled autoload from the Twilio PHP Helper Library
require __DIR__ . '/twilio-php-main/src/Twilio/autoload.php';
use Twilio\Rest\Client;
// Your Account SID and Auth Token from twilio.com/console

$account_sid = 'AC3d8e850e22cf6f0dbb452c054cfc9979';
$auth_token = '728f0322b7385e77553cb2a38b26a639';

// In production, these should be environment variables. E.g.:
// $auth_token = $_ENV["TWILIO_ACCOUNT_SID"]
// A Twilio number you own with SMS capabilities

//$twilio_number = "+447782334797";
$twilio = new Client($account_sid, $auth_token);
$message = $twilio->messages
                  ->create("whatsapp:+353874255064", // to
                           [
                               "from" => "whatsapp:+14155238886",
                               "body" => "Hello there!"
                           ]
                  );

print($message->sid);



/*
$client->messages->create(
    // Where to send a text message (your cell phone?)
    '+34644441092',
    array(
        'from' => 'CCS',
        'body' => 
"(English version below)
Recordatorio: tienes una factura impagada de CCS. Si no hemos recibido el pago antes del 01/09/2020, perderas el acceso a tu software. Para darnos una nueva fecha de pago, siga este enlace: www.ccsnube.com/p.php?h=asdf
*
Reminder: You have an unpaid invoice from CCS. If we haven't received payment by 01/09/2020 you will lose access to your software! To give us a new payment date, follow this link: www.ccsnube.com/p.php?h=asdf"
    )
);
*/