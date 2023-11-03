<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	if(isset($_SESSION['lang'])){
	 	$current_lang = $_SESSION['lang'];
	 }else{
	 	$current_lang = 'en';
	 }
	// Authenticate & authorize
	authorizeUser($accessLevel);
	if(isset($_GET['did'])){
		// delete department
		$id= $_GET['did'];
		$deleteTag = "DELETE FROM video_tags where id = $id";
			try
			{
				$results = $pdo3->prepare("$deleteTag");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$_SESSION['successMessage'] = "Tag deleted successfully!";
			header("location: video-tags.php");
			exit();
	}
	// Query to look up users
	 $selectTAgs= "SELECT * FROM video_tags order by id DESC";
		try
		{
			$results = $pdo3->prepare("$selectTAgs");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Tags",
			    filename: "Tags" //do not include extension
		
			  });
		
			});
		  
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); 

		});
      function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this tag ?')){
      	 	 window.location = "video-tags.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Video Tags", NULL, $memberScript, "pmembership", NULL, "Video Tags", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='help-section.php' class='cta'>Help Center</a><a href='new-tag.php' class='cta'>Add New Tag</a></center>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Popular</th>
	    <th>Action</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($tag = $results->fetch()) {

			$tag_en = $tag['tag'];
			$tag_es = $tag['tag_es'];
			$tag_ca = $tag['tag_ca'];
			$tag_fr = $tag['tag_fr'];
			$tag_nl = $tag['tag_nl'];
			$tag_it = $tag['tag_it'];
			$most_popular = $tag['most_popular'];
			$popular = '';
			if($most_popular == 1){
				$popular = 'Yes';
			}

				if($current_lang == 'en'){
		   			$tag_name = $tag_en;
		   			
		   		}else if($current_lang == 'es' && $tag_es != ''){
		   			$tag_name = $tag_es;
		   			
		   		}else if($current_lang == 'ca' && $tag_ca != ''){
		   			$tag_name = $tag_ca;
		   		}else if($current_lang == 'fr' && $tag_fr != ''){
		   			$tag_name = $tag_fr;
		   		}else if($current_lang == 'nl' && $tag_nl != ''){
		   			$tag_name = $tag_nl;
		   		}else if($current_lang == 'it' && $tag_it != ''){
		   			$tag_name = $tag_it;
		   		}else{
		   			$tag_name = $tag_en;
		   		}

	echo sprintf("
  	    <tr><td>%s</td>
  	    <td>%s</td>
		<td><a href='edit-tag.php?id=%d'><img src='images/edit.png' height='15' title='Edit tag'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete tag'></a></td></tr>",
  	 $tag_name, $popular, $tag['id'], $tag['id']
  	);
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();