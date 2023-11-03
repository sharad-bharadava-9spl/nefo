<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Status: 0 = default. 1 = sent. 2 = failed. 3 = delivered.
	
	// Query all from SMS
	$query = "SELECT * FROM sms WHERE period = '202010'";
	try
	{
		$results = $pdo3->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	require __DIR__ . '/twilio-php-main/src/Twilio/autoload.php';
	use Twilio\Rest\Client;
	// Your Account SID and Auth Token from twilio.com/console
	
	$account_sid = 'AC3d8e850e22cf6f0dbb452c054cfc9979';
	$auth_token = '728f0322b7385e77553cb2a38b26a639';
	
	// In production, these should be environment variables. E.g.:
	// $auth_token = $_ENV["TWILIO_ACCOUNT_SID"]
	// A Twilio number you own with SMS capabilities
	
	$twilio_number = "+447782334797";
	$client = new Client($account_sid, $auth_token);

	while ($row = $results->fetch()) {
		
		$id = $row['id'];
		$hash = $row['hash'];
		$number = $row['number'];
		$status = $row['status'];
		
		if ($status == 0) {
			
$client->messages->create(
    "$number",
    array(
        "from" => "CCS",
        "body" => 
"(English version below)
Recordatorio: tienes una factura impaga de CCS. Si no recibimos el pago hoy, perderas el acceso a tu software! Para darnos una nueva fecha de pago, siga este enlace: www.ccsnube.com/p.php?h=$hash
*
Reminder: You have an unpaid invoice from CCS. If we do not receive payment today, you will lose access to your software! To give us a new payment date, follow this link: www.ccsnube.com/p.php?h=$hash"
    )
);
			
			echo "sent: $hash - $number<br />";
			
			$query = "UPDATE sms SET status = 1 WHERE id = '$id'";
			try
			{
				$result = $pdo3->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}			
			echo "db updated<br /><br />";
			
		}
			
	}