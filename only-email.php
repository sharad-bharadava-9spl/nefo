<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['submitted'])) {
		
		$notFirst = 'true';
		
		$cashBox1 = $_POST['cashBox1'];
		$cashBox2 = $_POST['cashBox2'];
		$cashBox3 = $_POST['cashBox3'];
		$cashBox4 = $_POST['cashBox4'];
		$cashBox5 = $_POST['cashBox5'];
		$cashBox6 = $_POST['cashBox6'];
		$cashBox7 = $_POST['cashBox7'];
		$cashBox9 = $_POST['cashBox9'];
		
		if ($cashBox1 == 1) {
			$userGroups .= "1,";
		}
		if ($cashBox2 == 1) {
			$userGroups .= "2,";
		}
		if ($cashBox3 == 1) {
			$userGroups .= "3,";
		}
		if ($cashBox4 == 1) {
			$userGroups .= "4,";
		}
		if ($cashBox5 == 1) {
			$userGroups .= "5,";
		}
		if ($cashBox6 == 1) {
			$userGroups .= "6,";
		}
		if ($cashBox7 == 1) {
			$userGroups .= "7,";
		}
		if ($cashBox9 == 1) {
			$userGroups .= "9,";
		}
		
		$userGroups = substr($userGroups, 0, -1);
		
		
	} else { 
		$userGroups = "1,2,3,4,5,6,7,9";
		
	}
	
	    
	// Query to look up users
	$selectUsers = "SELECT email FROM users WHERE email <> '' AND email LIKE ('%@%') AND email LIKE ('%.%') AND userGroup IN ($userGroups)";
	try
	{
		$result = $pdo3->prepare("$selectUsers");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	
	pageStart($lang['email-list'], NULL, $deleteDonationScript, "pprofile", "statutes", $lang['email-list'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>	  

<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent' style='padding-bottom: 0; text-align: left;'>
        <form action='' method='POST'>
        
<?php    if ($notFirst != 'true') { ?>

	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Administrator
	  <input type="checkbox" name="cashBox1" id="accept1" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Staff
	  <input type="checkbox" name="cashBox2" id="accept2" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Volunteer
	  <input type="checkbox" name="cashBox3" id="accept3" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Professional contact
	  <input type="checkbox" name="cashBox4" id="accept4" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Member
	  <input type="checkbox" name="cashBox5" id="accept5" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Visitor
	  <input type="checkbox" name="cashBox6" id="accept6" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Banned
	  <input type="checkbox" name="cashBox7" id="accept7" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inactive
	  <input type="checkbox" name="cashBox9" id="accept9" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
<?php  } else { ?>

	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Administrator
	  <input type="checkbox" name="cashBox1" id="accept1" value='1' <?php if ($cashBox1 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Staff
	  <input type="checkbox" name="cashBox2" id="accept2" value='1' <?php if ($cashBox2 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Volunteer
	  <input type="checkbox" name="cashBox3" id="accept3" value='1' <?php if ($cashBox3 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Professional contact
	  <input type="checkbox" name="cashBox4" id="accept4" value='1' <?php if ($cashBox4 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Member
	  <input type="checkbox" name="cashBox5" id="accept5" value='1' <?php if ($cashBox5 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Visitor
	  <input type="checkbox" name="cashBox6" id="accept6" value='1' <?php if ($cashBox6 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Banned
	  <input type="checkbox" name="cashBox7" id="accept7" value='1' <?php if ($cashBox7 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inactive
	  <input type="checkbox" name="cashBox9" id="accept9" value='1' <?php if ($cashBox9 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
<?php  } ?>
	<br />
	<br />

        </div>
       </td>
      </tr>
     </table>
 	<input type="hidden" name="submitted" value="1">
	<button class="cta1" type="submit">OK</button>
	

	

        </form>
   </div>
   </center>
	<br />
	<br />
<center>	  
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['email-list']; ?>
 </div>
 <div class='boxcontent' style='text-align: left;'>
	  <?php

	while ($user = $result->fetch()) {

		$user_row .=	sprintf("
  	  %s, ",
	  $user['email']
	  );
  	}
  	
	$user_row = substr($user_row, 0, -2);
	echo $user_row;
	
?>

   </div>
	 </center>
	 
<?php  displayFooter(); ?>
