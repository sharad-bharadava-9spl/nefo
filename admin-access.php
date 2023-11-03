<!-- Admin-level menu -->
<?php 

  require_once 'cOnfig/connection.php';
  require_once 'cOnfig/view.php';
  require_once 'cOnfig/authenticate.php';
  require_once 'cOnfig/languages/common.php';
  
  session_start();
  
  $accessLevel = '1';
  
  // Authenticate & authorize
  authorizeUser($accessLevel);
  
  getSettings();   
// update page access function
  $query = "SELECT setting3, setting4, appointments FROM systemsettings";
  try
  {
    $result = $pdo3->prepare("$query");
    $result->execute();
  }
  catch (PDOException $e)
  {
      $error = 'Error fetching user: ' . $e->getMessage();
      echo $error;
      exit();
  }

  $row = $result->fetch();
    $setting3 = $row['setting3'];
    $setting4 = $row['setting4'];
    $appointments = $row['appointments'];

   // get all pages

   $selectPages = "SELECT * from admin_page_access";

        try
        {
           $pageResult = $pdo3->prepare("$selectPages");
           $pageResult->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }

     while($page_result_row = $pageResult->fetch()){
        $page_id_arr[] = $page_result_row['page_id'];

     }  

    
if(isset($_POST['oneClick'])){

   $post_arr = $_POST;

      

   foreach ($post_arr as $page_key => $access_val) {
     if($page_key != 'oneClick'){
        $page_id = str_replace("page_", "", $page_key);

        $post_id_arr[] = $page_id; 
        update_page_access($page_id, $access_val);

        // update default access to '1'

         $updateDefaultAccess = "UPDATE systemsettings SET default_access = 1";  
            try
            {
               $pdo3->prepare("$updateDefaultAccess")->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }

      }
   }

   
   $removed_id_arr = array_diff($page_id_arr, $post_id_arr);
  
   $remove_id_str = implode(",", $removed_id_arr);

   if($remove_id_str == ''){
      $remove_id_str = -1;
   }

   // remove permissions

   $deletePageAccess  = "DELETE FROM admin_page_access WHERE page_id IN ($remove_id_str)";

      try
        {
           $pdo3->prepare("$deletePageAccess")->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }

   $_SESSION['successMessage'] = "Permission Changed successfully !";
   header("location:admin-access.php");

   die;
}

  pageStart('Page Access', NULL, "", "settings", "index", 'Page Access', $_SESSION['successMessage'], $_SESSION['errorMessage']);
  
  $userLvl = $_SESSION['userGroup'];
  $domain = $_SESSION['domain'];


  function update_page_access($page_id, $access_values){
      global $pdo3;

      // convert to string
      $access_level = implode(",", $access_values);

      // check for the page

      $checkPage_access = "SELECT * FROM admin_page_access WHERE page_id = '".$page_id."'";

      try
      {
        $page_result = $pdo3->prepare("$checkPage_access");
        $page_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }

      $page_count = $page_result->rowCount();

      if($page_count == 0){
          $insertPage_access = "INSERT INTO admin_page_access (page_id, access_level) VALUES ('".$page_id."', '".$access_level."')";  
            try
            {
               $pdo3->prepare("$insertPage_access")->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
      }else{
           $updatePage_access = "UPDATE admin_page_access SET access_level = '".$access_level."' WHERE page_id = '".$page_id."'";  
            try
            {
               $pdo3->prepare("$updatePage_access")->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user: ' . $e->getMessage();
                echo $error;
                exit();
            }
      }

  }


  ?>
<center>
<form id="registerForm" action="" method="POST">
<div class="actionbox-np2">
 <div class='mainboxheader'><img src="images/admin-dispensary.png" style="margin-bottom: -4px; margin-right: 10px;"/> <?php echo $lang['global-dispensary']; ?></div>
<div class="boxcontent">
 <table class='settingstable'>
  <?php
    $selectDispenses = "SELECT * FROM admin_page_details WHERE category = 'Dispensary'";

      try
      {
        $dispenses_result = $pdo2->prepare("$selectDispenses");
        $dispenses_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
      while($dispenses_row = $dispenses_result->fetch()){


        $dispense_name = str_replace(".php", "", $dispenses_row['page_link']);

        $dispense_id = $dispenses_row['id'];

        // fetch access level
        
        $dispense_access_level = "";

        if($dispense_id != ''){
          $getAccess = "SELECT access_level from admin_page_access WHERE page_id=".$dispense_id; 

            try
            {
              $dispenses_access_result = $pdo3->prepare("$getAccess");
              $dispenses_access_result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user 1: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $dipense_access_row = $dispenses_access_result->fetch();
            $dispense_access_level = explode(",", $dipense_access_row['access_level']);
          }

          $dispense_page_link = $dispenses_row['page_link'];
          $dispense_page_title = $dispenses_row['page_title'];

          if($dispense_page_title == 'Pre-orders'){
              if($setting3 == 0){
                $dispense_page_link  = 'pre-order-info.php';
              }else if($setting3 == 1){
                $dispense_page_link  = 'pre-order.php';
              }
          }


  
  ?>
  <tr>
   <td><a href="<?php echo $dispense_page_link; ?>"><?php echo $dispense_page_title; ?></a></td>
   <td class="left"><input type="checkbox" name="page_<?php echo $dispense_id ?>[]"  <?php if(in_array('2', $dispense_access_level)){  echo "checked"; } ?>  value="2"> Worker </td>
   <td class="left"><input type="checkbox" name="page_<?php echo $dispense_id ?>[]" <?php if(in_array('3', $dispense_access_level)){  echo "checked"; } ?> value="3"> Volunteer </td>
  </tr>
<?php }
 ?>
 </table>   
</div>
<br />
 <div class='mainboxheader'><img src="images/admin-bar.png" style="margin-bottom: -4px; margin-right: 10px;"/> Bar</div>
<div class="boxcontent">
 <table class='settingstable'>
    <?php
    $selectBar = "SELECT * FROM admin_page_details WHERE category = 'Bar'";

      try
      {
        $bar_result = $pdo2->prepare("$selectBar");
        $bar_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
      while($bar_row = $bar_result->fetch()){

        

        $bar_name = str_replace(".php", "", $bar_row['page_link']);
        $page_id = $bar_row['id'];

          // fetch access level
        
        $bar_access_level = "";

        if($page_id != ''){
          $getAccess = "SELECT access_level from admin_page_access WHERE page_id=".$page_id;

            try
            {
              $bar_access_result = $pdo3->prepare("$getAccess");
              $bar_access_result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user 2: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $bar_access_row = $bar_access_result->fetch();
            $bar_access_level = explode(",", $bar_access_row['access_level']);
          }


  ?>
  <tr>
   <td><a href="<?php echo $bar_row['page_link'] ?>"><?php echo $bar_row['page_title'] ?></a></td>
    <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('2', $bar_access_level)){  echo "checked"; } ?> value="2"> Worker </td>
   <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('3', $bar_access_level)){  echo "checked"; } ?> value="3"> Volunteer </td>
  </tr>
<?php
 } ?>
 </table>   
 </div>
</div>

<div class="actionbox-np2">
  <div class="mainboxheader"><img src="images/admin-users.png" style="margin-bottom: -4px; margin-right: 10px;"/> <?php echo $lang['global-members']; ?></div>
<div class="boxcontent">

 <table class='settingstable'>
    <?php
    $selectMembers = "SELECT * FROM admin_page_details WHERE category = 'Members'";

      try
      {
        $member_result = $pdo2->prepare("$selectMembers");
        $member_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
      while($member_row = $member_result->fetch()){


        $member_name = str_replace(".php", "", $member_row['page_link']);

        $page_id = $member_row['id'];


        // fetch access level
        
        $member_access_level = "";

        if($page_id != ''){
          $getAccess = "SELECT access_level from admin_page_access WHERE page_id=".$page_id;

            try
            {
              $member_access_result = $pdo3->prepare("$getAccess");
              $member_access_result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user 3: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $member_access_row = $member_access_result->fetch();
            $member_access_level = explode(",", $member_access_row['access_level']);
          }

          $member_page_link = $member_row['page_link'];
          $member_page_title = $member_row['page_title'];

          

          if($member_page_title == 'Pre-registered'){
              if($_SESSION['presignup'] == 0){
                $member_page_link  = 'pre-reg-info.php';
              }else if($_SESSION['presignup'] == 1){
                $member_page_link  = 'pre-reg.php';
              }
          }


          if($member_page_title == 'Appointments'){
              if($appointments == 0){
                $member_page_link  = 'appointments-info.php';
              }else if($appointments == 1){
                $member_page_link  = 'appointment.php';
              }
          }

  ?>
  <tr>
   <td><a href="<?php echo $member_page_link ?>"><?php echo $member_page_title; ?></a></td>
    <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('2', $member_access_level)){  echo "checked"; } ?> value="2"> Worker </td>
   <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]"  <?php if(in_array('3', $member_access_level)){  echo "checked"; } ?> value="3"> Volunteer </td>
  </tr>
<?php
    

 } ?>
 
 </table>   
  
</div>
<br />
<div class="boxcontent">
  <div class="mainboxheader"><img src="images/admin-products.png" style="margin-bottom: -4px; margin-right: 10px;" /> <?php echo $lang['global-products']; ?></div>
 <table class='settingstable'>
  <?php
    $selectProducts = "SELECT * FROM admin_page_details WHERE category = 'Products'";

      try
      {
        $product_result = $pdo2->prepare("$selectProducts");
        $product_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
      while($product_row = $product_result->fetch()){

        $product_name = str_replace(".php", "", $product_row['page_link']);

        $page_id = $product_row['id'];


        // fetch access level
        
        $product_access_level = "";

        if($page_id != ''){
          $getAccess = "SELECT access_level from admin_page_access WHERE page_id=".$page_id;

            try
            {
              $product_access_result = $pdo3->prepare("$getAccess");
              $product_access_result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user 4: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $product_access_row = $product_access_result->fetch();
            $product_access_level = explode(",", $product_access_row['access_level']);
          }



  ?>
  <tr>
   <td><a href="<?php echo $product_row['page_link'] ?>"><?php echo $product_row['page_title'] ?></a></td>
         <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('2', $product_access_level)){  echo "checked"; } ?> value="2"> Worker </td>
   <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('3', $product_access_level)){  echo "checked"; } ?> value="3"> Volunteer </td>
  </tr>
<?php 

}
 ?>
 </table>   
</div>

<div class="boxcontent">
   <div class="mainboxheader"><img src="images/admin-products.png" style="margin-bottom: -4px; margin-right: 10px;" /> <?php echo $lang['admin-clubadmin']; ?></div>
 <table class='settingstable'>
    <?php
    $selectAdmin = "SELECT * FROM admin_page_details WHERE category = 'Administration'";

      try
      {
        $admin_result = $pdo2->prepare("$selectAdmin");
        $admin_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
      while($admin_row = $admin_result->fetch()){


        $admin_name = str_replace(".php", "", $admin_row['page_link']);

        $page_id = $admin_row['id'];


 

        // fetch access level
        
        $admin_access_level = "";

        if($page_id != ''){
          $getAccess = "SELECT access_level from admin_page_access WHERE page_id=".$page_id;

            try
            {
              $admin_access_result = $pdo3->prepare("$getAccess");
              $admin_access_result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user 5: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $admin_access_row = $admin_access_result->fetch();
            $admin_access_level = explode(",", $admin_access_row['access_level']);
          }

          $open_close_arr = array("close-day-pre.php", "open-day-pre.php", "open-shift-pre.php", "close-shift-and-day-pre.php", "close-shift-pre.php");


if(in_array($admin_row['page_link'], $open_close_arr)){

    if ($_SESSION['openAndClose'] == 2) {
    
      $admin_link = "<tr><td><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></td></tr>";
      
  } else if ($_SESSION['openAndClose'] == 3) {
    
      $admin_link = "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
          <tr><td><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></td></tr>";
            
  } else if ($_SESSION['openAndClose'] == 4) {
    
      // Find last opening
      $query = "SELECT openingtime FROM opening ORDER BY openingtime DESC LIMIT 1";
      try
      {
        $result = $pdo3->prepare("$query");
        $result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
    
      $row = $result->fetch();
        $openingtime = $row['openingtime'];
              
      // Find last shiftclose after opening
      $query = "SELECT closingid FROM shiftclose WHERE closingtime > '$openingtime'";
      try
      {
        $result = $pdo3->prepare("$query");
        $result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
    
      $row = $result->fetch();
        $closingid = $row['closingid'];
      
      // Check if that shift has been closed or not (maybe just reception was closed for example)
      $query = "SELECT shiftClosed FROM opening WHERE shiftClosedNo = '$closingid'";
      try
      {
        $result = $pdo3->prepare("$query");
        $result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
    
      $row = $result->fetch();
        $shiftClosed = $row['shiftClosed'];
        
      // Show links accordingly
      if ($shiftClosed == 2) {
        
          $admin_link = "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
                <tr><td><a href='open-shift-pre.php'>{$lang['start-shift']}</a></td></tr>
            <tr><td><a href='close-shift-and-day-pre.php'>{$lang['close-shift-and-day']}</a></td></tr>";
          
      } else {
        
          $admin_link =  "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
              <tr><td><a href='close-shift-pre.php'>{$lang['close-shift']}</a></td></tr>
                <tr><td><a href='open-shift-pre.php'>{$lang['start-shift']}</a></td></tr>
            <tr><td><a href='close-shift-and-day-pre.php'>{$lang['close-shift-and-day']}</a></td></tr>";
          
      }
            
    } 
        //echo $admin_link;
  }else{  
  ?>
  <tr>
   <td><a href="<?php echo $admin_row['page_link']; ?>"><?php echo $admin_row['page_title'] ?></a></td>
         <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('2', $admin_access_level)){  echo "checked"; } ?> value="2"> Worker </td>
   <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('3', $admin_access_level)){  echo "checked"; } ?> value="3"> Volunteer </td>
  </tr>
 
  <?php }
  }

   ?>
 
 </table>   
</div>


<div class="boxcontent">
     <div class="mainboxheader"><img src="images/admin-reports.png" style="margin-bottom: -4px; margin-right: 10px;" /> <?php echo $lang['reports']; ?></div>
 <table class='settingstable'>
      <?php
    $selectReports = "SELECT * FROM admin_page_details WHERE category = 'Reports'";

      try
      {
        $reports_result = $pdo2->prepare("$selectReports");
        $reports_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
      while($reports_row = $reports_result->fetch()){


        $report_name = str_replace(".php", "", $reports_row['page_link']);

        $page_id = $reports_row['id'];



        // fetch access level
        
        $reports_access_level = "";

        if($page_id != ''){
          $getAccess = "SELECT access_level from admin_page_access WHERE page_id=".$page_id;

            try
            {
              $reports_access_result = $pdo3->prepare("$getAccess");
              $reports_access_result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user 6: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $reports_access_row = $reports_access_result->fetch();
            $reports_access_level = explode(",", $reports_access_row['access_level']);
          }

  
  ?>
  <tr>
   <td><a href="<?php echo $reports_row['page_link'] ?>"><?php echo $reports_row['page_title'] ?></a></td>
         <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('2', $reports_access_level)){  echo "checked"; } ?> value="2"> Worker </td>
   <td class="left"><input type="checkbox" name="page_<?php echo $page_id ?>[]" <?php if(in_array('3', $reports_access_level)){  echo "checked"; } ?> value="3"> Volunteer </td>
  </tr>
<?php }

 ?>
 </table>   
</div>


</div>
<br>
<button class="cta1" name="oneClick" type="submit">Save changes</button>
</form>

<!-- <script type="text/javascript">
  $(document).ready(function(){
      $("input[type='checkbox']").change(function(){
          $("#registerForm").submit();
      });
  });
</script> -->

<?php 
displayFooter();
?>
