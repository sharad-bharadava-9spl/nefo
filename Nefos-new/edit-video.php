<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	$video_upload_path = "https://ccsnubev2.com/v6/Nefos";
	//$video_upload_path = "videos";
	$preview_upload_path = "https://ccsnubev2.com/v6/Nefos";
	//$preview_upload_path = "videos/preview_images";
	session_start();
	$accessLevel = '3';
	$videoid = $_GET['videoid'];
		if(empty($videoid) || $videoid == ''){
			header("Location:help-section.php");
			exit();
		}
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$validationScript = <<<EOD
    $(document).ready(function() {
    	    
	  $('#registerForm').validate({
		  rules: {
			  video_file_en: {
				  extension: "mp4",
				  /*filesize: 8388608*/
			  },video_file_es: {
				  extension: "mp4",
			  },video_file_ca: {
				  extension: "mp4",
			  },video_file_fr: {
				  extension: "mp4",
			  },video_file_nl: {
				  extension: "mp4",
			  },video_file_it: {
				  extension: "mp4",
			  },
			  video_preview:{
			  	 extension: "jpg|jpeg|png"
			  }
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
	



	pageStart("Edit Video", NULL, $validationScript, "pprofile", NULL, "Edit Video", $_SESSION['successMessage'], $_SESSION['errorMessage']);


		// Query to look up videos
		$selectVideos = "SELECT * FROM  help_videos WHERE id = $videoid";
		try
		{
			$results = $pdo3->prepare("$selectVideos");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $results->fetch();
			$video_title_en = $row['video_title_en'];
			$video_title_es = $row['video_title_es'];
			$video_title_ca = $row['video_title_ca'];
			$video_title_fr = $row['video_title_fr'];
			$video_title_nl = $row['video_title_nl'];
			$video_title_it = $row['video_title_it'];
			$tags = $row['tags'];
			$video_path_en = $row['video_path_en'];
			$video_path_es = $row['video_path_es'];
			$video_path_ca = $row['video_path_ca'];
			$video_path_fr = $row['video_path_fr'];
			$video_path_nl = $row['video_path_nl'];
			$video_path_it = $row['video_path_it'];
			$preview_path = $row['preview_path'];
			$video_duration_en = $row['video_duration_en'];
			$video_duration_es = $row['video_duration_es'];
			$video_duration_ca = $row['video_duration_ca'];
			$video_duration_fr = $row['video_duration_fr'];
			$video_duration_nl = $row['video_duration_nl'];
			$video_duration_it = $row['video_duration_it'];
			$video_status = $row['video_status'];

			   //  query to look up issues
				$selectTags = "SELECT id,tag FROM video_tags WHERE id NOT IN($tags) order by id ASC"; 
					try
					{
						$result_tag = $pdo3->prepare("$selectTags");
						$result_tag->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				while($rowTag = $result_tag->fetch()){
					$tag_arr[$rowTag['id']] =  $rowTag['tag'];
				}

		   		 $selectTagsId = "SELECT id,tag from video_tags WHERE id IN ($tags)";
		   				try
						{
							$tag_id_results = $pdo3->prepare("$selectTagsId");
							$tag_id_results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						
						while($tagIdRow = $tag_id_results->fetch()){
							$tag_id_arr[$tagIdRow['id']] = $tagIdRow['tag'];
						}


						

					
?>
<center>
	<a href='help-section.php' class='cta'>Help Center</a>
	<a href='video-tags.php' class='cta'>Video Tags</a>
</center>
<form id="registerForm" action="edit-video-process.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="video_id" value="<?php echo $videoid ?>">
 <div class="overview">
	<table class='profileTable'>
		 <tr>
		  <td><strong>Video Title (English)</strong></td>
		  <td><input type="text" name="video_title_en"  value="<?php echo $video_title_en; ?>" required /></td>
		 </tr> 
		 <tr>
		  <td><strong>Video Title (Spanish)</strong></td>
		  <td><input type="text" name="video_title_es"  value="<?php echo $video_title_es; ?>" required /></td>
		 </tr> 
		 <tr>
		  <td><strong>Video Title (Catalan)</strong></td>
		  <td><input type="text" name="video_title_ca"  value="<?php echo $video_title_ca; ?>" required /></td>
		 </tr> 
		 <tr>
		  <td><strong>Video Title (French)</strong></td>
		  <td><input type="text" name="video_title_fr"  value="<?php echo $video_title_fr; ?>" required /></td>
		 </tr> 
		 <tr>
		  <td><strong>Video Title (Dutch)</strong></td>
		  <td><input type="text" name="video_title_nl"  value="<?php echo $video_title_nl; ?>" required /></td>
		 </tr> 
		 <tr>
		  <td><strong>Video Title (Italian)</strong></td>
		  <td><input type="text" name="video_title_it"  value="<?php echo $video_title_it; ?>" required /></td>
		 </tr>
		 <tr>
		  <td><strong>Video status</strong></td>
		  <td>
		  	<input type="radio" name="video_status"  value="1" <?php if($video_status == 1){ echo 'checked';  } ?> />New
		  	<input type="radio" name="video_status"  value="0" <?php if($video_status == 0){ echo 'checked';  } ?>/>Old
		  </td>
		 </tr>
		 <tr>
		 	<td>
		 		<strong>Add Tags</strong>
		 	</td>
		 	<td>
	 			<!-- <input type="text" name="tag" id="tagselect" value="<?php //echo $tag_names ?>"  class="multi_tags" placeholder="add multiple tags" required="">
			  	<input type="hidden" name="tag_id" id="tag_ids" value="<?php //echo $tags; ?>"> -->
			  	<?php  foreach($tag_id_arr as $tag_id_val => $tag_id_name){  ?>
			  		<input type="checkbox" class="specialInput" name="tag_id[]" value="<?php echo $tag_id_val ?>" checked required=""> <?php echo $tag_id_name; ?>&nbsp;
			   <?php } ?>
			   <?php  foreach($tag_arr as $tag_id => $tag_name){  ?>
			  		<input type="checkbox" class="specialInput" name="tag_id[]" value="<?php echo $tag_id ?>" required=""> <?php echo $tag_name; ?>&nbsp;
			   <?php } ?>
		 	</td>
		 </tr> 
		 <tr>
		 	<td>
		 		<strong>Add Video File (English)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_en" name='video_file_en' onchange="setFileInfo(this, 'video_duration_time_en');" accept="video/mp4"><br><br>
		 		<input type="hidden" name="last_video_path_en" value="<?php echo $video_upload_path; ?>/<?php echo $video_path_en; ?>">
		 		<input type="hidden" name="last_video_name_en" value="<?php echo $video_path_en; ?>">
		 		<input type="hidden" name="video_duration_en" id="video_duration_time_en" value="<?php echo $video_duration_en ?>">
		 		<video width="400" controls>
				 <source src="<?php echo $video_upload_path; ?>/<?php echo $video_path_en; ?>" type="video/mp4">
				  Your browser does not support HTML5 video.
				 </video>
		 	</td>
		 </tr>
		 <tr>
		 	<td>
		 		<strong>Add Video File (Spanish)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_es" name='video_file_es' onchange="setFileInfo(this, 'video_duration_time_es');" accept="video/mp4"><br><br>
		 		<input type="hidden" name="last_video_path_es" value="<?php echo $video_upload_path; ?>/<?php echo $video_path_es; ?>">
		 		<input type="hidden" name="last_video_name_es" value="<?php echo $video_path_es; ?>">
		 		<input type="hidden" name="video_duration_es" id="video_duration_time_es" value="<?php echo $video_duration_es ?>">
		 		<?php if($video_path_es != ''){ ?>
			 		<video width="400" controls>
					 <source src="<?php echo $video_upload_path; ?>/<?php echo $video_path_es; ?>" type="video/mp4">
					  Your browser does not support HTML5 video.
					 </video>
				<?php } ?>
		 	</td>
		 </tr>			 
		 <tr>
		 	<td>
		 		<strong>Add Video File (Catalan)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_ca" name='video_file_ca' onchange="setFileInfo(this, 'video_duration_time_ca');" accept="video/mp4"><br><br>
		 		<input type="hidden" name="last_video_path_ca" value="<?php echo $video_upload_path; ?>/<?php echo $video_path_ca; ?>">
		 		<input type="hidden" name="last_video_name_ca" value="<?php echo $video_path_ca; ?>">
		 		<input type="hidden" name="video_duration_ca" id="video_duration_time_ca" value="<?php echo $video_duration_ca ?>">
		 		<?php if($video_path_ca != ''){ ?>
			 		<video width="400" controls>
					 <source src="<?php echo $video_upload_path; ?>/<?php echo $video_path_ca; ?>" type="video/mp4">
					  Your browser does not support HTML5 video.
					 </video>
				<?php } ?>
		 	</td>
		 </tr>			 
		 <tr>
		 	<td>
		 		<strong>Add Video File (French)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_fr" name='video_file_fr' onchange="setFileInfo(this, 'video_duration_time_fr');" accept="video/mp4"><br><br>
		 		<input type="hidden" name="last_video_path_fr" value="<?php echo $video_upload_path; ?>/<?php echo $video_path_fr; ?>">
		 		<input type="hidden" name="last_video_name_fr" value="<?php echo $video_path_fr; ?>">
		 		<input type="hidden" name="video_duration_fr" id="video_duration_time_fr" value="<?php echo $video_duration_fr ?>">
		 		<?php if($video_path_fr != ''){ ?>
			 		<video width="400" controls>
					 <source src="<?php echo $video_upload_path; ?>/<?php echo $video_path_fr; ?>" type="video/mp4">
					  Your browser does not support HTML5 video.
					 </video>
				<?php } ?>
		 	</td>
		 </tr>			 
		 <tr>
		 	<td>
		 		<strong>Add Video File (Dutch)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_nl" name='video_file_nl' onchange="setFileInfo(this, 'video_duration_time_nl');" accept="video/mp4"><br><br>
		 		<input type="hidden" name="last_video_path_nl" value="<?php echo $video_upload_path; ?>/<?php echo $video_path_nl; ?>">
		 		<input type="hidden" name="last_video_name_nl" value="<?php echo $video_path_nl; ?>">
		 		<input type="hidden" name="video_duration_nl" id="video_duration_time_nl" value="<?php echo $video_duration_nl ?>">
		 		<?php if($video_path_nl != ''){ ?>
			 		<video width="400" controls>
					 <source src="<?php echo $video_upload_path; ?>/<?php echo $video_path_nl; ?>" type="video/mp4">
					  Your browser does not support HTML5 video.
					 </video>
				<?php } ?>
		 	</td>
		 </tr>			 
		 <tr>
		 	<td>
		 		<strong>Add Video File (Italian)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_it" name='video_file_it' onchange="setFileInfo(this, 'video_duration_time_it');" accept="video/mp4"><br><br>
		 		<input type="hidden" name="last_video_path_it" value="<?php echo $video_upload_path; ?>/<?php echo $video_path_it; ?>">
		 		<input type="hidden" name="last_video_name_it" value="<?php echo $video_path_it; ?>">
		 		<input type="hidden" name="video_duration_it" id="video_duration_time_it" value="<?php echo $video_duration_it ?>">
		 		<?php if($video_path_it != ''){ ?>
			 		<video width="400" controls>
					 <source src="<?php echo $video_upload_path; ?>/<?php echo $video_path_it; ?>" type="video/mp4">
					  Your browser does not support HTML5 video.
					 </video>
				<?php } ?>
		 	</td>
		 </tr>		 
		 <tr>
		 	<td>
		 		<strong>Add Video Preview image</strong>
		 	</td>
		 	<td><input type="file"  name='video_preview' accept="image/*"><br><br>
		 		<input type="hidden" name="last_preview_path" value="<?php echo $preview_upload_path; ?>/<?php echo $preview_path; ?>">
		 		<input type="hidden" name="last_preview_name" value="<?php echo $preview_path; ?>">
		 		<img src="<?php echo $preview_upload_path; ?>/<?php echo $preview_path ?>" width="400">
		 	</td>
		 </tr>
		</table>
		
	<button class='oneClick' name='save_video' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</div>

<?php displayFooter(); ?>
<script type="text/javascript">
	//document.getElementById('video_input').onchange = setFileInfo;

function setFileInfo(e, duration_id) {
	console.log(e);
  var files = e.files;
  myVideos = files[0];
  var video = document.createElement('video');
  video.preload = 'metadata';
//  var duration_id = $(this).closest().find('input[type=hidden]').attr("id");
  console.log(duration_id);
  video.onloadedmetadata = function() {
    window.URL.revokeObjectURL(video.src);
    var duration = video.duration;
    myVideos.duration = duration;
    updateInfos(duration_id);
  }

  video.src = URL.createObjectURL(files[0]);;
}


function updateInfos(duration_id) {
    var duration_time = myVideos.duration;
    $("#"+duration_id).val(duration_time);
    console.log(duration_time);
}
		 // user multiselect autocomplete  
	 var tag_arr = <?php echo json_encode($tag_arr)  ?>;
	
	 	 var tag_name =[];
	  	for(var i in tag_arr){
	  		tag_name.push(tag_arr[i]);
	  	}
	
	$( function() {
		
		function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}

		$( ".multi_tags" )
			// don't navigate away from the field on tab when selecting an item
			.on( "keydown", function( event ) {
				if ( event.keyCode === $.ui.keyCode.TAB &&
						$( this ).autocomplete( "instance" ).menu.active ) {
					event.preventDefault();
				}
			})
			.autocomplete({
				minLength: 0,
				source: function( request, response ) {
					// delegate back to autocomplete, but extract the last term
					response( $.ui.autocomplete.filter(
						tag_name, extractLast( request.term ) ) );
				},
				focus: function(event, ui) {
					// prevent value inserted on focus
					return false;
				},
				select: function( event, ui ) {
					var terms = split( this.value );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
				 
					var tag_ids = [];	
					Object.keys(tag_arr).forEach(function(k){
						for(var i in terms){
							if(terms[i] != '' && terms[i] == tag_arr[k]){
								tag_ids.push(k);
							}
						}
					});
					
					$("#tag_ids").val(tag_ids.join());
					return false;
				},
				change: function( event, ui ) {
					var terms = split( this.value );
					console.log(terms);
					// remove the current input
					terms.pop();
					
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
					
					var tag_ids = [];	
					Object.keys(tag_arr).forEach(function(k){
						for(var i in terms){
							if(terms[i] != '' && terms[i] == tag_arr[k]){
								tag_ids.push(k);
							}
						}
					});
					$("#tag_ids").val(tag_ids.join());
					return false;
				}
			});
	} );
</script>