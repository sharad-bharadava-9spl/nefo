<?php 

   define("SITE_ROOT", "https://devsj72web.websiteserverhost.com/cannabisclub");
    define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/");
    define("DATABASE_HOST", "localhost");
    
    $siteroot = SITE_ROOT; // Used for href, src, header(Location:)
    $hostroot = HOST_ROOT; // Used for includes --- and for uploads? CHECK!

    // Define constants for success/error messages
    define("MESSAGESUCCESS", "success");
    define("MESSAGEERROR", "error");

    define("USERNAME", "devsj72web_cannabisclub");
    define("PASSWORD", "TG=wLf]nfo?q");
    define("DATABASE_NAME", "devsj72web_ccs_masterdb");

    try {
        //echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
        $pdo = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, USERNAME, PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('SET NAMES "utf8"');
    }
    catch (PDOException $e) {
        $response = array('flag'=>'0', 'message' => $e->getMessage());
        echo json_encode($response); 
        
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

        if(!empty($lang == 'es') || !empty($lang == 'en')){
            /*check unique record number in order detail*/
            $userData = "SELECT * FROM `users`";
            $resultuser = $pdo->prepare("$userData");
            $resultuser->execute();
            $response['data'] = array();
            $new_arr = array();
                if($resultuser->rowCount() > 0){

                    
                    $response = array('flag' => '1','message' => 'Club Found Successfull' , 'status' => 'true');
                    
                    while ($club = $resultuser->fetch()) {

                        $new_arr['user_id']    = $club['id'];
                        $new_arr['club_name']  = $club['domain'];
                        $new_arr['club_email'] = $club['email'];
                        $response['data'][]    = $new_arr;
                    }
                    echo json_encode($response);

                }else{

                    $response = array('flag' => '0', 'message' => 'User Not Found.' ,'status' => 'false');
                    echo json_encode($response);
                }

        }else{

            $response = array('flag' => '0', 'message' => 'Please add parameter in language id.','status' => 'false');
            echo json_encode($response);
        }


    }catch(PDOException $e){

        $response = array('flag'=>'0', 'message' => $e->getMessage(), 'status' => 'false');
        echo json_encode($response);    
    }