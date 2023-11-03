<?php
// created by konstant for task-15060600 on 22-03-2022	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	 if(isset($_SESSION['lang'])){
	 	$current_lang = $_SESSION['lang'];
	 }else{
	 	$current_lang = 'en';
	 }

	
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "language-strings",
			    filename: "language-strings" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					2: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

	function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this string ?')){
      	 	 window.location = "language-strings.php?did="+delete_id;
      	 }
      }
		
EOD;
// delete videos

	if(isset($_GET['did'])){
		// delete department
		$id= $_GET['did'];
		$deleteString = "DELETE FROM language_strings where id = $id";
			try
			{
				$results = $pdo3->prepare("$deleteString");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$_SESSION['successMessage'] = "Language String deleted successfully!";
			header("location: language-strings.php");
			exit();
	}

	pageStart("Language Strings", NULL, $memberScript, "pmembership", NULL, "Language Strings", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center>
	<a href='new-string.php' class='cta1'>Add New String</a>
</center>


         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
<br />
<br />
<?php 
     // select videos
	$selectStrings = "SELECT * from language_strings order by id desc";
		try
		{
			$result = $pdo3->prepare("$selectStrings");
			$result->execute();
			
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
 ?>

	 <table class='default' id='mainTable'>
		  <thead>	
		   <tr style='cursor: pointer;'>
		    <th>String Slug Keywords</th>
		    <th>Language String</th>
		    <th>Created</th>
		    <th>Actions</th>
		   </tr>
		  </thead>
		  <tbody>
		<?php
		$i= 0;
		   while($string = $result->fetch()){
		   		$stringid = $string['id'];
		   		$string_slug = $string['string_slug'];
		   		$string_en = $string['string_en'];
		   		$string_es = $string['string_es'];
		   		$string_ca = $string['string_ca'];
		   		$string_fr = $string['string_fr'];
		   		$string_nl = $string['string_nl'];
		   		$string_it = $string['string_it'];

		   		if($current_lang == 'en'){
		   			$string_lang = $string_en;
		   			
		   		}else if($current_lang == 'es'){
		   			$string_lang = $string_es;
		   			
		   		}else if($current_lang == 'ca'){
		   			$string_lang = $string_ca;
		   		}else if($current_lang == 'fr'){
		   			$string_lang = $string_fr;
		   		}else if($current_lang == 'nl'){
		   			$string_lang = $string_nl;
		   		}else if($current_lang == 'it'){
		   			$string_lang = $string_it;
		   		}

		   		$created = date("d-m-Y H:i:s", strtotime($string['created_at']));

		   		$string_row =	sprintf("
			  	  <tr>
			  	   <td class='clickableRow' href='edit-string.php?stringid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-string.php?stringid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-string.php?stringid=%d'>%s</td>
			  	   <td style='text-align: center;'><a href='edit-string.php?stringid=%d'><img src='images/edit.png' height='15' title='Editar' /></a>&nbsp;&nbsp;<a href='javascript:void(0)' onClick='delete_element(%d)' ><img src='images/delete.png' height='15' title='Delete String' /></a></td>
				  </tr>",
				   $stringid, $string_slug, $stringid, $string_lang, $stringid, $created, $stringid, $stringid
				  );
				  $i++;
				  echo $string_row;
		   }
        ?>
		  </tbody>
	 </table>
<?php  displayFooter();