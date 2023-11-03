<?php


	function debugPrint($message) {
		if (DEBUG_MODE) {
			echo $message;
		}
	}

	function handleError($userError, $systemError) {
		
		global $siteroot;
		
		$_SESSION['userError'] = $userError;
		$_SESSION['systemError'] = $systemError;
		header("Location: {$siteroot}error.php");
		exit();
		
	}
	
	function getWebPath($fileSystemPath) {
		return str_replace($_SERVER['DOCUMENT_ROOT'], '', $fileSystemPath);	
	}

	$expr = "htmlentities";	


function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function generateRandomStringCapitals($length = 10) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function getOperator($user_id) {

	global $pdo3;
	
	try
	{
		$result = $pdo3->prepare("SELECT memberno, first_name, last_name FROM users WHERE user_id = :user_id");
		$result->bindValue(':user_id', $user_id);
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
				
	$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		
	$operator = "#" . $memberno . " - " . $first_name . " " . $last_name;
	return $operator;
			
}


function getUser($user_id) {

	global $pdo3;
	
	try
	{
		$result = $pdo3->prepare("SELECT memberno, first_name FROM users WHERE user_id = :user_id");
		$result->bindValue(':user_id', $user_id);
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
				
	$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		
	$operator = "#" . $memberno . " - " . $first_name;
	return $operator;
			
}

function getSettings() {

	global $pdo3;
	
	try
	{
		$result = $pdo3->prepare("SELECT highRollerWeekly, minAge, closingMail, dispensaryGift, barGift, menuType, medicalDiscount, logouttime, logoutredir, dispDonate, dispExpired, dispenseLimit, showAge, showGender, keepNumber, membershipFees, medicalDiscountPercentage, bankPayments, creditOrDirect, visitRegistration, cropOrNot, puestosOrNot, openAndClose, barMenuType, flowerLimit, extractLimit, realWeight, autologout FROM systemsettings");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
  	    $_SESSION['highRollerWeekly'] = $row['highRollerWeekly'];
  	    $_SESSION['minAge'] = $row['minAge'];
  	    $_SESSION['closingMail'] = $row['closingMail'];
  	    $_SESSION['dispensaryGift'] = $row['dispensaryGift'];
  	    $_SESSION['barGift'] = $row['barGift'];
  	    $_SESSION['menuType'] = $row['menuType'];
  	    $_SESSION['medicalDiscount'] = $row['medicalDiscount'];
		$_SESSION['logouttime'] = $row['logouttime'];
		$_SESSION['logoutredir'] = $row['logoutredir'];
		$_SESSION['dispDonate'] = $row['dispDonate'];
		$_SESSION['dispExpired'] = $row['dispExpired'];
		$_SESSION['dispenseLimit'] = $row['dispenseLimit'];
		$_SESSION['showAge'] = $row['showAge'];
		$_SESSION['showGender'] = $row['showGender'];
		$_SESSION['keepNumber'] = $row['keepNumber'];
		$_SESSION['membershipFees'] = $row['membershipFees'];
		$_SESSION['medicalDiscountPercentage'] = $row['medicalDiscountPercentage'];
		$_SESSION['bankPayments'] = $row['bankPayments'];
		$_SESSION['creditOrDirect'] = $row['creditOrDirect'];
		$_SESSION['visitRegistration'] = $row['visitRegistration'];
		$_SESSION['cropOrNot'] = $row['cropOrNot'];
		$_SESSION['puestosOrNot'] = $row['puestosOrNot'];
		$_SESSION['openAndClose'] = $row['openAndClose'];
		$_SESSION['barMenuType'] = $row['barMenuType'];
		$_SESSION['flowerLimit'] = $row['flowerLimit'];
		$_SESSION['extractLimit'] = $row['extractLimit'];
		$_SESSION['realWeight'] = $row['realWeight'];
		$_SESSION['showStock'] = $row['showStock'];
		$_SESSION['showOrigPrice'] = $row['showOrigPrice'];
		$_SESSION['checkoutDiscount'] = $row['checkoutDiscount'];
		$_SESSION['consumptionMin'] = $row['consumptionMin'];
		$_SESSION['consumptionMax'] = $row['consumptionMax'];
		$_SESSION['showStockBar'] = $row['showStockBar'];
		$_SESSION['showOrigPriceBar'] = $row['showOrigPriceBar'];
		$_SESSION['barTouchscreen'] = $row['barTouchscreen'];
		$_SESSION['trialMode'] = $row['trialMode'];
		$_SESSION['contract'] = $row['contract'];
		$_SESSION['iPadReaders'] = $row['iPadReaders'];
		$_SESSION['cashdro'] = $row['cashdro'];
		$_SESSION['creditchange'] = $row['creditchange'];
		$_SESSION['expirychange'] = $row['expirychange'];
		$_SESSION['exentoset'] = $row['exentoset'];
		$_SESSION['menusortdisp'] = $row['menusortdisp'];
		$_SESSION['menusortbar'] = $row['menusortbar'];
		$_SESSION['dispsig'] = $row['dispsig'];
		$_SESSION['barsig'] = $row['barsig'];
		$_SESSION['openmenu'] = $row['openmenu'];
		$_SESSION['keypads'] = $row['keypads'];
		$_SESSION['moneycount'] = $row['moneycount'];
		$_SESSION['customws'] = $row['customws'];
		$_SESSION['negcredit'] = $row['negcredit'];
		$_SESSION['language'] = $row['language'];
		$_SESSION['nobar'] = $row['nobar'];
		$_SESSION['sigtablet'] = $row['sigtablet'];
		$_SESSION['entrysys'] = $row['entrysys'];
		$_SESSION['entrysysstay'] = $row['entrysysstay'];
		$_SESSION['entrysyssecs'] = $row['entrysyssecs'];
		$_SESSION['dooropener'] = $row['dooropener'];
		$_SESSION['checkoutDiscountBar'] = $row['checkoutDiscountBar'];
		$_SESSION['cuotaincrement'] = $row['cuotaincrement'];
		$_SESSION['chipcost'] = $row['chipcost'];
		$_SESSION['fingerprint'] = $row['fingerprint'];
		$_SESSION['consumptionPercentage'] = $row['consumptionPercentage'];
		$_SESSION['pagination'] = $row['pagination'];
		$_SESSION['normalNumbers'] = $row['normalNumbers'];

}


function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}