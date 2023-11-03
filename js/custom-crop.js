$(document).ready(function(){
	// submit form

	$("#crop_sub").click(function(){
		$("#crop_form").submit();
	});


	$(".crop_btn").click(function(){
		var this_id = $(this).attr('id');
		
		var $image = $('#imageCrop');

		$image.cropper({
		  aspectRatio: 4 / 3,
		  crop: function(event) {
		   /* console.log(event.detail.x);
		    console.log(event.detail.y);
		    console.log(event.detail.width);
		    console.log(event.detail.height);
		    console.log(event.detail.rotate);
		    console.log(event.detail.scaleX);
		    console.log(event.detail.scaleY);*/
		  }
		});
		var cropper = $image.data('cropper');
		//console.log(cropper);
		if(this_id == 'crop'){

		}else if(this_id == 'zoom-in'){
			cropper.zoom(0.1);
		}else if(this_id == 'zoom-out'){
			cropper.zoom(-0.1);
		}else if(this_id == 'rotate-left'){
			cropper.rotate(-90);
		}else if(this_id == 'rotate-right'){
			cropper.rotate(90);
		}else if(this_id == 'get-canvas'){
			var croppedCanvas = cropper.getCroppedCanvas({ maxWidth: 4096, maxHeight: 4096 });
			//document.getElementById("cropped_img").html =croppedCanvas;
               $("#cropped_img").html(croppedCanvas); 
			   var canvas = $("canvas");
			   //var imgejpg = convertCanvasToImage(canvas);
			   //console.log(canvas);
			  

			   //document.getElementById("pngHolder").appendChild(convertCanvasToImage(canvas[0]));
			  $("#pngHolder").html(convertCanvasToImage(canvas[0]));
			  $("#crop_sub").show();
			   var elmnt = document.getElementById("pngHolder");
  			   elmnt.scrollIntoView();
			//$("#cropped_img").html(croppedCanvas);
			// Upload cropped image to server if the browser supports `HTMLCanvasElement.toBlob`.
				// The default value for the second parameter of `toBlob` is 'image/png', change it if necessary.
				
		}
	});

});

function convertCanvasToImage(canvas) {
	var image = new Image();
	image.src = canvas.toDataURL("image/png");
	var raw_image_data = image.src.replace(/^data\:image\/\w+\;base64\,/, '');
	document.getElementById('cropedPhoto').value = raw_image_data;
	return image;
}


	 function readURL(input) {
	  if (input.files && input.files[0]) {
	    var reader = new FileReader();
	    
	    reader.onload = function(e) {
	      $('#imageCrop').attr('src', e.target.result);
	    }
	    
	    reader.readAsDataURL(input.files[0]); // convert to base64 string
	  }
	}

	/*$("#imgInp").change(function() {
		//alert(this);
		$("#imagecrop_buttons").show();
	  	readURL(this);
	      var elmnt = document.getElementById("imagecrop_buttons");
  			elmnt.scrollIntoView();
	});*/
