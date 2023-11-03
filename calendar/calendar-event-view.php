<?php
ini_set("display_errors", "off");
session_start();

if(isset($_SESSION['user_id'])){

    if($_REQUEST['user_id'] == '' || $_REQUEST['event_id'] == '' || $_REQUEST['access'] == '' || $_REQUEST['domain'] == ''){
        die("You are not authorised to view this page !");
    }

  }else{

    if($_REQUEST['user_id'] == '' || $_REQUEST['event_id'] == '' || $_REQUEST['access'] == '' || $_REQUEST['domain'] == '' || $_REQUEST['reply_by'] == ''){
        die("You are not authorised to view this page !");
    }

  }

    require_once 'connection-calendar-view.php';
    require_once '../cOnfig/view-event.php';
   // require_once '../cOnfig/authenticate.php';
    require_once '../cOnfig/languages/common.php';
    
    getSettings(); 
    
    pageStart('Event', NULL, null, "calender-view", NULL, "Event Details", $_SESSION['successMessage'], $_SESSION['errorMessage']);
    ?>
    <style type="text/css">
      .actionbox-np2{
        width: 45%;
      }
      .purchasetable{
        width: 94%;
      }
     .purchasetable td{
        width: 100% !important;
        text-transform: none;
        font-size: 19px !important;
     }
     .purchaseNumber{
        width: auto;
     }
    </style>


    <?php
  // check for valid reply mail
    if(isset($_REQUEST['reply_by'])){
      
      $validuser = base64_decode($_REQUEST['reply_by'], true);
      if($validuser === false){
             echo "You are not authorised to view this event !";
            die();
      }else{
            $checkReplyuser = "SELECT COUNT(*) from events_guest_members WHERE user_id='".$validuser."' AND event_id =".$_REQUEST['event_id'];

                  try {
                      $chk_statement = $pdo3->prepare("$checkReplyuser");
                      $chk_statement->execute();
                      $chk_result = $chk_statement->fetch();
                  }
                  catch (PDOException $e){
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
                }
                $userexist = $chk_result['COUNT(*)'];

                if($userexist == 0){
                    echo "You are not authorised to view this event !";
                    die();
                }

        }
    }

  $query = "SELECT * from events where id =".$_REQUEST['event_id']." AND access ='".$_REQUEST['access']."' AND user_id = ".$_REQUEST['user_id']; 

  try {
        $statement = $pdo3->prepare("$query");
        $statement->execute();
        $result = $statement->fetch();
    }
    catch (PDOException $e){
      $error = 'Error fetching user: ' . $e->getMessage();
      echo $error;
      exit();
  }

$eventCount = count($result);
 
if(empty($result)){
     echo "You are not authorised to view this event !";
    die();
}



/*  echo "<pre>";
  print_r($result); */


// get ettachments
    $getAttachments = "SELECT file_name from event_attachments where event_id = ".$_REQUEST["event_id"];
      try
      {
          $attach_results = $pdo3->prepare("$getAttachments");
          $attach_results->execute();
      }
      catch (PDOException $e)
      {
              $error = 'Error fetching attachment: ' . $e->getMessage();
              echo $error;
              exit();
      }
      $attachCount = $attach_results->rowCount();
      if($attachCount > 0){
          $x =0;
          while($attachRow = $attach_results->fetch()){
              $attach_no = $x+1;
              $attach_arr[] = "<a href='".$main_site.$attachRow['file_name']."' target='_blank'>Attachment $attach_no</a>"; 
              $x++;   
          }
          $attachments = implode("<br>", $attach_arr);
      }else{
          $attachments = 'No files found !';
      }



      // get usergroups
$usergroups = $result['usergroups'];
if($usergroups == ''){
  $usergroups = -1;
}
  $selectgroups = "SELECT * FROM `usergroups` WHERE userGroup IN ($usergroups)";
  try
  {
    $selectgroups = $pdo3->prepare("$selectgroups");
    $selectgroups->execute();
  }
  catch (PDOException $e)
  {
      $error = 'Error fetching Groups: ' . $e->getMessage();
      echo $error;
      exit();
  }
while($result_usegroup = $selectgroups->fetch()){
    $usegroup_arr[] =  $result_usegroup['groupName'];
}

$userGroupName = implode(", ", $usegroup_arr);

// get registered users for events

     $user_query = "SELECT a.*, b.*  FROM users a, events_members b WHERE b.user_id=a.user_id AND  b.event_id = ".$_REQUEST['event_id']."   ORDER BY id"; 
  
    try {
        $user_statement = $pdo3->prepare($user_query);
        $user_statement->execute();
        
    }
    catch (PDOException $e){
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
    }


    // get guest users for events

     $guestuser_query = "SELECT *  FROM events_guest_members WHERE event_id = ".$_REQUEST['event_id']."   ORDER BY id"; 
  
    try {
        $guestuser_statement = $pdo3->prepare($guestuser_query);
        $guestuser_statement->execute();
        
    }
    catch (PDOException $e){
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
    }
    $guestCount =  $guestuser_statement->rowCount();
  
?>
<center>
<div class="actionbox-np2">
 <div class="mainboxheader">
  <center>Event Details</center>
 </div>
 <div class="boxcontent">
  <table class="purchasetable">
   <tbody><tr>
    <td class="left">Title<span class="purchaseNumber"><?php echo $result['title']; ?></span></td>
   </tr>
   <tr>
    <td class="left">Location<span class="purchaseNumber"><?php echo $result['location']; ?></span></td>
   </tr>   
   <tr>
    <td class="left">Description<span class="purchaseNumber"><?php echo $result['description']; ?></span></td>
   </tr>   
   <tr>
    <td class="left">Attachments<span style="float: right;"><?php echo $attachments; ?></span></td>
   </tr>   
   <tr>
    <td class="left">Start Time<span class="purchaseNumber"><?php echo $result['start_event']; ?></span></td>
   </tr>  
   <?php if($result['recurring_time'] != ''){ ?>  
   <tr>
    <td class="left">Repeat At<span class="purchaseNumber"><?php echo $result['recurring_time']; ?></span></td>
   </tr>   
   <tr>
    <td class="left">End Time<span class="purchaseNumber"><?php echo $result['end_event']; ?></span></td>
   </tr> 
 <?php } ?>
   <?php   if(isset($_SESSION['user_id'])){ ?>
   <tr>
    <td class="left">Usergroups<span class="purchaseNumber"><?php echo $userGroupName; ?></span></td>
   </tr>   
   <tr>
    <td class="left">Members<span class="purchaseNumber">
            <?php
              while($user_result = $user_statement->fetch()){

                $user_ans = $user_result['ans'];
                if($user_ans == ''){
                  $user_ans = '(<strong>Pending</strong>)';
                }else if($user_ans == 'yes'){
                  $user_ans = "<img src='images/accept.png' height='16'/>";
                }else if($user_ans == 'no'){
                  $user_ans = "<img src='images/reject.png' height='16'/>";
                }else if($user_ans == 'maybe'){
                  $user_ans = '(<strong>May Be</strong>)';
                }

                  echo $user_result['first_name']." ".$user_result['last_name']." ".$user_ans."<br>";
              }
            ?>
      </span></td>
   </tr>   
 <?php } ?>
 <?php if($guestCount > 0 && isset($_SESSION['user_id'])){ ?>
   <tr>
    <td class="left">Guest Invitations<span class="purchaseNumber">
        <?php
            while($guestuser_result = $guestuser_statement->fetch()){

                $guestuser_ans = $guestuser_result['ans'];
                if($guestuser_ans == ''){
                  $guestuser_ans = '(<strong>Pending</strong>)';
                }else if($guestuser_ans == 'yes'){
                  $guestuser_ans = "<img src='images/accept.png' height='16'/>";
                }else if($guestuser_ans == 'no'){
                  $guestuser_ans = "<img src='images/reject.png' height='16'/>";
                }else if($guestuser_ans == 'maybe'){
                  $guestuser_ans = '(<strong>May Be</strong>)';
                }
                echo $guestuser_result['user_id']." ".$guestuser_ans."<br>";
            }
        ?>
    </span></td>
   </tr>
   <?php } ?>
   <?php  if($_SESSION['user_id'] != $_REQUEST['user_id']){  ?>
   <tr>
    <?php  
        if(isset($_SESSION['user_id'])){
          $form_action = "calendar-event-user-ans.php";
          $reply_user_id = $_SESSION['user_id'];
          $getReply= "SELECT ans from  events_members WHERE user_id ='".$reply_user_id."' AND event_id =".$_REQUEST['event_id'];
        }else{
          $form_action = "calendar-event-guest-user-ans.php";
          $reply_user_id = base64_decode($_REQUEST['reply_by']);
          $getReply= "SELECT ans from  events_guest_members WHERE user_id ='".$reply_user_id."' AND event_id =".$_REQUEST['event_id'];
        }


        try {
            $reply_statement = $pdo3->prepare($getReply);
            $reply_statement->execute();
            
        }
        catch (PDOException $e){
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
        }
        $reply_row = $reply_statement->fetch();
          $answer = $reply_row['ans'];

     ?>
         <td class="left">Are you Going/Attend? :
            <form action="<?php echo $form_action; ?>" method="post" id="reply_form" class="purchaseNumber">
              <select name="reply_ans" id="ans">
                <option value="yes">Yes</option>
                <option value="no">No</option>
                <option value="maybe">May be</option>
              </select>
              <input type="hidden" name="reply_user" value="<?php echo $reply_user_id; ?>" >
              <input type="hidden" name="domain" value="<?php echo $_REQUEST['domain']; ?>" >
              <input type="hidden" name="reply_event" id="reply_event_id" value="<?php echo $_REQUEST['event_id'] ?>">
              <input type="hidden" name="reply_event_user_id" id="reply_event_user_id" value="<?php echo $_REQUEST['user_id']; ?>">
              <input type="hidden" name="reply_event_cal_id" id="reply_event_cal_id" value="<?php echo $result['event_cale_id'] ?>">
          </form>
        </td>
     </tr>
 <?php } ?>
  </tbody></table>
 </div>
 <?php  if($_SESSION['user_id'] != $_REQUEST['user_id']){ ?>
    <button class="cta1" id="reply_sub">Submit</button>

    <script type="text/javascript">
      $(document).ready(function(){
          $("#ans").val("<?php echo $answer ?>");
          $("#reply_sub").click(function(){
              $("#reply_form").submit();
          });
      })
    </script>
<?php } ?>
 </div>
</center>


<?php

  displayFooter(); ?>