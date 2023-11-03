<?php
	
	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/view-newclub.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	if(!isset($_SESSION['step2'])){
     $_SESSION['errorMessage'] = "Please complete the step 2 first !";
     header("location:new-club-2.php");
     die;
  }
$validationScript = <<<EOD
    $(document).ready(function() {
      $("#step3").validate({
           rules: {
              club_email:{
                  email:true
                }
           },
          
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            },

        });

  }); // end ready
EOD;
	pageStart("CCS", NULL, $validationScript, "pprofile", "club-launch", "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
   $updatee_id = $_SESSION['temp_id'];
   // get officiial address details
   $selectOfficial = "SELECT * from temp_customers WHERE id=".$updatee_id;
          try
          {
            $offc_result = $pdo2->prepare("$selectOfficial");
            $offc_result->execute();
          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
   $offcRow = $offc_result->fetch();
    $official_street_name = $offcRow['official_street_name'];
    $official_street_number = $offcRow['official_street_number'];
    $official_local = $offcRow['official_local'];
    $official_postcode = $offcRow['official_postcode'];
    $official_city = $offcRow['official_city'];
    $official_province = $offcRow['official_province'];
    $official_country = $offcRow['official_country'];
  // submit step 3
  if(isset($_POST)  && !empty($_POST)){
    $_SESSION['step3'] = 1;
    $club_telephone = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_telephone'])));      
    $club_email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_email'])));      
    $website = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['website'])));      
    $facebook = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['facebook'])));      
    $instagram = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['instagram'])));      
    $club_offc_street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_offc_street'])));      
    $club_offc_streetNo = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_offc_streetNo'])));      
    $club_offc_local = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_offc_local'])));      
    $club_offc_postcode = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_offc_postcode'])));      
    $club_offc_city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_offc_city'])));      
    $club_offc_province = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_offc_province'])));      
    $club_offc_country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_offc_country'])));  

    $official_address   = $_POST['official_address'];
    $_SESSION['official_address'] = $official_address;  
    // Query to update user - 28 arguments
    $updateTempCustomer = "UPDATE  temp_customers SET club_telephone = '$club_telephone', club_email = '$club_email',club_website = '$website',club_facebook = '$facebook',club_instagram = '$instagram',location_street_name = '$club_offc_street',location_street_number = '$club_offc_streetNo',location_local = '$club_offc_local',location_postcode = '$club_offc_postcode', location_city = '$club_offc_city', location_province = '$club_offc_province', location_country = '$club_offc_country' WHERE id='$updatee_id'";
           
          try
          {
            $update_result = $pdo2->prepare("$updateTempCustomer")->execute();
          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
     
      if($_GET['edit'] == 'preview'){
        $_SESSION['successMessage']  = "Step 3 updated successfully !";
        header("location:new-club-5.php");
        die;
      }else{
         $_SESSION['club_step'] = "step4";
        header("location:new-club-4.php");
        die;
      }
  }

    // edit preview 
  if(isset($_GET['edit']) && $_GET['edit'] == 'preview'){
      $update_id = $_SESSION['temp_id'];
      $selectDetails = "SELECT * from temp_customers WHERE id=".$update_id; 
          try
          {
            $offc_result = $pdo2->prepare("$selectDetails");
            $offc_result->execute();
          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
           while($clubRow = $offc_result->fetch()){
              $club_telephone = $clubRow['club_telephone'];
              $club_email = $clubRow['club_email'];
              $club_website = $clubRow['club_website'];
              $club_facebook = $clubRow['club_facebook'];
              $club_instagram = $clubRow['club_instagram'];
              $location_street_name = $clubRow['location_street_name'];
              $location_street_number = $clubRow['location_street_number'];
              $location_local = $clubRow['location_local'];
              $location_postcode = $clubRow['location_postcode'];
              $location_city = $clubRow['location_city'];
              $location_province = $clubRow['location_province'];
              $location_country = $clubRow['location_country'];
           }
  }
?>

<div id='progress'>
 <div id='progressinside3'>
 </div>
</div>
<br />
<div id='progresstext1'>
 1. Personal
</div>
<div id='progresstext2'>
 2. Club details
</div>
<div id='progresstext3'>
 3. Contact details
</div>
<form id="step3" action="" method="POST">
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    Please fill in contact details for your club
   </center>
  </div>
  <div class='boxcontent'>
   <center>
    <table>
     <tr>
      <td>Club telephone</td>
      <td><input type="text" name="club_telephone" class='defaultinput tenDigit' value="<?php echo $club_telephone ?>" placeholder="" /></td>
     </tr>
     <tr>
      <td>Club e-mail</td>
      <td><input type="email" name="club_email" class='defaultinput tenDigit' value="<?php echo $club_email ?>" placeholder="" /></td>
     </tr>
     <tr>
      <td>Club website</td>
      <td><input type="text" name="website" class='defaultinput tenDigit' value="<?php echo $club_website ?>" placeholder="" /></td>
     </tr>
     <tr>
      <td>Club Facebook</td>
      <td><input type="text" name="facebook" class='defaultinput tenDigit' value="<?php echo $club_facebook ?>" placeholder="" /></td>
     </tr>
     <tr>
      <td>Club Instagram</td>
      <td><input type="text" name="instagram" class='defaultinput tenDigit' value="<?php echo $club_instagram ?>" placeholder="" /></td>
     </tr>
     <tr>
      <td><br />Club location *</td>
      <td><br />
       <div class='fakeboxholder firstbox' style='margin-left: 12px;'>	
	    <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	     Same as official address
	     <input type="checkbox" name="official_address" value='yes' <?php if($_SESSION['official_address'] == 'yes'){ echo 'checked'; } ?>/>
	      <div class="fakebox"></div>
	    </label>
	   </div>
	   <br />
       <input type="text" id="street" name="club_offc_street" class='defaultinput eightDigit' value="<?php echo $location_street_name ?>" placeholder="Street name" required="" />
       <input type="text" id="street_no" name="club_offc_streetNo" class='defaultinput fiveDigit' value="<?php echo $location_street_number ?>" placeholder="No." required=""/>
       <input type="text" id="local" name="club_offc_local" class='defaultinput fiveDigit' value="<?php echo $location_local ?>" placeholder="Local" required=""/><br />
       <input type="text" id="postcode" name="club_offc_postcode" class='defaultinput fiveDigit' value="<?php echo $location_postcode ?>" placeholder="Postcode" required=""/>
       <input type="text" id="city" name="club_offc_city" class='defaultinput elevenDigit' value="<?php echo $location_city ?>" placeholder="City" required=""/><br />
       <input type="text" id="province" name="club_offc_province" class='defaultinput twelveDigit' value="<?php echo $location_province ?>" placeholder="Province" required="" /><br />
       <input type="text" id="country" name="club_offc_country" class='defaultinput twelveDigit' value="<?php echo $location_country ?>" placeholder="Country" required=""/><br />
      </td>
     </tr>
    </table>
   </center>
  </div>
 </div>
</div>
<center><button type="submit" name='step3_sub' class='cta1'>Continue</button></center>
</form>
<script type="text/javascript">
  $(document).ready(function(){
      $("input[name='official_address']").change(function(){
          var checked_val = $("input[name='official_address']:checked").val();
          if(checked_val == 'yes'){
            $("#street").val("<?php echo $official_street_name ?>");
            $("#street_no").val("<?php echo $official_street_number ?>");
            $("#local").val("<?php echo $official_local ?>");
            $("#postcode").val("<?php echo $official_postcode ?>");
            $("#city").val("<?php echo $official_city ?>");
            $("#province").val("<?php echo $official_province ?>");
            $("#country").val("<?php echo $official_country ?>");
          }else{
            $("#street").val("");
            $("#street_no").val("");
            $("#local").val("");
            $("#postcode").val("");
            $("#city").val("");
            $("#province").val("");
            $("#country").val("");
          }
      });
  });
</script>
<?php
 displayFooter();