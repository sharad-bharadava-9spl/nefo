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

	$stringid = $_GET['stringid'];

		if(empty($stringid) || $stringid == ''){
			header("Location:language-strings.php");
			exit();
		}
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
	


	pageStart("Edit String", NULL, $validationScript, "pprofile", NULL, "Edit String", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	// Query to look up strings
		$selectStrings = "SELECT * FROM  language_strings WHERE id = $stringid";
		try
		{
			$results = $pdo3->prepare("$selectStrings");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $results->fetch();
			$string_slug = $row['string_slug'];
			$string_en = $row['string_en'];
			$string_es = $row['string_es'];
			$string_ca = $row['string_ca'];
			$string_fr = $row['string_fr'];
			$string_nl = $row['string_nl'];
			$string_it = $row['string_it'];


?>
<style type="text/css">
	.defaultinput{
		 width:  500px;
	}
</style>
<center>
	<a href='language-strings.php' class='cta1' style="width: auto;">Language Strings</a>

<form id="registerForm" action="edit-string-process.php" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="string_id" value="<?php echo $stringid ?>">
	<div id="mainbox-no-width">   
		 <div class="boxcontent">
			<table class='profileTable'>
				<tr>
				  <td><strong>String Slug Keyword</strong></td>
				  <td><input type="text" class="defaultinput" name="string_slug" value="<?php echo $string_slug; ?>" required /></td>
				 </tr>
				 <tr>
				  <td><strong>String (English)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_en" value="<?php echo $string_en; ?>" required /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (Spanish)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_es" value="<?php echo $string_es; ?>" /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (Catalan)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_ca" value="<?php echo $string_ca; ?>" /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (French)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_fr" value="<?php echo $string_fr; ?>" /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (Dutch)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_nl" value="<?php echo $string_nl; ?>" /></td>
				 </tr>			 
				 <tr>
				  <td><strong>String (Italian)</strong></td>
				  <td><input type="text" class="defaultinput" name="string_it" value="<?php echo $string_it; ?>" /></td>
				 </tr>		 
				</table>
				
			<button class='oneClick cta4' name='save_string' type="submit"><?php echo $lang['global-savechanges']; ?></button>
		</div>
	</div>
</form>
</center>
<?php displayFooter(); ?>
