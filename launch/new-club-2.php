<?php
	
	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/view-newclub.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
//ini_set("display_errors", "on");

$validationScript = <<<EOD
    $(document).ready(function() {
      $.validator.addMethod("noSpace", function(value, element) { 
        return value.indexOf(" ") < 0 && value != ""; 
      }, "No space please and don't leave it empty");

      $.validator.addMethod("lowercase", function(value, element) {
        return /^[a-z]+$/.test(value);
      }, "Please enter valid letters only"); 

      $("#step2").validate({
           rules: {
            longName: {
                required: true,
              },
              shortName:{
                required: true,
              }

           }, 
           messages:
             {
                 longName:
                 {
                    remote: $.validator.format("{0} is already taken.")
                 },                 
             }, 
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            },

        });

  }); // end ready
EOD;
	pageStart("CCS", NULL, $validationScript, "pprofile", "club-launch", "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

  // check if already exist
/*function generate_clubName($source, $coloumn){ 
    global $pdo2;
    $query = "SELECT COUNT(id) FROM customers WHERE $coloumn = '".$source."'"; 
     try
      {
        $get_result = $pdo2->prepare("$query");
        $get_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching club: ' . $e->getMessage();
          echo $error;
          exit();
      }
      $row = $get_result->fetch();
      $clubcount = $row['COUNT(id)'];
   if($clubcount > 0){   
        //echo "SELECT $coloumn FROM customers WHERE $coloumn like '".$source."%' order by id desc limit 1"; die;
          $result = $pdo2->prepare("SELECT $coloumn FROM customers WHERE $coloumn like '".$source."%' order by id desc limit 1"); 
         try
        {
          $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching club: ' . $e->getMessage();
            echo $error;
            exit();
        }
      $row = $result->fetch();
        $c_name = $row[$coloumn]; 
           preg_match('/[0-9]/', $c_name, $n);
         if(isset($n[0])){
           $cnumpos = strpos($c_name, $n[0]); 
           $cname = substr($c_name, 0, $cnumpos); 
           $cnumber = substr($c_name, $cnumpos); 
           $club_name = $cname.($cnumber+1);
         }else{
            $club_name = $c_name."1";
         }
      }else{
        $club_name = $source;
      }
  return $club_name;
}*/

  //--------check the email verification status here--------------

  try
		{
      $id = $_SESSION['temp_id'];
			$selectDetails = "SELECT * from temp_customers WHERE id=".$id; 
      $user_result = $pdo2->prepare("$selectDetails");
      $user_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching club: ' . $e->getMessage();
				echo $error;
				exit();
    }

    $status = $user_result->fetch()['nts_email_verify'];
    if($status==0){
      ?>
      <div id='mainboxheader'>
        <center>
        <?php if ($_SESSION['lang'] == 'es') { echo "Verifique su dirección de correo electrónico para continuar ..."; } else { echo "Please verify you email address to continue..."; } ?>
        </center>
      </div>
      <?php
    }else{


    // submit step 2
    if(isset($_POST) && !empty($_POST)){
      $updatee_id = $_SESSION['temp_id'];
      $_SESSION['step2'] = 1;
      $longName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['longName'])));   
    // $longName = generate_clubName($club_name, 'longName');   
      $shortName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['shortName']))); 
      //$shortName = generate_clubName($club_short_name, 'shortName');  
            
      $cif = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['cif'])));      
      $club_street_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_street_name'])));      
      $club_street_no = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_street_no'])));      
      $club_local = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_local'])));      
      $club_postcode = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_postcode'])));      
      $club_city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_city'])));      
      $club_province = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_province'])));      
      $club_country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club_country'])));      
      // Query to update user - 28 arguments
      $updateTempCustomer = "UPDATE  temp_customers SET official_club = '$longName', common_club = '$shortName',cif = '$cif',official_street_name = '$club_street_name',official_street_number = '$club_street_no',official_local = '$club_local',official_postcode = '$club_postcode',official_city = '$club_city',official_province = '$club_province',official_country = '$club_country' WHERE id='$updatee_id'";
            
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
      $_SESSION['club_step'] = "step3";
      if($_GET['edit'] == 'preview'){
        $_SESSION['successMessage']  = "Step 2 updated successfully !";
        header("location:new-club-5.php");
        die;
      }else{
        header("location:new-club-3.php");
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
                  $official_club = $clubRow['official_club'];
                  $common_club = $clubRow['common_club'];
                  $cif = $clubRow['cif'];
                  $official_street_name = $clubRow['official_street_name'];
                  $official_street_number = $clubRow['official_street_number'];
                  $official_local = $clubRow['official_local'];
                  $official_postcode = $clubRow['official_postcode'];
                  $official_city = $clubRow['official_city'];
                  $official_province = $clubRow['official_province'];
                  $official_country = $clubRow['official_country'];
            }
    }
  ?>

  <div id='progress'>
  <div id='progressinside2'>
  </div>
  </div>
  <br />
  <div id='progresstext1'>
  1. Personal
  </div>
  <div id='progresstext2'>
  2. Club details
  </div>
  <form id="step2" action="" method="POST">
  <div id='mainbox-new-club'>
    <div id='mainboxheader'>
    <center>
      Please fill in your club details
    </center>
    </div>
    <div class='boxcontent'>
    <center>
      <table>
      <tr>
        <td>Official licensed club name *</td>
        <td><input type="text"  name="longName" id="offcial_club" class='defaultinput twelveDigit' value="<?php echo $official_club ?>" placeholder="" required="" /></td>
      </tr>
      <tr>
        <td>Commonly used club name *</td>
        <td><input type="text" id="common_club" name="shortName" class='defaultinput twelveDigit' value="<?php echo $common_club ?>" placeholder="" required=""/></td>
      </tr>
      <tr>
        <td>Club CIF</td>
        <td><input type="text" name="cif" class='defaultinput twelveDigit' placeholder="" value="<?php echo $cif; ?>" /></td>
      </tr>
      <tr>
        <td>Official licensed club address *</td>
        <td>
        <input type="text" name="club_street_name" class='defaultinput eightDigit' placeholder="Street name" value="<?php echo $official_street_name ?>" required=""/>
        <input type="text" name="club_street_no" class='defaultinput fiveDigit' placeholder="No." value="<?php echo $official_street_number ?>" required=""/>
        <input type="text" name="club_local" class='defaultinput fiveDigit' placeholder="Local" value="<?php echo $official_local ?>" required=""/><br />
        <input type="text" name="club_postcode" class='defaultinput fiveDigit' placeholder="Postcode" value="<?php echo $official_postcode ?>" required=""/>
        <input type="text" name="club_city" class='defaultinput elevenDigit' placeholder="City" value="<?php echo $official_city ?>" required=""/><br />
        <input type="text" name="club_province" class='defaultinput twelveDigit' placeholder="Province" value="<?php echo $official_province ?>" required=""/><br />
        <input type="text" name="club_country" class='defaultinput twelveDigit' placeholder="Country" value="<?php echo $official_country ?>" required=""/><br />
        </td>
      </tr>
      </table>
    </center>
    </div>
  </div>
  </div>
  <center><button type="submit" name="step2_sub" class='cta1'>Continue</button></center>
  </form>

<?php
  }
 displayFooter(); 
 ?>