<?php

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
    
?>
<center>
<div class="adminbox2">
<div class="adminbox">
 <center><img src="images/admin-dispensary.png" /></center>
 <h3><?php echo $lang['global-dispensary']; ?></h3>
 <table class='admintable'>
  <?php
   
    $selectDispenses = "SELECT * FROM admin_page_details WHERE category = 'Dispensary' AND admin_menu = 1";

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

        $dispense_access_level = [];

        $dispense_id = $dispenses_row['id'];

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

        if(in_array($userLvl, $dispense_access_level) || $userLvl == 1){

          $page_link = $dispenses_row['page_link'];

          if($dispenses_row['page_title'] == 'Pre-orders'){

              if ($setting3 == 0) {
                $page_link = 'pre-order-info.php';
              }else{
                  $page_link = 'pre-order.php';
              }

          }
  ?>
  <tr>
   <td><a href="<?php echo $page_link; ?>"><?php echo $dispenses_row['page_title']; ?></a></td>
  </tr>
<?php }
  }
 ?>
 </table>   
</div>
<br />
<div class="adminbox">
 <center><img src="images/admin-bar.png" /></center>
 <h3>Bar</h3>
 <table class='admintable'>
  <?php
    $selectBar = "SELECT * FROM admin_page_details WHERE category = 'Bar' AND admin_menu = 1";

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


        $page_id = $bar_row['id'];

          // fetch access level
        
        $bar_access_level = [];

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


        if(in_array($userLvl, $bar_access_level) || $userLvl == 1){

  ?>
  <tr>
   <td><a href="<?php echo $bar_row['page_link'] ?>"><?php echo $bar_row['page_title']; ?></a></td>
  </tr>
<?php }

}
?>
 </table>   
 </div>
</div>

<div class="adminbox">
 <center><img src="images/admin-users.png" /></center>
 <h3><?php echo $lang['global-members']; ?></h3>
 <table class='admintable'>
   <?php
    $selectMembers = "SELECT * FROM admin_page_details WHERE category = 'Members' AND admin_menu = 1";

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

     

      while($access_link  = $member_result->fetch()){

          $member_access_level = explode(",", $access_link['access_level']);

        $page_id = $access_link['id'];

        // fetch access level
        
        $member_access_level = [];

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


        if(in_array($userLvl, $member_access_level) || $userLvl == 1){
            $member_page_link = $access_link['page_link'];


            if($access_link['page_title'] == 'Pre-registered'){
                if($_SESSION['presignup'] == 0){
                  $member_page_link = 'pre-reg-info.php';
                }else{
                  $member_page_link = 'pre-reg.php';
                }

            }


            if($access_link['page_title'] == 'Appointments'){
                if($appointments == 0){
                  $member_page_link = 'appointments-info.php';
                }else{
                  $member_page_link = 'appointments.php';
                }

            }

    ?>
    <tr>
     <td><a href="<?php echo $member_page_link; ?>"><?php echo $access_link['page_title']; ?></a></td>
    </tr>
  <?php }
    }
   ?>
 </table>   
</div>

<div class="adminbox">
 <center><img src="images/admin-products.png" /></center>
 <h3><?php echo $lang['global-products']; ?></h3>
 <table class='admintable'>
   <?php
    $selectProducts = "SELECT * FROM admin_page_details WHERE category = 'Products' AND admin_menu = 1";

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

        $product_access_level = explode(",", $product_row['access_level']);

                // fetch access level
        $page_id = $product_row['id'];
        $product_access_level = [];

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

        if(in_array($userLvl, $product_access_level) || $userLvl == 1){

  ?>
  <tr>
   <td><a href="<?php echo $product_row['page_link'] ?>"><?php echo $product_row['page_title']; ?></a></td>
  </tr>

<?php }
  }
 ?>
 </table>   
</div>


<div class="adminbox">
 <center><img src="images/admin-admin.png" /></center>
 <h3><?php echo $lang['admin-clubadmin']; ?></h3>
 <table class='admintable'>
  <table class='admintable'>
      <?php
    $selectAdmin = "SELECT * FROM admin_page_details WHERE category = 'Administration' AND admin_menu = 1";

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

     // $admin_link_arr = $admin_result->fetchAll();
    
      $open_close_arr = [];
      
      if ($_SESSION['openAndClose'] == 2) {

            $open_close_arr[0]['page_title'] = $lang['admin-closeday'];
            $open_close_arr[0]['page_link'] = "close-day-pre.php";

          //echo "<tr><td><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></td></tr>";
          
      } else if ($_SESSION['openAndClose'] == 3) {
        
         /* echo "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
              <tr><td><a href='close-day-pre.php'>{$lang['admin-closeday']}</a></td></tr>";*/

              $open_close_arr[0]['page_title'] = $lang['admin-openday'];
              $open_close_arr[0]['page_link'] = "open-day-pre.php";
              $open_close_arr[1]['page_title'] = $lang['admin-closeday'];
              $open_close_arr[1]['page_link'] = "close-day-pre.php";
                
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
          
            /*echo "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
                  <tr><td><a href='open-shift-pre.php'>{$lang['start-shift']}</a></td></tr>
              <tr><td><a href='close-shift-and-day-pre.php'>{$lang['close-shift-and-day']}</a></td></tr>";*/

              $open_close_arr[0]['page_title'] = $lang['admin-openday'];
              $open_close_arr[0]['page_link'] = "open-day-pre.php";
              $open_close_arr[1]['page_title'] = $lang['start-shift'];
              $open_close_arr[1]['page_link'] = "open-shift-pre.php";
              $open_close_arr[2]['page_title'] = $lang['close-shift-and-day'];
              $open_close_arr[2]['page_link'] = "close-shift-and-day-pre.php";
            
        } else {
          
            /*echo "<tr><td><a href='open-day-pre.php'>{$lang['admin-openday']}</a></td></tr>
                <tr><td><a href='close-shift-pre.php'>{$lang['close-shift']}</a></td></tr>
                  <tr><td><a href='open-shift-pre.php'>{$lang['start-shift']}</a></td></tr>
              <tr><td><a href='close-shift-and-day-pre.php'>{$lang['close-shift-and-day']}</a></td></tr>";*/

              $open_close_arr[0]['page_title'] = $lang['admin-openday'];
              $open_close_arr[0]['page_link'] = "open-day-pre.php";
              $open_close_arr[1]['page_title'] = $lang['close-shift'];
              $open_close_arr[1]['page_link'] = "close-shift-pre.php";
              $open_close_arr[2]['page_title'] = $lang['start-shift'];
              $open_close_arr[2]['page_link'] = "open-shift-pre.php";
              $open_close_arr[3]['page_title'] = $lang['close-shift-and-day'];
              $open_close_arr[3]['page_link'] = "close-shift-and-day-pre.php";
            
        }
        
                
      }   


      while($admin_row = $admin_result->fetch()){

        // fetch access level
        $page_id = $admin_row['id'];
        $admin_access_level = [];

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

       
         if(in_array($userLvl, $admin_access_level) || $userLvl == 1){
  ?>
  <tr>
   <td><a href="<?php echo $admin_row['page_link'] ?>"><?php echo $admin_row['page_title']; ?></a></td>
  </tr>
<?php }
}
 ?>
 <?php 
      if($userLvl == 1){
        ?>
        <tr>
          <td><a href="admin-access.php">Pages Access Settings</a></td>
        </tr>
  <?php    }
  ?>
 </table>   
</div>
<div class="adminbox">
 <center><img src="images/admin-reports.png" /></center>
 <h3><?php echo $lang['reports']; ?></h3>
 <table class='admintable'>
        <?php
    $selectReports = "SELECT * FROM admin_page_details WHERE category = 'Reports' AND admin_menu = 1";

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

        $page_id = $reports_row['id'];

                // fetch access level
        
        $reports_access_level = [];

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

if(in_array($userLvl, $reports_access_level) || $userLvl == 1){

    $page_link = $reports_row['page_link'];

    if($_SESSION['workertracking'] == 1 && $page_link == 'worker-module.php'){
        $page_link = 'worker-module.php?user_id='.$_SESSION['user_id'];
    }
  ?>
  <tr>
   <td><a href="<?php echo $page_link; ?>"><?php echo $reports_row['page_title']; ?></a></td>
  </tr>
<?php }

  }
   ?>

 </table>   
</div>


</div>


<style>
form {
	display: inline;
	
	cursor: pointer;
}
button {
	border-radius: 3px;
	background-color: white;
	padding: 10px;
	text-align: center;
	line-height: 1.4em;
	border: 3px solid #8f006e;
	color: #a80082;
	font-size: 15px;
	padding-bottom: 5px;
	height: 100px !important;
	font-weight: 800;
	cursor: pointer;
}

</style>
<div class="adminbox2">
<form id="highroller" action="/HighRoller/index.php" method="POST">
<input type="hidden" name="domain" value="<?php echo $domain; ?>" />
<button type="submit">
<center><img src="images/shop-icon.png" /></center>
HIGH ROLLER
</button>
</form><br />
<form id="highroller" action="/CCS/index.php" method="POST">
<input type="hidden" name="domain" value="<?php echo $domain; ?>" />
<button type="submit">
<center><img src="images/shop-icon.png" /></center>
TIENDA CCS
</button>
</form>
</div>
</center>

