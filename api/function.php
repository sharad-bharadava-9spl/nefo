<?php 
    include('connectionM.php'); 
        

	function donationhistory($topupcredit,$preorder,$showprice){
		/*Check system setting find domain*/
        $domainName = $_REQUEST['club_name'];
        $checkSystemsetting = "SELECT * FROM systemsettings WHERE domain = '$domainName'";
        $result = $pdo->prepare("$checkSystemsetting");
        $result->execute();
        $clubSystem = $result->fetch();
        $domain = $clubSystem['domain'];

        /*check system wise multiple data check*/
        $checkDomainMulitpleData = "SELECT * FROM moblie_macaddress WHERE domain_name = '$domain'";
        $result = $pdo->prepare("$checkDomainMulitpleData");
        $result->execute();
            $macarr = array();
            if($result->rowCount() > 0){
                while($macaddress = $result->fetch()){
                    $macarr[] = $macaddress['mac_address'];
                }
            }

            if(in_array($macAddress,$macarr)){
                $topupcredit = 1;
                $preorder    = 1;
                $showprice   = 1;
            }else{
                $topupcredit = $clubSystem['topcredit_option'];
                $preorder    = $clubSystem['preorder_option'];
                $showprice   = $clubSystem['showprice_option'];
            }

            return $topupcredit;
	}