<?php
//Created by Konstant for Task-14929108 on 07/09/2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	
	if(!isset($_SESSION['change_pass'])){
		header("Location: index.php");
		exit();
	}

	// Did this page resubmit with a form?
	if ($_POST['pwdaction'] == 'yes') {

			$email = $_POST['email'];
			$oldPassword = $_POST['old_pass'];
			$newPassword = $_POST['new_pass'];
			

			// Check if email exists
			try
			{
				$result = $pdo3->prepare("SELECT user_id, email, userPass FROM users WHERE email = :email");
				$result->bindValue(':email', $email);
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

			// Email doesn't exist
			if (!$data) {

				$_SESSION['errorMessage'] = "E-mail doesn't exist / E-mail no existe!";
				header("Location:change-password.php");
				exit();

			// E-mail exists, let's check password
			} else {

				try
				{
					$result = $pdo3->prepare("SELECT * FROM users WHERE email = :email AND userPass = :userPass");
					$result->bindValue(':email', $email);
					$result->bindValue(':userPass', crypt($oldPassword, $email));
					$result->execute();
					$data = $result->fetchAll();
					
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				// Wrong pwd
				if (!$data) {

					$_SESSION['errorMessage'] = "Incorrect old password / Contrase&ntilde;a erronea";
					header("Location:change-password.php");
					exit();

				// Correct pwd
				}else{
					// check last five passwords of user
					$row = $data[0];

					$selectPasswords = "SELECT newUserPass FROM password_changes WHERE user_id =".$row['user_id']." ORDER BY id DESC limit 5";
					try
					{
						$result_pass = $pdo3->prepare("$selectPasswords");
						$result_pass->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$pass_arr = []; 
					while($pass_row = $result_pass->fetch()){
						$pass_arr[] = $pass_row['newUserPass'];
					}

					$newPass_hash = crypt($newPassword, $email);

					if(in_array($newPass_hash, $pass_arr)){
						$_SESSION['errorMessage'] = "This password is already used, please create new password!";
						header("Location:change-password.php");
						exit();
					}
					
					// Edit user here. Save their new password. Their usergoup has already been updated
					$updateUser = sprintf("UPDATE users SET userPass = '%s' WHERE user_id = '%d';",
							$newPass_hash,
							$row['user_id']
						);

					try
					{
						$result = $pdo3->prepare("$updateUser")->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}

					// insert password chnage
					$changeTime = date("Y-m-d H:i:s");
					$insertChangePassword = $pdo3->prepare("INSERT INTO password_changes (user_id, newUserPass, changed_at) VALUES (?,?,?)");
			        $insertChangePassword->bindValue(1, $row['user_id']);
			        $insertChangePassword->bindValue(2, $newPass_hash);
			        $insertChangePassword->bindValue(3, $changeTime);
			        $insertChangePassword->execute();
						
					// On success: redirect.
					$_SESSION['user_id'] = $row['user_id'];
					$_SESSION['username'] = $row['email'];
					$_SESSION['memberno'] = $row['memberno'];
					$_SESSION['first_name'] = $row['first_name'];
					$_SESSION['userGroup'] = $row['userGroup'];
					$_SESSION['workStationAccess'] = $row['workStation'];
					$_SESSION['domain'] = $row['domain'];
					$_SESSION['cloud'] = 'nefos';
					unset($_SESSION['change_pass']);
					$_SESSION['successMessage'] = $lang['index-loggedin'];
					header("Location: main.php");
					exit();
				}
			}

	}
	
  $validationScript = <<<EOD
    $(document).ready(function() {
      $("#registerForm").validate({
           ignore: "",
           rules: {
             email:{
                email: true
             },
             description: {
                required: true
             }
           },
          messages:{
              
          },
            errorPlacement: function(error, element) {
                if ( element.is(":radio") || element.is(":checkbox") || element.is("textarea")){
                     error.appendTo(element.next());
                } else {
                    return true;
                }
            },

        });

  }); // end ready
EOD;
		pageStart("Change Password", NULL, $validationScript, "pdebt", "index", "Change Password", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

?><br /><br />
<center>

	<form id="registerForm" action="" method="POST">
	    <input type="hidden" name="pwdaction" value="yes">
	    <div id="mainbox-no-width">
		     <div id="mainboxheader">
		      Change Paasword 
		     </div>
		    <div class='boxcontent'>
	            <table class="padded">
	              <tr>
	               <td>Email:</td>
	               <td>
	                <input type="email" name="email" class='defaultinput' required />
	               </td>
	              </tr>		              
	              <tr>
	               <td>Old Password:</td>
	               <td>
	                <input type="password" name="old_pass" class='defaultinput' required />
	               </td>
	              </tr>		              
	              <tr>
	               <td>New Password:</td>
	               <td>
	                <input type="password" name="new_pass" class='defaultinput' required />
	               </td>
	              </tr>
	             </table>
		     </div>
	    </div>
        <br />
        <button type="submit" name="change_password" class='cta1'>Submit</button>
	</form>

</center>
<?php

 displayFooter();


?>
