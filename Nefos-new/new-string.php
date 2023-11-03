<?php
	// created by konstant for task-15060600 on 23-03-2022	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

	$validationScript = <<<EOD
    $(document).ready(function() {
    	    
	  $('#registerForm').validate({
		  rules: {

    	}, // end rules
		  errorPlacement: function(error, element) {
			 if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		}
		 
    	 
	  }); // end validate


  }); // end ready
EOD;
	


	pageStart("Add New String", NULL, $validationScript, "pprofile", NULL, "Add New String", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<style type="text/css">
	.defaultinput{
		 width:  500px;
	}
</style>
<center>
	<a href='language-strings.php' class='cta1' style="width: auto;">Language Strings</a>

<form id="registerForm" action="string-process.php" method="POST" enctype="multipart/form-data">
	<div id="mainbox-no-width">   
		 <div class="boxcontent">
			<table class='profileTable'>
				 <tr>
				  <td><strong>String Slug Keyword</strong></td>
				  <td><input type="text" class="defaultinput" name="string_slug" required /></td>
				 </tr>				 
				 <tr>
				  <td><strong>String (English)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_en" required /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (Spanish)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_es"  /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (Catalan)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_ca"  /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (French)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_fr"  /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (Dutch)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_nl"  /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (Italian)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_it"  /></td>
				 </tr>		 
				</table>
				
			<button class='oneClick cta4' name='save_string' type="submit"><?php echo $lang['global-savechanges']; ?></button>
		</div>
	</div>
</form>
</center>
<?php displayFooter(); ?>
