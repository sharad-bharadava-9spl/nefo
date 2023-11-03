<?php 
    include('connectionM.php');
    try {
        //echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
        //$pdo_master = new PDO('mysql:host=localhost;dbname=ccs_masterdb', 'root', '');
        $pdo_master = new PDO('mysql:host=127.0.0.1:3306;dbname=ccs_masterdb', 'ccs_masterdbu', 'GMjq8iG8mEkPMJRf');
        $pdo_master->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo_master->exec('SET NAMES "utf8"');
    }
    catch (PDOException $e) {
        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response); 
        die();
        
    }

    try{
        if(!empty($_POST['language'])){
            $lang = $_POST['language'];
        }else{
            $lang = ""; 
        }

        if(!empty($_POST['user_id'])){
            $user_id = $_POST['user_id'];
        }else{
            $user_id = ""; 
        }

        if(!empty($lang == 'es') || !empty($lang == 'en') && !empty($user_id)){

            /*user detail*/
            $userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.friend2, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(), u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.dniext1, u.dniext2, u.workStation, u.bajaDate, u.starCat, u.interview, u.exento, u.fptemplate1, u.usergroup2, u.qrcode, u.app_member FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
            $resultuserDetails = $pdo->prepare("$userDetails");
            $resultuserDetails->execute();

            if($resultuserDetails->rowCount() > 0){

                $userdata = $resultuserDetails->fetch();


                $response['data'] = array();
                $userarr = array();

                /* get notification count */
                $notificntdata= "SELECT DISTINCT unique_num ,title,description,image,notification_status FROM pushnotification WHERE user_id = '".$user_id."' AND  notification_status = 'unread'";
                $resultcntdata = $pdo->prepare("$notificntdata");
                $resultcntdata->execute();
                $countnotfication = $resultcntdata->rowCount();
   
                if($lang=='es')
                {	
                    $response = array('flag' => '1','message' => '¡Los datos del socio encontrados con éxito!','notification_count' => $countnotfication);
                }else{
                    $response = array('flag' => '1','message' => 'Member details found successfully!','notification_count' => $countnotfication);
                }
                //$response = array('flag' => '1','message' => 'User detail found successfully','notification_count' => $countnotfication);

                /*set user colour */
                if(isset($userdata['starCat']) == 1){
                    $colour = 'Yellow';
                }elseif(isset($userdata['starCat']) == 2){
                    $colour = 'Black';
                }elseif(isset($userdata['starCat']) == 3){
                    $colour = 'Green';
                }elseif(isset($userdata['starCat']) == 4){
                    $colour = 'Red';
                }elseif(isset($userdata['starCat']) == 5){
                    $colour = 'Purple';
                }elseif(isset($userdata['starCat']) == 6){
                    $colour = 'Blue';
                }else{
                    $colour = '';
                }

                /*set user usertype */
                if(isset($userdata['userGroup']) == 1){
                    $usertype = 'Administrator';
                }elseif(isset($userdata['userGroup']) == 2){
                    $usertype = 'Worker';
                }elseif(isset($userdata['userGroup']) == 3){
                    $usertype = 'Volunteer';
                }elseif(isset($userdata['userGroup']) == 4){
                    $usertype = 'Professional contact';
                }elseif(isset($userdata['userGroup']) == 5){
                    $usertype = 'Member';
                }elseif(isset($userdata['userGroup']) == 6){
                    $usertype = 'Visitor';
                }elseif(isset($userdata['userGroup']) == 7){
                    $usertype = 'Banned';
                }elseif(isset($userdata['userGroup']) == 8){
                    $usertype = 'Deleted';
                }elseif(isset($userdata['userGroup']) == 9){
                    $usertype = 'Inactive';
                }else{
                    $usertype = '';
                }
                
                $userGroup = $userdata['userGroup'];
                $exento = $userdata['exento'];
                $groupName=$userdata['groupName'];
                // echo $groupName;

                // $selectMemberuserGroup = "SELECT * FROM usergroups WHERE userGroup =".$userGroup;
                // $member_resultuserGroup = $pdo_master->prepare("$selectMemberuserGroup");
                // $member_resultuserGroup->execute();
                // $member_rowuserGroup = $member_resultuserGroup->fetch();
                //$groupName=$member_rowuserGroup[$lang];
                $paidUntil = $userdata['paidUntil'];
                $memberEReadabled = date('d M Y', strtotime($paidUntil));
                
                $mark_icon=0;
                
                if ($userGroup == 5 && $exento == '1') {
                    if($lang == 'es')
                    {
                        $groupName = "Exento";
                    }else{
                        $groupName = "Exempt";
                    }
                } else if ($userGroup == 5) {
                    if($lang == 'es')
                    {
                        $groupName = "Socio hasta $memberEReadabled";
                    }else{
                        $groupName = "Member until $memberEReadabled";
                    }
                    $current_date=date('d M Y');
                    if (strtotime($memberEReadabled) <= strtotime($current_date))
                    {
                        $mark_icon=1;
                        if($lang == 'es')
                        {
                            $groupName="Membership expiro $memberEReadabled";
                        }else{
                            $groupName="Membership expired $memberEReadabled";
                        }
                    }else{
                        $mark_icon=0;
                    } 
                } else {
                    $groupName = "$groupName";
                }
                

                
                //print_r($member_rowuserGroup);
                //$member_nicknameuserGroup = $member_rowuserGroup['username'];
                
                


                /*set user member_interviewed */
                if(isset($userdata['interview']) == 0){
                    $interview = 'No';
                }elseif(isset($userdata['interview']) == 1){
                    $interview = 'Yes';
                }else{
                    $interview = '';
                }
               
               if($userdata['userGroup'] != 1 && $userdata['userGroup'] != 2 && $userdata['userGroup'] != 3){
                    /*set user Exempt from paying membership fee? */
                    if(isset($userdata['exento']) == 0){
                        $exempt_membership_fee = 'No';
                    }elseif(isset($userdata['exento']) == 1){
                        $exempt_membership_fee = 'Yes';
                    }else{
                        $exempt_membership_fee = '';
                    }
                }else{
                    $exempt_membership_fee = "";
                }

                /*set user passport */
                if(isset($userdata['passport'])){
                    $passport = $userdata['passport'];
                }else{
                    $passport = '';
                }
                /*set user telephone */
                if(isset($userdata['telephone'])){
                    $telephone =$userdata['telephone'];
                }else{
                    $telephone = '';
                }

                /*set user email */
                if(isset($userdata['email'])){
                    $email = $userdata['email'];
                }else{
                    $email = '';
                }

                /*set user email */
                if(isset($userdata['street'])){
                    $street = $userdata['street'];
                }else{
                    $street = '';
                }

                /*set user email */
                if(isset($userdata['streetnumber']) || isset($userdata['flat'])){
                    $streetnumber = $userdata['streetnumber'] .' '. $userdata['flat'] ;
                }else{
                    $streetnumber = '';
                }

                /*set user postcode */
                if(isset($userdata['postcode'])){
                    $postcode = $userdata['postcode'];
                }else{
                    $postcode = '';
                }

                /*set user city */
                if(isset($userdata['city'])){
                    $city = $userdata['city'];
                }else{
                    $city = '';
                } 

                /*set user country */
                if(isset($userdata['country'])){
                    $country = $userdata['country'];
                }else{
                    $country = '';
                }

                /*set user cardid */
                if(isset($userdata['cardid'])){
                    $cardid = $userdata['cardid'];
                }else{
                    $cardid = '';
                }

                /*set user cardid2 */
                if(isset($userdata['cardid2'])){
                    $cardid2 = $userdata['cardid2'];
                }else{
                    $cardid2 = '';
                }

                /*set user cardid2 */
                if(isset($userdata['cardid3'])){
                    $cardid3 = $userdata['cardid3'];
                }else{
                    $cardid3 = '';
                }
                /*set user cardid2 */
                if(isset($userdata['dni'])){
                    $dni = $userdata['dni'];
                }else{
                    $dni = '';
                }
                /*set user adminComment */
                if(isset($userdata['adminComment'])){
                    $adminComment = $userdata['adminComment'];
                }else{
                    $adminComment = '';
                }

                /*set user adminComment */
                if(isset($userdata['mconsumption'])){
                    $mconsumption = $userdata['mconsumption'] .' '.'g.';
                }else{
                    $mconsumption = '';
                }

                /*set user usageType */
                if(isset($userdata['usageType']) == 1){
                    $usageType = 'Medicinal';
                }else{
                    $usageType = 'Recreational';
                } 

                /*set user credit */
                $userCredit = $userdata['credit'];
                if ($userdata['credit']) {
                    $credit = $userdata['credit'];
                } else {
                    $credit = $userdata['credit'];
                }

                /*set user banComment */
                if(isset($userdata['banComment'])){
                    $banComment = $userdata['banComment'];
                }else{
                    $banComment = '';
                }

                /*set user banComment */
                if(isset($userdata['banTime'])){
                    $banTime = date('d-m-Y H:i:s',strtotime($userdata['banTime']));
                }else{
                    $banTime = '';
                }

                /*set user deleteTime */
                if(isset($userdata['deleteTime'])){
                    $deleteTime = date('d-m-Y H:i:s',strtotime($userdata['deleteTime']));
                }else{
                    $deleteTime = '';
                }

                /*set user discount */
                if(isset($userdata['discount'])){
                    $discount = $userdata['discount'];
                }else{
                    $discount = '';
                } 

                /*set user discountBar */
                if(isset($userdata['discountBar'])){
                    $discountBar = $userdata['discountBar'];
                }else{
                    $discountBar = '';
                }

                 /*set user signupsource */
                if(isset($userdata['signupsource'])){
                    $signupsource = $userdata['signupsource'];
                }else{
                    $signupsource = '';
                }
                $QR_server = "https://ccsnube.com/ttt/"; // change the server if neccassary
                if(isset($userdata['qrcode']) && $userdata['qrcode'] != ''){
                    $QRsavePath = $QR_server."images/_".$_REQUEST['club_name']."/qrcodes/" . $user_id . ".png";
                }else{
                    $QRsavePath = "";
                }
                 
                /*check user group*/ 
                $usergroup2 = $userdata['usergroup2'];
                $result = $pdo->prepare("SELECT name FROM usergroups2 WHERE id = $usergroup2");
                $result->execute();
                $row = $result->fetch();
                
                if($userdata['usergroup2']){
                    $groupName2 = $row['name'];
                }else{
                    $groupName2 = "";
                }

                $day   = $userdata['day'];
                $month = $userdata['month'];
                $year  = $userdata['year'];

                if ($day != 0) {
                    $bdayraw = $day . "." . $month . "." . $year;
                    $bday = new DateTime($bdayraw);
                    $today = new DateTime(); // for testing purposes
                    $diff = $today->diff($bday);
                    $age = $diff->y;
                    
                    $birthday = date("d-m", strtotime($bdayraw));
                } else {
                    $birthday = '';
                }

                // Query to look up total sales and find weekly average
                $user_id    =   $userdata['user_id'];
                $daysMember = $userdata['daysMember'];
                $result = $pdo->prepare("SELECT COUNT(saleid) FROM sales WHERE userid = $user_id");
                $result->execute();
                $row = $result->fetch();
                $totalDispenses = $row['COUNT(saleid)'];
                $totalDispensesPerDay = $totalDispenses / $daysMember;
                $totalDispensesPerWeek = $totalDispensesPerDay * 7;
              
                /*user star*/
                $friend  = $userdata['friend'];
                $friend2 = $userdata['friend2'];
                $starCat = $userdata['starCat']; 

                if ($starCat == 1) {
                    $userStar = SITE_ROOT."/api/image/star-yellow.png";
                } else if ($starCat == 2) {
                    $userStar = SITE_ROOT."/api/image/star-black.png";
                } else if ($starCat == 3) {
                    $userStar = SITE_ROOT."/api/image/star-green.png";
                } else if ($starCat == 4) {
                    $userStar = SITE_ROOT."/api/image/star-red.png";
                } else if ($starCat == 5) {
                    $userStar = SITE_ROOT."/api/image/star-purple.png";
                } else if ($starCat == 6) {
                    $userStar = SITE_ROOT."/api/image/star-blue.png";
                } else {
                    $userStar = "";
                }

                /*user expired date*/
                $paidUntil = $userdata['paidUntil'];
                $memberExp = date('y-m-d', strtotime($paidUntil));
                $memberExpReadable = date('d M Y', strtotime($paidUntil));
                $member_exp_date = date('d / M', strtotime($paidUntil));
                $timeNow = date('y-m-d');
                $paymentWarning = $userdata['paymentWarning'];
                $paymentWarningDate = $userdata['paymentWarningDate'];
                $paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
                $errimage = SITE_ROOT."/api/image/exclamation-22.png";
                
                if($userdata['exento'] != 1){
                
                    if($usertype != 1 || $usertype != 2 || $usertype != 3 || $usertype != 4 ){

                        if (strtotime($memberExp) == strtotime($timeNow)) {
                            $userexpiredate = 'Membership expires today';
                        } else if (strtotime($memberExp) > strtotime($timeNow)) { 
                            $userexpiredate = 'Member until'.':'.$memberExpReadable;
                        } else {
                            $userexpiredate ='Membership expired on'. ':' .$memberExpReadable;
                            if ($paymentWarning == '1') {
                              $userexpiredate = 'Received warning' .':'. $paymentWarningDateReadable;
                            }
                            
                        }
                    } else{
                        $userexpiredate = "";
                    }
                }else{
                    $userexpiredate = "";
                }
                    $member_nickname = '';
                    if(isset($userdata['app_member']) && $userdata['app_member'] != null){
                        $app_member = $userdata['app_member'];
                        // fetch member username from app
                        $selectMember = "SELECT username FROM members WHERE id =".$app_member;
                        $member_result = $pdo_master->prepare("$selectMember");
                        $member_result->execute();
                        $member_row = $member_result->fetch();
                        $member_nickname = $member_row['username'];

                    }
                    $userarr['member_nickname'] = $member_nickname;

                    /*personal detail*/
                    $userarr['first_section_img']  = $userStar;
                    $userarr['first_section']      = $userdata['memberno'] . " - " . ucwords($userdata['first_name']) . " " . ucwords($userdata['last_name']);
                    $userarr['second_section']      = $userdata['gender'] .' , '. $age .' '.'Years old';
                    $userarr['second_section_title']      = $age .' '.'Years';
                    $userarr['third_section']       = $credit .' '.'€';
                    $userarr['four_section']        = $usertype;
                    $userarr['qr_code']        = $QRsavePath;
                    $userarr['member_exp_date']        = $member_exp_date;
                   
                    if (strtotime($memberExp) == strtotime($timeNow)) {
                        $userarr['five_section_image']  = $errimage;
                    } else if (strtotime($memberExp) > strtotime($timeNow)) { 
                        $userarr['five_section_image']  = "";
                    } else {
                        $userarr['five_section_image']  = $errimage;
                        
                        if ($paymentWarning == '1') {
                            $userarr['five_section_image']  = $errimage;
                        }else{
                            $userarr['five_section_image']  = $errimage;
                        }
                        
                    } 
                    
                    $userarr['five_section']        = $userexpiredate;
                     // Is the user a high roller.
                    $selectHighRollerLimit = "SELECT highRollerWeekly FROM systemsettings";
                    $result = $pdo->prepare("$selectHighRollerLimit");
                    $result->execute();
                    $row = $result->fetch();
                    $highRollerWeekly = $row['highRollerWeekly'];

                    $result = $pdo->prepare("SELECT SUM(amount) FROM sales WHERE userid = $user_id");
                    $result->execute();
                     $row = $result->fetch();
                    $totalAmount = $row['SUM(amount)'];
                    $totalAmountPerDay = $totalAmount / $daysMember;
                    $totalAmountPerWeek = $totalAmountPerDay * 7;

                    if ($totalAmountPerWeek >= $highRollerWeekly) {
                        $userarr['sex_section_image']= SITE_ROOT."/api/image/hi-roller.png";
                        $userarr['sex_section']  ='High roller';
                    }else{
                        $userarr['sex_section_image']= "";
                        $userarr['sex_section']  ="";
                    }

                    $userarr['memberno']      = $userdata['memberno'];
                    $userarr['first_name']    = ucwords($userdata['first_name']);
                    $userarr['last_name']     = ucwords($userdata['last_name']);
                    $userarr['age']           = $birthday .','. $age .''.'Years old';
                    $userarr['birth_date']    = $birthday;
                    $userarr['user_image']    = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/members/' . $userdata['user_id'] . '.' .  $userdata['photoext'];
                    $userarr['gender']                = $userdata['gender'];
                    $userarr['admin_comment']         = $userdata['adminComment'];
                    $userarr['colour']                = $colour;
                    $userarr['user_type']             = $usertype;
                    $userarr['user_group']            = $groupName2;
                    $userarr['member_interviewed']    = $interview;
                    $userarr['exempt_membership_fee'] = $exempt_membership_fee;
                    $userarr['register_date']         = date('d-m-Y H:i:s',strtotime($userdata['registeredSince']));
                    $userarr['nationality']    = $userdata['nationality'];
                    $userarr['dnipassport']    = $dni;

                    $userarr['groupName']            = $groupName;
                    $userarr['mark_icon']            = $mark_icon;

                    /*contact detail*/
                    $userarr['telephone']      = $telephone;
                    $userarr['email']          = $email;
                    $userarr['street']         = $street;
                    $userarr['streetnumber']   = $streetnumber;
                    $userarr['postcode']       = $postcode;
                    $userarr['city']           = $city;
                    $userarr['country']        = $country;
                  
                    $userarr['card_id2']       = $cardid2;
                    $userarr['card_id3']       = $cardid3;
                    $userarr['user_credit']    = $credit;
                    $userarr['banTime']        = $banTime;
                    $userarr['deleteTime']     = $deleteTime;

                    /*usage type*/
                    $userarr['usageType']      = $usageType;
                    $userarr['mconsumption']   = $mconsumption;

                    /*Discounts bar*/
                    $userarr['discountdispensary']       = $discount .''.'%';
                    $userarr['discountBar']              = $discountBar .''. '%';

                    /*systemspecific*/
                   /* $userarr['user_id']        = $userdata['user_id'];
                    $userarr['Signupsource']   = $signupsource;
                    $userarr['card_id']        = $cardid;*/

                    // Query to look up total sales and find weekly average
                    $result = $pdo->prepare("SELECT SUM(amount) FROM sales WHERE userid = $user_id");
                    $result->execute();
                     $row = $result->fetch();
                    $totalAmount = $row['SUM(amount)'];
                    $totalAmountPerDay = $totalAmount / $daysMember;
                    $totalAmountPerWeek = $totalAmountPerDay * 7;

                    $result = $pdo->prepare("SELECT COUNT(saleid) FROM sales WHERE userid = $user_id");
                    $result->execute();
                    $row = $result->fetch();
                    $totalDispenses = $row['COUNT(saleid)'];
                    $totalDispensesPerDay = $totalDispenses / $daysMember;
                    $totalDispensesPerWeek = $totalDispensesPerDay * 7;

                    /*weekly average*/
                    $userarr['dispenses']    = number_format($totalDispensesPerWeek,0);
                    $userarr['spenditure']   = number_format($totalAmountPerWeek,0).' '.'€';


                    // Select flower purchases
                    $result = $pdo->prepare("SELECT SUM(d.quantity), SUM(d.amount) FROM salesdetails d, sales s WHERE d.category = 1 AND s.userid = $user_id AND s.saleid = d.saleid");
                    $result->execute();
                    $row = $result->fetch();
                    $totalFlowers = $row['SUM(d.quantity)'];

                    // Select extract purchases
                    $result = $pdo->prepare("SELECT SUM(d.quantity), SUM(d.amount) FROM salesdetails d, sales s WHERE d.category = 2 AND s.userid = $user_id AND s.saleid = d.saleid");
                    $result->execute();
                    $row = $result->fetch();
                    $totalExtracts = $row['SUM(d.quantity)'];
                    $totalPurchases = $totalExtracts + $totalFlowers;
                        
                    if ($totalFlowers > $totalExtracts) {
                        $favouriteCategory = 'Flower';
                        $percentage = ($totalFlowers / $totalPurchases) * 100;
                    } else if ($totalFlowers < $totalExtracts) {
                        $favouriteCategory = 'Extract';
                        $percentage = ($totalExtracts / $totalPurchases) * 100;
                    } else if ($totalFlowers == $totalExtracts && $totalFlowers != 0) {
                        $favouriteCategory = 'Both';
                        $percentage = 50;
                    } else {
                        $favouriteCategory = '';
                        $percentage = '';
                    }
                    if ($favouriteCategory != '') {
                        $categorypercentage = number_format($percentage,0);
                    }else{
                        $categorypercentage  = "";
                    } 
                    /*prefrence*/
                    $userarr['category']    = 'Category: Flor'.' '.'('.$categorypercentage.')'.''.'%';

                    // Select favourite products
                    $resultF = $pdo->prepare("SELECT d.category, d.productid, SUM(d.quantity) FROM salesdetails d, sales s WHERE (d.category = 1 OR d.category = 2) AND s.userid = $user_id AND s.saleid = d.saleid GROUP by d.category, d.productid ORDER by SUM(d.quantity) DESC");
                    $resultF->execute();
                  
                    // Get the five favourites
                    for ($i = 1; $i < 6; $i++) {
                    
                        $rowF = $resultF->fetch();
                            $category = $rowF['category'];
                            $productid = $rowF['productid'];
                            $quantity = $rowF['SUM(d.quantity)'];
                        
                        if ($category == 1) {
                                $result = $pdo->prepare("SELECT name, breed2 from flower where flowerid = '$productid'");
                                $result->execute();
                                $row = $result->fetch();
                                $name = $row['name'] . " " . $row['breed2'];
                    
                        } else if ($category == 2) {
                            
                                $result = $pdo->prepare("SELECT name from extract where extractid = '$productid'");
                                $result->execute();
                                $row = $result->fetch();
                                $name = $row['name'];
                                
                        } else {
                            
                                $result = $pdo->prepare("SELECT name from products where productid = '$productid'");
                                $result->execute();
                                $row = $result->fetch();
                                $name = $row['name'];
                        }
                        
                        ${'favourite' . $i} = $name;
                        ${'quantity' . $i} = $quantity;
                                
                    }
                    $userarr['Preferences1']     = '#1:'.' '.$favourite1 . " (" . number_format($quantity1,0) . " g)".''.'%';
                    $userarr['Preferences2']     = '#2:'.' '.$favourite2 . " (" . number_format($quantity2,0) . " g)".''.'%';
                    $userarr['Preferences3']     = '#3:'.' '.$favourite3 . " (" . number_format($quantity3,0) . " g)".''.'%';
                    $userarr['Preferences4']     = '#4:'.' '.$favourite4 . " (" . number_format($quantity4,0) . " g)".''.'%';
                    $userarr['Preferences5']     = '#5:'.' '.$favourite5 . " (" . number_format($quantity5,0) . " g)".''.'%';


                    /*usergurdation*/
                    $friend = $userdata['friend'];
                    $friend2 = $userdata['friend2'];
                    $interview = $userdata['interview'];

                    $GuardianarrFirst = array();
                    $GuardianarrSecond = array();

                    if ($friend > 0 && $friend2 > 0) {

                        /*friend section-1 detail*/
                        $friendDetails1 = "SELECT starCat, memberno, first_name, last_name, photoext, userGroup  FROM users WHERE user_id = $friend";
                        $result = $pdo->prepare("$friendDetails1");
                        $result->execute();
                        $row = $result->fetch();
                        $starCat1 = $row['starCat'];
                        $photoext1 = $row['photoext'];

                        if(!empty($row['memberno'])){ 
                            $memberno1 = $row['memberno'];
                        }else{
                            $memberno1 = "";
                        }

                        if(!empty($row['first_name'])){ 
                            $first_name1 = $row['first_name'];
                        }else{
                            $first_name1 = "";
                        }

                        if(!empty($row['last_name'])){ 
                            $last_name1 = $row['last_name'];
                        }else{
                            $last_name1 = "";
                        }

                        if($row['userGroup'] == 1){
                            $userfirstype = 'Administrator';
                        }elseif($row['userGroup'] == 2){
                            $userfirstype = 'Worker';
                        }elseif($row['userGroup'] == 3){
                            $userfirstype = 'Volunteer';
                        }elseif($row['userGroup'] == 4){
                            $userfirstype = 'Professional contact';
                        }elseif($row['userGroup'] == 5){
                            $userfirstype = 'Member';
                        }elseif($row['userGroup'] == 6){
                            $userfirstype = 'Visitor';
                        }elseif($row['userGroup'] == 7){
                            $userfirstype = 'Banned';
                        }elseif($row['userGroup'] == 8){
                            $userfirstype = 'Deleted';
                        }elseif($row['userGroup'] == 9){
                            $userfirstype = 'Inactive';
                        }else{
                            $userfirstype = '';
                        }

                        /*user star*/
                        if ($starCat1 == 1) {
                            $userStar1 = SITE_ROOT."/api/image/star-yellow.png";
                        } else if ($starCat1 == 2) {
                            $userStar1 = SITE_ROOT."/api/image/star-black.png";
                        } else if ($starCat1 == 3) {
                            $userStar1 = SITE_ROOT."/api/image/star-green.png";
                        } else if ($starCat1 == 4) {
                            $userStar1 = SITE_ROOT."/api/image/star-red.png";
                        } else {
                            $userStar1 = "";
                        }
                        
                       
                        if ($friend && $photoext1) {

                            $GuardianarrFirst['Guardian_first']    = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/members/' . $friend . '.' .  $photoext1;
                            $GuardianarrFirst['Guardianfirst_img']    = $userStar1;
                            /*$GuardianarrFirst['Guardianfirst_detail']    = $memberno1.' - '.$first_name1.' '.$last_name1;*/
                            $GuardianarrFirst['guardianfirst_member_no']    = $memberno1;
                            $GuardianarrFirst['guardianfirst_name']    = $first_name1.' '.$last_name1;
                            $GuardianarrFirst['guardianfirst_type']    = $userfirstype;

                        } else {

                            $GuardianarrFirst['Guardian_first']    = SITE_ROOT."/images/silhouette.png";
                            $GuardianarrFirst['Guardianfirst_img']       = "";
                            $GuardianarrFirst['guardianfirst_member_no'] = "";
                            $GuardianarrFirst['guardianfirst_name']    = 'No guardian #1';;
                            $GuardianarrFirst['guardianfirst_type']    = "";

                        }

                        /*friend section-2 detail*/
                        $friendDetails2 = "SELECT starCat, memberno, first_name, last_name, photoext,userGroup FROM users WHERE user_id = $friend2";
                        $result = $pdo->prepare("$friendDetails2");
                        $result->execute();
                        $row = $result->fetch();
                        $starCat2 = $row['starCat'];
                        $photoext2 = $row['photoext'];

                        if(!empty($row['memberno'])){ 
                            $memberno2 = $row['memberno'];
                        }else{
                            $memberno2 = "";
                        }

                        if(!empty($row['first_name'])){ 
                            $first_name2 = $row['first_name'];
                        }else{
                            $first_name2 = "";
                        }

                        if(!empty($row['last_name'])){ 
                            $last_name2 = $row['last_name'];
                        }else{
                            $last_name2 = "";
                        }

                        if ($starCat2 == 1) {
                            $userStar2 = SITE_ROOT."/api/image/star-yellow.png";
                        } else if ($starCat2 == 2) {
                            $userStar2 = SITE_ROOT."/api/image/star-black.png";
                        } else if ($starCat2 == 3) {
                            $userStar2 = SITE_ROOT."/api/image/star-green.png";
                        } else if ($starCat2 == 4) {
                            $userStar2 =  SITE_ROOT."/api/image/star-red.png";
                        } else {
                            $userStar2 = "";
                        }

                        if($row['userGroup'] == 1){
                            $usersectype = 'Administrator';
                        }elseif($row['userGroup'] == 2){
                            $usersectype = 'Worker';
                        }elseif($row['userGroup'] == 3){
                            $usersectype = 'Volunteer';
                        }elseif($row['userGroup'] == 4){
                            $usersectype = 'Professional contact';
                        }elseif($row['userGroup'] == 5){
                            $usersectype = 'Member';
                        }elseif($row['userGroup'] == 6){
                            $usersectype = 'Visitor';
                        }elseif($row['userGroup'] == 7){
                            $usersectype = 'Banned';
                        }elseif($row['userGroup'] == 8){
                            $usersectype = 'Deleted';
                        }elseif($row['userGroup'] == 9){
                            $usersectype = 'Inactive';
                        }else{
                            $usersectype = '';
                        }

                        if ($friend2 && $photoext1) {
                            $GuardianarrSecond['Guardian_second']    = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/members/' . $friend2 . '.' .  $photoext1;
                            $GuardianarrSecond['Guardiansecond_img']    = $userStar2;
                          /*  $GuardianarrSecond['Guardiansecond_detail']    = $memberno2.' - '.$first_name2.' '.$last_name2;*/
                            $GuardianarrSecond['guardiansecond_member_no']    = $memberno2;
                            $GuardianarrSecond['guardiansecond_name']    =$first_name2.' '.$last_name2;
                            $GuardianarrSecond['guardiansecond_type']    =$usersectype;
                        } else {
                            $GuardianarrSecond['Guardian_second']       = SITE_ROOT."/images/silhouette.png";;
                            $GuardianarrSecond['Guardiansecond_img']    = "";
                            $GuardianarrSecond['Guardiansecond_detail'] = 'No guardian #2';
                            $GuardianarrSecond['guardiansecond_member_no']  = "";
                            $GuardianarrSecond['guardiansecond_name']    = 'No guardian #2';
                            $GuardianarrSecond['guardiansecond_type']    = "";

                        }
                        $GuardianarrSecond['Guardian_memberintrviewd'] = "";

                    } else if ($friend > 0) {
                        /*friend section-1 detail*/
                        $friendDetails1 = "SELECT starCat, memberno, first_name, last_name, photoext,userGroup FROM users WHERE user_id = $friend";
                        $result = $pdo->prepare("$friendDetails1");
                        $result->execute();
                        $row = $result->fetch();
                        $starCat1 = $row['starCat'];
                        $photoext1 = $row['photoext'];

                        if(!empty($row['memberno'])){ 
                            $memberno1 = $row['memberno'];
                        }else{
                            $memberno1 = "";
                        }

                        if(!empty($row['first_name'])){ 
                            $first_name1 = $row['first_name'];
                        }else{
                            $first_name1 = "";
                        }

                        if(!empty($row['last_name'])){ 
                            $last_name1 = $row['last_name'];
                        }else{
                            $last_name1 = "";
                        }

                        /*user star*/
                        if ($starCat1 == 1) {
                            $userStar1 = SITE_ROOT."/api/image/star-yellow.png";
                        } else if ($starCat1 == 2) {
                            $userStar1 = SITE_ROOT."/api/image/star-black.png";
                        } else if ($starCat1 == 3) {
                            $userStar1 = SITE_ROOT."/api/image/star-green.png";
                        } else if ($starCat1 == 4) {
                            $userStar1 = SITE_ROOT."/api/image/star-red.png";
                        } else {
                            $userStar1 = "";
                        }

                        if($row['userGroup'] == 1){
                            $userfirstype = 'Administrator';
                        }elseif($row['userGroup'] == 2){
                            $userfirstype = 'Worker';
                        }elseif($row['userGroup'] == 3){
                            $userfirstype = 'Volunteer';
                        }elseif($row['userGroup'] == 4){
                            $userfirstype = 'Professional contact';
                        }elseif($row['userGroup'] == 5){
                            $userfirstype = 'Member';
                        }elseif($row['userGroup'] == 6){
                            $userfirstype = 'Visitor';
                        }elseif($row['userGroup'] == 7){
                            $userfirstype = 'Banned';
                        }elseif($row['userGroup'] == 8){
                            $userfirstype = 'Deleted';
                        }elseif($row['userGroup'] == 9){
                            $userfirstype = 'Inactive';
                        }else{
                            $userfirstype = '';
                        }


                        if ($friend && $photoext1) {
                            $GuardianarrFirst['Guardian_first']    = SITE_ROOT.'/images/_' . $_REQUEST['club_name'] . '/members/' . $friend . '.' .  $photoext1;
                            $GuardianarrFirst['Guardianfirst_img']    = $userStar1;
/*                            $GuardianarrFirst['Guardianfirst_detail']    = $memberno1.' - '.$first_name1.' '.$last_name1;*/
                            $GuardianarrFirst['guardianfirst_member_no']    = $memberno1;
                            $GuardianarrFirst['guardianfirst_name']    = $first_name1.' '.$last_name1;
                            $GuardianarrFirst['guardianfirst_type']    = $userfirstype;
                        } else {
                            $GuardianarrFirst['Guardian_first']    = SITE_ROOT."/images/silhouette.png";
                            $GuardianarrFirst['guardianfirst_member_no'] = "";
                            $GuardianarrFirst['guardianfirst_name']    = 'No guardian #1';
                            $GuardianarrFirst['guardianfirst_type']    = "";
                        }

                        $GuardianarrSecond['Guardian_second']       = SITE_ROOT."/images/silhouette.png";;
                        $GuardianarrSecond['Guardiansecond_img']    = "";
                        $GuardianarrSecond['Guardiansecond_detail'] = 'No guardian #2';
                        $GuardianarrSecond['guardiansecond_member_no']  = "";
                        $GuardianarrSecond['guardiansecond_name']    = 'No guardian #2';
                        $GuardianarrSecond['guardiansecond_type']    = "";


                    } else {
                            
                        $GuardianarrFirst['Guardian_first']       = SITE_ROOT."/images/silhouette.png";
                        $GuardianarrFirst['Guardianfirst_img']    = "";
                        $GuardianarrFirst['Guardianfirst_detail'] = 'No guardian #1';
                        $GuardianarrFirst['guardianfirst_member_no'] = "";
                        $GuardianarrFirst['guardianfirst_name']    = 'No guardian #1';
                        $GuardianarrFirst['guardianfirst_type']    = "";

                        $GuardianarrSecond['Guardian_second']       = SITE_ROOT."/images/silhouette.png";;
                        $GuardianarrSecond['Guardiansecond_img']    = "";
                        $GuardianarrSecond['Guardiansecond_detail'] = 'No guardian #2';
                        $GuardianarrSecond['guardiansecond_member_no']  = "";
                        $GuardianarrSecond['guardiansecond_name']    = 'No guardian #2';
                        $GuardianarrSecond['guardiansecond_type']    = "";

                        if ($interview == 0) {
                            $interviewed = "NO";
                        } else {
                            $interviewed = "Yes";
                        }

                        $GuardianarrSecond['Guardian_memberintrviewd'] = 'Member interviewed' .' : ' . $interviewed;
                    }
                                        
                    $response['data'] = $userarr;
                    $response['data']['guardian'][] = $GuardianarrFirst;
                    $response['data']['guardian'][] = $GuardianarrSecond;
                    echo json_encode($response);
            }else{
                if($lang=='es')
                {	
                    $response = array('flag' => '0', 'message' => 'Please add parameter in language id.');
                }else{
                    $response = array('flag' => '0', 'message' => 'Please add parameter in language id.');
                }
                //$response = array('flag' => '0', 'message' => 'Please add parameter in language id.');
                echo json_encode($response);
            }; 

        }else{
            if($lang=='es')
            {	
                $response = array('flag' => '0', 'message' => 'Please add parameter in language id and user_id.');
            }else{
                $response = array('flag' => '0', 'message' => 'Please add parameter in language id and user_id.');
            }
            //$response = array('flag' => '0', 'message' => 'Please add parameter in language id and user_id.');
            echo json_encode($response);
        }


    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response);
    }
?>