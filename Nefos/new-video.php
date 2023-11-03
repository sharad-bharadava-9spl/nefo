<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
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
			  video_file_en: {
				  required: true,
				  extension: "mp4",
				  /*filesize: 8388608*/
			  },video_file_es: {
				  extension: "mp4",
				  /*filesize: 8388608*/
			  },video_file_ca: {
				  extension: "mp4",
				  /*filesize: 8388608*/
			  },video_file_fr: {
				  extension: "mp4",
				  /*filesize: 8388608*/
			  },video_file_nl: {
				  extension: "mp4",
				  /*filesize: 8388608*/
			  },video_file_it: {
				  extension: "mp4",
				  /*filesize: 8388608*/
			  },
			  video_preview:{
			  	 required: true,
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
	
   //  query to look up issues
	$selectTags = "SELECT * FROM video_tags order by id ASC"; 
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
/*	while($rowTag = $result_tag->fetch()){
		$tag_arr[$rowTag['id']] =  $rowTag['tag'];
	}*/


	pageStart("Add New Video", NULL, $validationScript, "pprofile", NULL, "Add New Video", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<center>
	<a href='help-section.php' class='cta'>Help Center</a>
	<a href='video-tags.php' class='cta'>Video Tags</a>
</center>
<form id="registerForm" action="video-process.php" method="POST" enctype="multipart/form-data">
    
 <div class="overview">
	<table class='profileTable'>
		 <tr>
		  <td><strong>Video Title (English)</strong></td>
		  <td><input type="text" name="video_title_en" required /></td>
		 </tr>			 
		 <tr>
		  <td><strong>Video Title (Spanish)</strong></td>
		  <td><input type="text" name="video_title_es" required /></td>
		 </tr>			 
		 <tr>
		  <td><strong>Video Title (Catalan)</strong></td>
		  <td><input type="text" name="video_title_ca" required /></td>
		 </tr>			 
		 <tr>
		  <td><strong>Video Title (French)</strong></td>
		  <td><input type="text" name="video_title_fr" required /></td>
		 </tr>			 
		 <tr>
		  <td><strong>Video Title (Dutch)</strong></td>
		  <td><input type="text" name="video_title_nl" required /></td>
		 </tr>			 
		 <tr>
		  <td><strong>Video Title (Italian)</strong></td>
		  <td><input type="text" name="video_title_it" required /></td>
		 </tr>		 
<!-- 		 <tr>
		  <td><strong>Video status</strong></td>
		  <td>
		  	<input type="radio" name="video_status"  value="1" checked />New
		  	<input type="radio" name="video_status"  value="0" />Old
		  </td>
		 </tr> -->
		 <tr>
		 	<td>
		 		<strong>Add Tags</strong>
		 	</td>
		 	<td>
	 			<!-- <input type="text" name="tag" id="tagselect"  class="multi_tags" placeholder="add multiple tags" required="">
			  	<input type="hidden" name="tag_id" id="tag_ids"> -->
			  	<?php $x=0; while($rowTag = $result_tag->fetch()){ 

			  		      $tag_id = $rowTag['id'];
			  		      $tag_en = $rowTag['tag'];
			  		      $tag_es = $rowTag['tag_es'];
			  		      $tag_ca = $rowTag['tag_ca'];
			  		      $tag_fr = $rowTag['tag_fr'];
			  		      $tag_nl = $rowTag['tag_nl'];
			  		      $tag_it = $rowTag['tag_it'];
			  		     
			  		       if($tag_es == ''){
			  		       	  $tag_es = 'N/A';
			  		       }if($tag_ca == ''){
			  		       	  $tag_ca = 'N/A';
			  		       }if($tag_fr == ''){
			  		       	  $tag_fr = 'N/A';
			  		       }if($tag_nl == ''){
			  		       	  $tag_nl = 'N/A';
			  		       }if($tag_it == ''){
			  		       	  $tag_it = 'N/A';
			  		       }

			  		       $tag_box = $tag_es." (Spanish)<br>".$tag_ca." (Catalan)<br>".$tag_fr." (French)<br>".$tag_nl." (Dutch)<br>".$tag_it." (Italian)";

		  					
											$commentRead = "
											                <img src='images/info2.png' id='comment$tag_id' /><div id='helpBox$tag_id' class='helpBox'><strong>Other Languages:</strong><br>{$tag_box}</div>
											                <script>
											                  	$('#comment$tag_id').on({
															 		'mouseover' : function() {
																	 	$('#helpBox$tag_id').css('display', 'block');
															  		},
															  		'mouseout' : function() {
																	 	$('#helpBox$tag_id').css('display', 'none');
																  	}
															  	});
															</script>
											                ";
											
			  	 ?>
			  		<input type="checkbox" class="specialInput" name="tag_id[]" value="<?php echo $rowTag['id'] ?>" required=""> <?php echo $rowTag['tag']; ?>&nbsp;<span style="position: relative;"><?php echo $commentRead;  ?></span>&nbsp;
			   <?php  $x++; } ?>
		 	</td>
		 </tr>
		 <tr>
		 	<td>
		 		<strong>Add Video File (English)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_en" name='video_file_en' onchange="setFileInfo(this, 'video_duration_time_en');" required="" accept="video/mp4">
		 		<input type="hidden" name="video_duration_en" id="video_duration_time_en">
		 	</td>
		 </tr>		 
		 <tr>
		 	<td>
		 		<strong>Add Video File (Spanish)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_es" name='video_file_es' onchange="setFileInfo(this, 'video_duration_time_es');" accept="video/mp4">
		 		<input type="hidden" name="video_duration_es" id="video_duration_time_es">
		 	</td>
		 </tr>		 
		 <tr>
		 	<td>
		 		<strong>Add Video File (Catalan)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_ca" name='video_file_ca' onchange="setFileInfo(this, 'video_duration_time_ca');" accept="video/mp4">
		 		<input type="hidden" name="video_duration_ca" id="video_duration_time_ca">
		 	</td>
		 </tr>		 
		 <tr>
		 	<td>
		 		<strong>Add Video File (French)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_fr" name='video_file_fr' onchange="setFileInfo(this, 'video_duration_time_fr');" accept="video/mp4">
		 		<input type="hidden" name="video_duration_fr" id="video_duration_time_fr">
		 	</td>
		 </tr>		 
		 <tr>
		 	<td>
		 		<strong>Add Video File (Dutch)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_nl" name='video_file_nl' onchange="setFileInfo(this, 'video_duration_time_nl');" accept="video/mp4">
		 		<input type="hidden" name="video_duration_nl" id="video_duration_time_nl">
		 	</td>
		 </tr>		 
		 <tr>
		 	<td>
		 		<strong>Add Video File (Italian)</strong>
		 	</td>
		 	<td><input type="file" id="video_input_it" name='video_file_it' onchange="setFileInfo(this, 'video_duration_time_it');" accept="video/mp4">
		 		<input type="hidden" name="video_duration_it" id="video_duration_time_it">
		 	</td>
		 </tr>		 
		 <tr>
		 	<td>
		 		<strong>Add Video Preview image</strong>
		 	</td>
		 	<td><input type="file" name='video_preview' required="" accept="image/*"></td>
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